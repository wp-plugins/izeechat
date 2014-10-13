<?php

class IzeeProcess {
	
	private $_userInst;
	private $_API;
	private $_apiHost;
	private $_cipher;
	private $_currentTime;
	private $_tables;
	private $_url;

	/**
	 * Constructeur
	 * 
	 * @param  strinf  $api_host URLdu serveur cloud
	 * @author  Kevin B. Apizee Inc
	 */
	public function __construct($api_host) {
		$this->_userInst    = IzeeContainer::getUserInst();
		$this->_API         = new IzeeAPI($api_host);
		$this->_apiHost		= $api_host;
		$this->_cipher      = new IzeeCipher();
		$this->_currentTime = current_time( 'mysql', 1 );
		$this->_tables      = array("izeechat","izeechat_users");
		$this->_url         = IzeeContainer::getRequestInst()->url;
	}

	public function verifForm($data=array(), $action, $test=false) {
		try {
			if (!empty($data)) {
				if((isset($data[$action.'_submit'])) || ( isset($data[$action.'_submit']) && $test === true ) ) {
					if(!empty($data[$action.'_submit'])) {
						return true;
					} else {
						$err = __('An unexpected error has occurred. please contact an administrator','Izeechat');
						throw new IzeeException($err);
					}
				} else {
					if ($test === true) return false;
					$err = __('An unexpected error has occurred. please contact an administrator','Izeechat');
					throw new IzeeException($err);
				}
			} else {
				$err = __('An unexpected error has occurred. please contact an administrator','Izeechat');
				throw new IzeeException($err);
			}
		} catch (IzeeException $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e)));
			exit;
		} catch (Exception $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e->getMessage())));
			exit;
		}
	}

	/**
	 * Enregistrement du futur admin cloud
	 * 
	 * @param  array  $data Tableau $data
	 * @author  Kevin B. Apizee Inc
	 */
	public function registerProcess($data=array()) {
		global $wpdb;

		try {
			// vérifier si des données sont reçues
			$this->verifForm($data, 'register');

			$fisrtname        = $data['register_firstname'];
			$lastname         = $data['register_lastname'];
			$email            = $data['register_email'];
			$domain           = $this->_url;
			$password         = $data['register_password'];
			$password2        = $data['register_password2'];
			$password_crypted = $this->_cipher->encrypt($password);
			$enterpriseId     = 0; // own enterprise
			$userWPId         = IzeeContainer::getUserInst()->userInfos['id'];
			$content          = array();

			if ( $password !== $password2 ) {
				$err = __('An unexpected error has occurred','Izeechat'); 
				$err2 = __('Passwords not match !','Izeechat');
				throw new IzeeException($err.'##'.$err2);
			} else {
				// Créer le compte sur le cloud apizee
				$apizee_sub = $this->_API->subscription(
					$fisrtname,		
					$lastname,
					$password,		
					$email,
					$domain
				);

				if ($apizee_sub['result'] == true ) { array_push($content, $apizee_sub); }
				else { 
					$err = __('An unexpected error has occurred','Izeechat'); 
					$errapi = unserialize($apizee_sub['json']); 
					$errapi = ($errapi['reason'] != "") ? $errapi['reason'] : __("User could already exist else contact an administrator", "Izeechat"); 
					throw new IzeeException($err.'##'.$errapi); 
				}

				$apizee_gc = $this->_API->getConfiguration(
					$email,
					$password,
					$enterpriseId,
					$domain
				);

				if ($apizee_gc['result'] == true ) { array_push($content, $apizee_gc); }
				else { $err = __('An unexpected error has occurred','Izeechat'); $errapi = unserialize($apizee_gc['json']); $errapi = $errapi['reason']; throw new IzeeException($err.'##'.$errapi); }

				$params = array(
					'email'        => $email,
					'password'     => $password_crypted,
					'api_rtcKey'   => $content[1]["apiRtcKey"],
					'site_domain'  => $domain,
					'site_key'     => $content[1]["siteKey"],
					'users'        => $content[1]["users"],
					'enterpriseId' => $content[1]["eid"],
					'activation'   => 1,
					'box_display'  => 'enable',
					'created_at'   => $dateNow
				);

				if (IzeeContainer::getBddInst()->insertInTable($this->_tables[0], $params)) {

					$params = array(
						'first_name'     => $fisrtname,
						'last_name'      => $lastname,
						'email'          => $email,
						'password'       => $this->_userInst->userInfos['password'],
						'cloud_password' => $password_crypted,
						'status'		 => 1
					);

					if ( IzeeContainer::getBddInst()->insertInTable($this->_tables[1], $params) ) {

						wp_update_user(array('ID' => intval($userWPId) , 'role' => "izeechat_admin"));
						setcookie("wordpress_izeechat_access", "", time()-3600 );
						IzeeContainer::getUtilsInst()->redirect('home');

					} else {
						$err = __('An unexpected error has occurred during registration. please contact an administrator','Izeechat');
						throw new IzeeException($err);
					}
				} else {
					$err = __('An unexpected error has occurred during registration. please contact an administrator','Izeechat');
					throw new IzeeException($err);
				}
			}
		} catch (IzeeException $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e)));
			exit;
		} catch (Exception $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e->getMessage())));
			exit;
		}
	}

	/**
	 * Authentification de l'admin cloud
	 * @param  array  $data Tableau $data
	 * @author  Kevin B. Apizee Inc
	 */
	public function authenticationProcess($data=array()) {
		global $wpdb;

		try {
			// vérifier si des données sont reçues
			$this->verifForm($data, 'authentication');

			$email            = $data['authentication_email'];
			$domain           = $this->_url;
			$password         = $data['authentication_password'];
			$password_crypted = $this->_cipher->encrypt($password);
			$enterpriseId     = 0; // own enterprise
			$userWPId         = IzeeContainer::getUserInst()->userInfos['id'];
			$content          = array();
			

			$apizee_gsk = $this->_API->getSiteKey(
				$email,
				$password,
				$domain
			);

			if ($apizee_gsk['result'] != true ) {
				$apizee_ns = $this->_API->newSite(
					$email,
					$password,
					$domain, // Domain Name
					$domain  // Domain Url
				);

				if ($apizee_ns['result'] == true ) { array_push($content, $apizee_ns); }
				//else { $err = __('An error has occurred while interacting with Apizee Server','Izeechat'); $errapi = unserialize($apizee_ns['json']); $errapi = $errapi['reason']; throw new IzeeException($err.'##'.$errapi); }
				else { 
					$err = __('An unexpected error has occurred','Izeechat'); 
					$errapi = unserialize($apizee_ns['json']); 
					$errapi = ($errapi['reason'] != "") ? $errapi['reason'] : __("User probably doesn't exist else contact an administrator", "Izeechat"); 
					throw new IzeeException($err.'##'.$errapi); 
				}
			} 

			$apizee_gc = $this->_API->getConfiguration(
				$email,
				$password,
				$enterpriseId,
				$domain
			);

			if ($apizee_gc['result'] == true ) { array_push($content, $apizee_gc); }
			else { $err = __('An unexpected error has occurred','Izeechat'); $errapi = unserialize($apizee_gc['json']); $errapi = $errapi['reason']; throw new IzeeException($err.'##'.$errapi); }

			$params = array(
				'email'        => $email,
				'password'     => $password_crypted,
				'api_rtcKey'   => (!isset($content[1])) ? $content[0]["apiRtcKey"] : $content[1]["apiRtcKey"],
				'site_domain'  => $domain,
				'site_key'     => (!isset($content[1])) ? $content[0]["siteKey"] : $content[1]["siteKey"],
				'users'        => (!isset($content[1])) ? $content[0]["users"] : $content[1]["users"],
				'enterpriseId' => (!isset($content[1])) ? $content[0]["eid"] : $content[1]["eid"],
				'activation'   => 1,
				'box_display'  => 'enable',
				'created_at'   => $dateNow
			);

			if (IzeeContainer::getBddInst()->insertInTable($this->_tables[0], $params)) {
				$params = array(
					'first_name'     => (!isset($content[1])) ? $content[0]["firstname"] : $content[1]["firstname"],
					'last_name'      => (!isset($content[1])) ? $content[0]["lastname"] : $content[1]["lastname"],
					'email'          => $email,
					'password'       => $this->_userInst->userInfos['password'],
					'cloud_password' => $password_crypted,
					'status'		 => 1
				);

				if ( IzeeContainer::getBddInst()->insertInTable($this->_tables[1], $params) ) {

					wp_update_user(array('ID' => intval($userWPId) , 'role' => "izeechat_admin"));
					setcookie("wordpress_izeechat_access", "", time()-3600 );
					IzeeContainer::getUtilsInst()->redirect('home');

				} else {
					$err = __('An unexpected error has occurred during authentication. please contact an administrator','Izeechat');
					throw new IzeeException($err);
				}

			} else {
				$err = __('An unexpected error has occurred during authentication. please contact an administrator','Izeechat');
				throw new IzeeException($err);
			}
		} catch (IzeeException $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e) ));
			exit;
		} catch (Exception $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e->getMessage()) ));
			exit;
		}
	}

	/**
	 * Connexion d'un agent cloud souhaitant être agent sur wordpress
	 * 
	 * @param  array  $data Tableau $data
	 * @author  Kevin B. Apizee Inc
	 */
	public function loginProcess($data=array()) {
		global $wpdb;

		try {
			// vérifier si des données sont reçues
			$this->verifForm($data, 'login');

			$email            = $data['login_email'];
			$password_cloud   = $this->_cipher->encrypt($data['login_password']);
			$password_crypted = $this->_cipher->encrypt($data['login_wpPassword']);
			$userWPId         = IzeeContainer::getUserInst()->userInfos['id'];
			$content          = array();

			$apizee_login = $this->_API->login(
				$email,
				$data['login_password']
			);

			if ($apizee_login['result'] == "KO") {
				$err = __('An unexpected error has occurred', 'Izeechat');
				$errapi = __("Your password isn't correct, please retry.", "Izeechat");
				throw new IzeeException($err.'##'.$errapi);
			}

			if ( !IzeeContainer::getUserInst()->verifyRole('admin') && !IzeeContainer::getUserInst()->verifyRole('agent') && !IzeeContainer::getUserInst()->verifyRole('adminwp') ) {
				$role = 'izeechat_agent';
			} elseif(IzeeContainer::getUserInst()->verifyRole('admin')) {
				$role = 'izeechat_admin';
			} else {
				$role = 'izeechat_agent';
			}

			$apizee_gui = $this->_API->getUserId(
				$this->_userInst->agentInfos[0]['email'],
				$this->_userInst->agentInfos[0]['password'],
				$email
			);

			if ($apizee_gui['result'] == true ) { array_push($content, $apizee_gui); }
			else { $err = __('An unexpected error has occurred','Izeechat'); $errapi = unserialize($apizee_gui['json']); $errapi = $errapi['reason']; throw new IzeeException($err.'##'.$errapi); }

			$params = array (
				'first_name'     => $content[0]['first_name'],
				'last_name'      => $content[0]['last_name'],
				'email'          => $email,
				'password'       => $password_crypted,
				'cloud_password' => $password_cloud,
				'status'		 => 1
			);

			if (IzeeContainer::getUtilsInst()->isAgent($email)) {
				$sql = $wpdb->get_results("SELECT email FROM ".$this->_tables[1]." WHERE email = '".$email."' ");

				if (empty($sql)) {
					if (!IzeeContainer::getBddInst()->insertInTable($this->_tables[1], $params)) {
						$err = __('An unexpected error has occurred during login. please contact an administrator','Izeechat');
						throw new IzeeException($err);
					}
				} else {
					if (!$wpdb->query($wpdb->prepare( "UPDATE ".$this->_tables[1]." SET status = %d WHERE email='".$email."' ", 1))) {
						$err = __('An unexpected error has occurred during login. please contact an administrator','Izeechat');
						throw new IzeeException($err);
					}
				}
				setcookie("wordpress_izeechat_access", "", time()-3600 );
				wp_update_user(array('ID' => intval($userWPId) , 'role' => $role));
				IzeeContainer::getUtilsInst()->redirect('home');
			} else {
				$err = __('An unexpected error has occurred', 'Izeechat');
				$errapi = __("You don't allowed to use this plugin.", "Izeechat");
				throw new IzeeException($err.'##'.$errapi);
			}

		} catch (IzeeException $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e) ));
			exit;
		} catch (Exception $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e->getMessage()) ));
			exit;
		}
	}

	/**
	 * Enregistrement sur cloud fait par le futur agent
	 * 
	 * @param  array  $data Tableau $data
	 * @author  Kevin B. Apizee Inc
	 */
	public function registrationProcess($data=array()) {
		global $wpdb;

		try {
			// vérifier si des données sont reçues
			$this->verifForm($data, 'registration');

			$lastname         = $data['registration_lastname'];
			$firstname        = $data['registration_firstname'];
			$email            = $data['registration_email'];
			$password         = $data['registration_password'];
			$password2        = $data['registration_password2'];
			$password_crypted = $this->_cipher->encrypt($data['registration_cloudPassword']);
			$enterpriseId     = $this->_userInst->agentInfos[0]['enterpriseId'];
			$content          = array();

			if ( $password !== $password2 ) {
				$err = __('An unexpected error has occurred','Izeechat'); 
				$err2 = __('Passwords not match !','Izeechat');
				throw new IzeeException($err.'##'.$err2);
			} else {
				$apizee_subA = $this->_API->subscriptionAgent(
					$this->_userInst->agentInfos[0]['email'],
					$this->_userInst->agentInfos[0]['password'],
					$last_name,
					$first_name,
					$email,
					$data['registration_cpassword'],
					$enterpriseId
				);

				if ($apizee_subA['result'] == true ) { array_push($content, $apizee_subA); }
				else { $err = __('An unexpected error has occurred','Izeechat'); $errapi = unserialize($apizee_subA['json']); $errapi = $errapi['reason']; throw new IzeeException($err.'##'.$errapi); }

				if ( $wpdb->query($wpdb->prepare( "UPDATE ".$this->_tables[1]." SET last_name = %s, first_name = %s, cloud_password = %s, status = %d WHERE email='".$email."' ", $first_name, $last_name, $password, 1 )) ) {

					setcookie("wordpress_izeechat_access", "", time()-3600 );
					IzeeContainer::getUtilsInst()->redirect('home');

				} else {
					$err = __('An unexpected error has occurred during registration. please contact an administrator','Izeechat');
					throw new IzeeException($err);
				}
			}

			$this->verifForm($data, 'registration');

			$admin_username   = $this->_userInst->agentInfos[0]['email'];
			$lastname         = $data['register_lastname'];
			$firstname        = $data['register_firstname'];
			$email            = $data['register_email'];

			$headers = 'From: '.$email. "\r\n";

			$message = __("Request to create a new agent account for IzeeChat plugin. User: ", "Izeechat");
			$message .= $firstname.' '.$lastname.' ';
			$message .= __("Email: ", "Izeechat");
			$message .= $email;

			if (!wp_mail($admin_username, "IzeeChat Registration Account", $message, $headers)) {
				$err = __('An error has occurred while send your request, please retry later','Izeechat');
				throw new IzeeException($err);
			}

		} catch (IzeeException $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e) ));
			exit;
		} catch (Exception $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e->getMessage()) ));
			exit;
		}
	}

	/**
	 * Créer un agent
	 * 
	 * @param  array  $data Tableau $data
	 * @author  Kevin B. Apizee Inc
	 */
	public function createProcess($data=array()) {
		global $wpdb;

		try {
			// vérifier si des données sont reçues
			$this->verifForm($data, 'create');

			$admin_username = $this->_userInst->agentInfos[0]['email'];
			$admin_password = $this->_userInst->agentInfos[0]['password'];
			// get user config
			$name           = $data['agent_name'];
			$firstname      = $data['agent_firstname'];
			$lastname       = $data['agent_lastname'];
			$email          = $data['agent_email'];
			$password       = $data['create_password'];
			$password2      = $data['create_password2'];;
			$enterpriseId   = $this->_userInst->agentInfos[0]['enterpriseId'];
			$role           = "izeechat_agent";
			$userWPId       = IzeeContainer::getUserInst()->userInfos['id'];
			$content        = array();

			if ( $password !== $password2 ) {
				$err = __('An unexpected error has occurred','Izeechat'); 
				$err2 = __('Passwords not match !','Izeechat');
				throw new IzeeException($err.'##'.$err2);
			} else {

				$apizee_subA = $this->_API->subscriptionAgent(
					$admin_username,
					$admin_password,
					$lastname,
					$firstname,
					$email,
					$password,
					$enterpriseId
				);

				if ($apizee_subA['result'] == true ) { array_push($content, $apizee_subA); }
				else { $err = __('An unexpected error has occurred','Izeechat'); $errapi = unserialize($apizee_subA['json']); $errapi = $errapi['reason']; throw new IzeeException($err.'##'.$errapi); }

				$id = username_exists($name);

				if ( !$user_id and email_exists($email) == false ) {
					$user_id = wp_create_user( $name, $password, $email );
					$user_id = wp_update_user( array( 'ID' => intval($id), 'role' => $role ) );

					$args = array("include"=>$id);
					$blogusers = get_users($args);
					foreach ($blogusers as $bloguser) : $WPpassword = $bloguser->user_pass; endforeach;
					$password_crypted = $this->_cipher->encrypt($WPpassword);
					$cloudPassword_crypted = $this->_cipher->encrypt($password);

					$sql = $wpdb->get_results("SELECT email FROM ".$this->_tables[1]." WHERE email = '".$email."' ");
					if (empty($sql)) {
						$params = array(
							'first_name'     => $firstname,
							'last_name'      => $lastname,
							'email'          => $email,
							'password'       => $password_crypted,
							'cloud_password' => $cloudPassword_crypted,
							'status'		 => 0
						);

						if (IzeeContainer::getBddInst()->insertInTable($this->_tables[1], $params)) { 
							$apizee_sptu = $this->_API->sendPasswordToUser(
								$email,
								$password,
								$mailType = "create"
							);

							if ($apizee_sptu['result'] == true ) { array_push($content, $apizee_sptu); }
							else { $err = __('An unexpected error has occurred','Izeechat'); $errapi = unserialize($apizee_sptu['json']); $errapi = $errapi['reason']; throw new IzeeException($err.'##'.$errapi); }
							
							wp_update_user(array('ID' => intval($userWPId) , 'role' => 'izeechat_agent'));
							setcookie("wordpress_izeechat_access", "", time()-3600 );
							IzeeContainer::getUtilsInst()->redirect('home');

						}
						else { $err = __('An unexpected error has occurred during creation. please contact an administrator','Izeechat'); 
							throw new IzeeException($err); 
						}
					} else {
						$err = __('An unexpected error has occurred during creation. please contact an administrator','Izeechat');
						throw new IzeeException($err);
					}

				} else {
					$err = __('An unexpected error has occurred','Izeechat'); 
					$err2 = __('User already exist','Izeechat');
					throw new IzeeException($err.'##'.$err2);
				}
			}
		} catch (IzeeException $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e) ));
			exit;
		} catch (Exception $e) {
			header('Location:'.add_query_arg( 'exc', base64_encode($e->getMessage()) ));
			exit;
		}
	}
}

?>