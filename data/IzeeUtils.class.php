<?php

class IzeeUtils {

	public $agentsIds;
	public $agentProfileIds;
	public $agentsProfile;

	/**
	 * Tester si le site est encore existant sur cloud
	 *
	 * @param string  URL de l'host de l'API
	 * @return boolean True/False
	 * @author  Kevin B. Apizee Inc
	 */
	public function isExist($api_host, $tables) {
		global $wpdb;
		$API          = new IzeeAPI($api_host);
		$email        = IzeeContainer::getUserInst()->agentInfos[0]['email'];
		$password     = IzeeContainer::getUserInst()->agentInfos[0]['password'];
		$enterpriseId = (IzeeContainer::getUserInst()->agentInfos[0]['enterpriseId'] != "") ? IzeeContainer::getUserInst()->agentInfos[0]['enterpriseId'] : 0 ;
		$domain       = IzeeContainer::getUserInst()->agentInfos[0]['site_domain'];

		if (is_admin()) {
			$myTables = array_keys($tables);
			if ( $wpdb->get_results("SELECT * FROM $myTables[0]") ) {
				$getConf = $API->getConfiguration(
					$email,
					$password,
					$enterpriseId,
					$domain
				);

				if($getConf['result'] == false) {
					// Réinitilisation à zéro des tables
					foreach ($tables as $table => $content) {
						$wpdb->query("TRUNCATE TABLE ".$table);
					}
					$this->redirect('home');
				} else {
					$users = $getConf['users'];
					$content = array ('users' => $users);
					$this->agentsIds = $getConf['ids'];
					$this->agentProfileIds = $getConf['profileIds'];
					$this->agentsProfile = $getConf['json'];

					$wpdb->query($wpdb->prepare( "UPDATE ".$myTables[0]." SET users = %s WHERE email='".$email."' ", $users ));
					return true;
				}
			}
		}
	}

	public function isAgent($email) {
		$myAgents = unserialize(IzeeContainer::getUserInst()->agentInfos[0]['users']);
		if (in_array($email, $myAgents)) {
			return true;
		} else { return false; }
	}

	/**
	 * Charger les textes en fonctions de la langue du wordpress (EN/FR)
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function loadLang() {
		load_plugin_textdomain('Izeechat', false, dirname(plugin_basename(ROOT_FILE)) . '/languages');
	}

	/**
	 * Charger les fichiers de styles et fichiers de scripts
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function loadJsCss() {
		wp_enqueue_script('jquery');

		if (is_admin()) {
			if ( IzeeContainer::getRequestInst()->request_page ) {
				if ( in_array(IzeeContainer::getRequestInst()->page, IzeeContainer::getCoreInst()->config['admin_pages']) ) {

					// Bootstrap
					wp_enqueue_script( 'bootstrap-js',plugins_url('lib/bootstrap/js/bootstrap.min.js', ROOT_FILE), array( 'jquery' ), '3.0.1', true );
					wp_enqueue_style( 'bootstrap-css', plugins_url('lib/bootstrap/css/bootstrap.min.css', ROOT_FILE), array(), '3.0.1', 'all' );

					// Fancybox
					wp_enqueue_script( 'fancybox-js',plugins_url('lib/fancybox/js/jquery.fancybox.js', ROOT_FILE), array( 'jquery' ), '2.1.5', true );
					wp_enqueue_style( 'fancybox-css', plugins_url('lib/fancybox/css/jquery.fancybox.css', ROOT_FILE), array(), '2.1.5', 'all' );

					wp_enqueue_script('izeechat-script',plugins_url('includes/js/admin.js',ROOT_FILE), array('jquery'),'',1);
					wp_enqueue_style('izeechat-style',plugins_url('includes/css/admin.css',ROOT_FILE), false, '1.0.0' );
					wp_enqueue_style('izeechat-style-fonts',plugins_url('includes/css/fonts/fonts.css',ROOT_FILE), false, '1.0.0' );
				}
				if ( IzeeContainer::getRequestInst()->page == 'dashboard' ) {
					wp_enqueue_script('izeechat-script',plugins_url('includes/js/admin.js',ROOT_FILE), array('jquery'),'',1);
					wp_enqueue_style('izeechat-style-dashboard',plugins_url('includes/css/dashboard.css',ROOT_FILE), false, '1.0.0' );
					wp_enqueue_style('izeechat-style-menu',plugins_url('includes/css/menu.css',ROOT_FILE), false, '1.0.0' );
				}
			}
		}
	}

	/**
	 * Ajouter des rôles personnalisés à Wordpress
	 * 
	 * @param string  $newRole     Nom du rôle à créer
	 * @param string  $displayName Nom visible du rôle
	 * @param boolean $role        Rôle à cloner
	 * @param array   $caps        Permissions associées
	 * @author  Kevin B. Apizee Inc
	 */
	public function addRole($newRole, $displayName, $role=false, $caps=NULL) {
		if ($role != false) :
			$adm = get_role($role);
			add_role($newRole, $displayName, $adm->capabilities);
		else :
			add_role($newRole, $displayName, $caps);
		endif;
	}

