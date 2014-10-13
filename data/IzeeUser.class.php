<?php

class IzeeUser {

	private $_API;
	public $userInfos;
	public $agentInfos;
	public $agents;

	/**
	 * Constructeur
	 *
	 * @author  Kevin B. Apizee Inc
	 */
	public function __construct() {

		$this->_API = new IzeeAPI(IzeeContainer::getCoreInst()->config['api']['host']);

		if ( !function_exists('wp_get_current_user') ) include( ABSPATH . "wp-includes/pluggable.php" ); 
			$user = wp_get_current_user();

		$this->userInfos = array(
			"email"        => $user->user_email,
			"password"     => $user->user_pass,
			"id"           => $user->ID,
			"role"         => ($user->roles[0]) ? $user->roles[0] : $user->roles[1],
			"roles"        => $user->roles,
			"display_name" => $user->display_name
		);

		$this->agentInfos = array(IzeeContainer::getBddInst()->getResultTable("SELECT * FROM izeechat WHERE id=1"));

		array_push($this->agentInfos, IzeeContainer::getBddInst()->getResultsTable("SELECT * FROM izeechat_users WHERE email='".$this->userInfos['email']."'"));

		$this->agents = IzeeContainer::getBddInst()->getResultsTable("SELECT email FROM izeechat_users", "push");
		// Récupérer passwords décryptés
		$decrypt = IzeeContainer::getCipherInst()->decrypt($this->agentInfos[0]['password']);
		$this->agentInfos[0]['password'] = $decrypt;

		$decryptCloud = IzeeContainer::getCipherInst()->decrypt($this->agentInfos[1][0]['cloud_password']);
		$this->agentInfos[1][0]['cloud_password'] = $decryptCloud;

		// Récuprer les infos agent de cloud pour loader comBox
		$getUserID = IzeeContainer::getApiInst()->getUserID(
				$this->agentInfos[0]['email'], 
				$this->agentInfos[0]['password'], 
				$this->userInfos['email']
		);
		$this->agentInfos['userId'] = $getUserID['uid'];
		$this->agentInfos['nickname'] = $getUserID['first_name'].' '.$getUserID['last_name'];
	}

	/**
	 * Ajouter de nouveaux rôles personnalisés à Wordpress
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function addUserRoles() {
		// Rôle Administrateur Agent
		IzeeContainer::getUtilsInst()->addRole( "izeechat_admin", "IzeeChat Admin", "administrator" );
		// Rôle Agent
		IzeeContainer::getUtilsInst()->addRole( "izeechat_agent", "IzeeChat Agent", false, array( "read"=>true,"edit_posts"=>false,"delete_posts"=>false ) );
	}

	/**
	 * Supprimer les rôles personnalisés de Wordpress
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function remUserRoles() {
		// Restaurer le rôle par défaut pour les utilisateurs
		IzeeContainer::getUtilsInst()->updateAgentRole('izeechat_admin', 'administrator');
		IzeeContainer::getUtilsInst()->updateAgentRole('izeechat_agent', 'subscriber');
		// Supprimer nos rôles
		remove_role( 'izeechat_admin' );
		remove_role( 'izeechat_agent' );
	}

	/**
	 * Générer le menu du plugin dans la zone admin
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function genMenu() {
		$auth = $this->authentication();
		$admin_menu = new IzeeMenu();

		if ($auth == 'restricted') {
			add_utility_page('IzeeChat', 'IzeeChat', 'read', 'izeechat-home', array($admin_menu,'activationView'), plugins_url('includes/img/logo-apizee.png',ROOT_FILE), 30);
		} 
		elseif ($auth == 'full') {
			add_utility_page('IzeeChat', 'IzeeChat', 'read', 'izeechat-home', array($admin_menu,'adminView'), plugins_url('includes/img/logo-apizee.png',ROOT_FILE), 30);
		}
		elseif ( $auth == 'toRegist') {
			add_utility_page('IzeeChat', 'IzeeChat', 'read', 'izeechat-home', array($admin_menu,'userView'), plugins_url('includes/img/logo-apizee.png',ROOT_FILE), 30);
		}
		else {
	   		add_utility_page('IzeeChat', 'IzeeChat', 'read', 'izeechat-home', array($admin_menu,'activationView'), plugins_url('includes/img/logo-apizee.png',ROOT_FILE), 30);
		}
	}

	/**
	 * Authentification interne de l'utilisateur courant pour définir ces droits
	 *
	 * @return string  Retourne le tag permettant d'identifier le level de l'utilisateur
	 * @author  Kevin B. Apizee Inc
	 */
	public function authentication() {
		$access     = $this->userInfos['password']."authorized";
		$regist     = $this->userInfos['password']."registrationRequired";
		$deactivate = $this->userInfos['password']."useraccessdeactivate";
		$noaccess   = $this->userInfos['password']."noauthorized";

		$cipher 	= IzeeContainer::getCipherInst();
		$decrypt    = $cipher->decrypt($_COOKIE['wordpress_izeechat_access']);

		$user_status   = $this->agentInfos[1][0]['status'];

		// si dans la zone admin
		if ( is_admin() ) {
			// si le plugin est actif
			if ( $this->agentInfos[0]['activation'] == 1 ) {
				
				// le cookie éxiste
				if ( $_COOKIE['wordpress_izeechat_access'] != " " ) {
					// le cookie authorise l'utilisateur courant
					if ( $decrypt == $access && $user_status == 1 ) {
						return "full";
					}
					elseif ( $decrypt == $access && $user_status == 0 ) {
						return "toRegist";
					}
					// l'utilisateur doit s'authentifier
					elseif ( $decrypt == $regist && $user_status == 0 ) {
						return "toRegist";
					}
					// le cookie n'authorise pas l'utilisateur courant
					elseif ( $decrypt == $noaccess ) {
						return "restricted";
					}
					// 
					elseif ( $decrypt == $deactivate ) { 
						return "deactivate";
					// sinon
					} else {
						// supprimer le cookie
						setcookie("wordpress_izeechat_access", "", time()-3600 );
					}
				}

				// si c'est un admin ou un agent
				if( ($this->verifyRole('admin') || $this->verifyRole('agent')) && ($user_status == 1) ) {
					// on authorise l'accès
					setcookie("wordpress_izeechat_access", $cipher->encrypt($access), 0, '/');
					return "full";
				}

				// si l'utilisateur fait parti des agents
				if ( in_array($this->userInfos['email'], unserialize($this->agentInfos[0]['users'])) ) {
					// si il est actif
					if ( $user_status == 1 ) {
						// on authorise l'accès
						setcookie("wordpress_izeechat_access", $cipher->encrypt($access), 0, '/');
						// si l'utilisateur n'est pas un admin ou un agent ou un admin wp
						if ( !$this->verifyRole('admin') && !$this->verifyRole('agent') && !$this->verifyRole('adminwp') ) 
							wp_update_user( array( 'ID' => $this->userInfos['id'], 'role' => 'izeechat_agent' ) ); // on met à jour son rôle en tant qu'agent
						return "full";
					// sinon
					} else {
						// on demande une authentification
						setcookie("wordpress_izeechat_access", $cipher->encrypt($regist), 0, '/');
						// si l'utilisateur n'est pas un admin ou un agent ou un admin wp
						if ( !$this->verifyRole('admin') && !$this->verifyRole('agent') && !$this->verifyRole('adminwp') ) {
							wp_update_user( array( 'ID' => $this->userInfos['id'], 'role' => 'izeechat_agent' ) ); // on met à jour son rôle en tant qu'agent
						}
						return "toRegist";
					}
				// sinon
				} else {
					// on n'authorise pas l'accès
					setcookie("wordpress_izeechat_access", $cipher->encrypt($regist), 0, '/');
					return "toRegist";
				}
			// sinon
			} else {
				return false;
			}
		}
	}

