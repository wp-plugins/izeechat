<?php

class IzeeCore {

	public $config;
	public $myTables;

	public function __construct() {
		
		register_activation_hook( ROOT_FILE, array($this, 'install'));	// Activation du plugin
		register_deactivation_hook( ROOT_FILE, array($this, 'remove'));	// Désactivation du plugin

		add_action('init', array($this, 'initialization'));				// Initialisation (librairies,...)
		add_action('plugins_loaded', array($this, 'load'));				// Charger comBox	
		add_action('admin_menu', array($this, 'menu'));					// Creation du menu dans la zone admin

		// On récupère les configurations
		$this->config = include MYPLUGIN_ROOT.DS.'config.php';
		$this->myTables = include MYPLUGIN_ROOT.DS.'schema_database.php';

		$request = new IzeeRequest();
		// On enrichi notre conteneur
		IzeeContainer::setCoreInst($this);
		IzeeContainer::setRequestInst($request);
		IzeeContainer::setBddInst(new IzeeDatabase());
		IzeeContainer::setCipherInst(new IzeeCipher());
		IzeeContainer::setApiInst(new IzeeAPI($this->config['api']['host']));
		IzeeContainer::setUserInst(new IzeeUser());
		IzeeContainer::setComboxInst(new IzeeCombox($this->config['api']['host']));
		IzeeContainer::setUtilsInst(new IzeeUtils());
		IzeeContainer::setUiInst(new IzeeUI());

		IzeeContainer::getUtilsInst()->isExist($this->config['api']['host'], $this->myTables);
	}

	public function install() {
		// Créer les tables nécessaires tables dans la base
		foreach ($this->myTables as $table => $content) {
			IzeeContainer::getBddInst()->createTable($table, $content);
		}
		// Créer les nouveaux rôles nécessaire pour le plugin
		IzeeContainer::getUserInst()->addUserRoles();
	}

	public function remove() { 
		global $wpdb;

		// Supprimer les tables créées avec le plugin
		foreach ($this->myTables as $table => $content) {
			$wpdb->query("DROP TABLE IF EXISTS ".$table);
		}
		// Supprimer les rôles créés avec le plugin
		IzeeContainer::getUserInst()->remUserRoles();
	}

	public function initialization() {
		// Charger la langue
		IzeeContainer::getUtilsInst()->loadLang();
		// Charger les scripts js et styles css
		IzeeContainer::getUtilsInst()->loadJsCss();
	}

	public function load() {
		// Si on est dans wp-admin
		if (is_admin() && (PLUGIN_DISPLAY == "enable")) :
			if ( IzeeContainer::getRequestInst()->request_page ) :
				// Si notre page est différente de la page 'dashboard'
				if ( IzeeContainer::getRequestInst()->page != "dashboard" ) :
					// On ajoute un hook pour ajouter comBox dans wp-admin
					add_action('in_admin_footer', array($this, 'inject'));
				endif;
			endif;
		endif;
		// On ajoute un hook pour ajouter comBox sur le frontend
		add_action('wp_footer', array($this, 'inject'));
	}

	public function inject() {

		$userInfos  = IzeeContainer::getUserInst()->userInfos;
		$agentInfos = IzeeContainer::getUserInst()->agentInfos;
		$siteInfos  = IzeeContainer::getUserInst()->agentInfos[0];

		if ( IzeeContainer::getRequestInst()->getCurrentDomain($siteInfos['site_domain']) == true ) {
			if ( IzeeContainer::getUserInst()->verifyRole("admin") || IzeeContainer::getUserInst()->verifyRole("agent") ) {
				echo IzeeContainer::getComboxInst()->agent($agentInfos['userId'], $agentInfos[0]['api_rtckey'], $agentInfos['nickname']); 
			} else {
				echo IzeeContainer::getComboxInst()->visitor($siteInfos['site_key']);
			}
            IzeeContainer::getUtilsInst()->updateSitekey($agentInfos[0]['email'], $siteInfos['site_domain'], $siteInfos['site_key']); 
	    }
	}

	public function menu() {
		IzeeContainer::getUserInst()->genMenu();
	}

}

?>