	/**
	 * Mettre à jour le rôle d'un utilisateur
	 * 
	 * @param  string  $old_role Rôle courant de l'utilisateur
	 * @param  string  $new_role Nouveau rôle de l'utilisateur
	 * @param  integer $id       ID de l'utilisateur
	 * @author  Kevin B. Apizee Inc
	 */
	public function updateAgentRole($old_role, $new_role, $id=NULL) {
		$blogusers = get_users('role='.$old_role);
		
			foreach ($blogusers as $bloguser) :
				if ( !$id ) :
					wp_update_user( array( 'ID' => $bloguser->ID, 'role' => $new_role ) );
				else :
					if ( $bloguser->ID == $id ) :
						wp_update_user( array( 'ID' => $id, 'role' => $new_role ) );
					endif;
				endif;
			endforeach;
	}

	/**
	 * Retourne le header à afficher sur les pages
	 * 
	 * @return stdio HTML du header
	 * @author  Kevin B. Apizee Inc
	 */
	public function getHeader() {
		echo '
		<div id="overlay"></div>
		<div id="overlay2"></div>
		<div id="load"><img src="'.plugins_url('includes/img/loading.gif',ROOT_FILE).'" /></div>
		<div id="login-logo" class="margin text-center">
			<a href="'.__("http://www.apizee.com/izeechat/", "Izeechat").'" target="_BLANK"><img alt="Apizee" id="logo_izeechat" src="'.plugins_url('includes/img/logo_izeechat.png',ROOT_FILE).'" width="350"></a>
		</div>';
	}

	/**
	 * Retourne le footer à affciher sur les pages
	 * 
	 * @return stdio HTML du footer
	 * @author  Kevin B. Apizee Inc
	 */
	public function getFooter() {
		//echo '
		//	<div id="apizee_foot">
		//		<p>'.__("Apizee - Easy Web Communications.", "Izeechat").'</p>
		//	</div>';
		echo '<p class="apizee_licence"><a target="_BLANK" style="color:#fff;" href="'.__("//www.apizee.com", "Izeechat").'" />Apizee</a> 2013-'.date('Y').'<br/>'.__("Licence GPL2", "Izeechat").'</p>';
	}

	/**
	 * Mettre à jour la clé de site
	 * 
	 * @param  string $email  Adresse email de l'administrateur
	 * @param  string $domain Nom de domaine associé à la clé de site
	 * @param  string $key    Clé de site
	 * @return boolean  True/False
	 * @author  Kevin B. Apizee Inc
	 */
	public function updateSitekey($email, $domain, $key) {
		global $wpdb;

		if ( $wpdb->query( $wpdb->prepare( "UPDATE izeechat SET site_domain = %s, site_key = %s WHERE email='".$email."' ", $domain, $key ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Retourne le formulaire pour créer un agent
	 * 
	 * @return stdio HTML du formulaire
	 * @author  Kevin B. Apizee Inc
	 */
	public function createAgent() {
		return '
		<form method="post">
			<div class="body stick">
				<div class="form-group">
					<input name="agent_name" class="form-control" size="30" placeholder="'.__("Username", "Izeechat").'" type="text" required="required">
				</div>
				<div class="form-group">
					<input name="agent_lastname" class="form-control" size="30" placeholder="'.__("Lastname", "Izeechat").'" type="text" required="required">
				</div>
				<div class="form-group">
					<input name="agent_firstname" class="form-control" size="30" placeholder="'.__("Firstname", "Izeechat").'" type="text" required="required">
				</div>
				<div class="form-group">
					<input name="agent_email" class="form-control" size="30" placeholder="'.__("Email Address", "Izeechat").'" type="email" required="required">
				</div>
				<div class="form-group">
					<input name="create_password" class="form-control" size="30" placeholder="'.__("Password", "Izeechat").'" type="password" id="pass1" onkeyup="checkPass(); return false;" required="required">
				</div>
				<div class="form-group">
					<input name="create_password2" class="form-control" size="30" placeholder="'.__("Confirm Password", "Izeechat").'" type="password" id="pass2" onkeyup="checkPass(); return false;" required="required">
				</div>
				<input type="submit" name="create_submit" class="btn bg-orange btn-block" value="'.__("Create Account", "Izeechat").'" />
			</div>
		</form>';
	}

	/**
	 * Rediriger vers une autre page
	 * 
	 * @param  string $page page vers laquelle on doit effectuer la redirection
	 * @author  Kevin B. Apizee Inc
	 */
	function redirect($page) {
		wp_redirect( get_option('siteurl').'/wp-admin/admin.php?page=izeechat-'.$page ); 
		exit; 
	}

	/**
	 * Afficher les erreurs
	 * @return stdio HTML de l'erreur
	 * @author  Kevin B. Apizee Inc
	 */
	public function showError() {
		if (isset($_GET)) {
			if (isset($_GET['exc']) && !empty($_GET['exc'])) {

				$err = base64_decode($_GET['exc']);
				$err = explode('##', $err);
				if($err[1]) 
					$mess = __($err[0], "Izeechat").'.<br/>'.__("Reason:","Izeechat").' '.__($err[1], "Izeechat");
				else
					$mess = __($err[0], "Izeechat");

				return '
					<div class="izeeException">
						<span class="error">'.$mess.'</span>
						<span class="close"></span>
					</div>';
			}
		}
	}
}

?>