	/**
	 * Vérifie le rôle d'un utilisateur
	 * 
	 * @param  string $role Nom du rôle à vérifier
	 * @return boolean  True/False
	 * @author  Kevin B. Apizee Inc
	 */
	public function verifyRole($role){
		if ( is_user_logged_in() ) :
			if ($role == "admin") :
				return in_array('izeechat_admin', $this->userInfos['roles']);
			endif;
			if ($role == "agent") :
				return in_array('izeechat_agent', $this->userInfos['roles']);
			endif;
			if ($role == "adminwp") :
				return in_array('administrator', $this->userInfos['roles']);
			endif;
		endif;
	}

	/**
	 * Afficher les agents ainsi que leur statut
	 * 
	 * @return stdio Table listant l'ensemble des agents
	 * @author  Kevin B. Apizee Inc
	 */
	public function listAgents() {

		$list = '<table class="table-striped table-hover table">';
		$agents = unserialize($this->agentInfos[0]["users"]);
		$agentProfileIds = unserialize(IzeeContainer::getUtilsInst()->agentProfileIds);
		$agentIds = unserialize(IzeeContainer::getUtilsInst()->agentsIds);
		$allAgents = unserialize(IzeeContainer::getUtilsInst()->agentsProfile)['users'];
		$i = 0;


		foreach ($agents as $agent) {
			$img = '//cloud.apizee.com/index.php/sf_guard_user_profile/getImage?id='.$agentProfileIds[$i];
			$nickname = $allAgents[$i]['firstName'].' '.$allAgents[$i]['lastName'];
			$list .= '<tr>
						<td class="agentphoto">
							<img width="40" height="40" src="'.$img.'" />
						</td>
						<td><span class="agent_name">'.$nickname.'</span></td>
						<td>
							<span data="'.$nickname.'" class="intracomUserStatus" rel="'.$agentIds[$i].'" title="'.$nickname.'"></span>
						</td>
					</tr>';
			$i++;
		}
		$list .= '</table>';

		return $list;

	}

	/**
	 * Mettre à jour le rôle d'un agent
	 * 
	 * @param  string  $old_role Ancien rôle
	 * @param  string  $new_role Nouveau rôle
	 * @param  boolean $id       ID de l'utilisateur
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
}


?>