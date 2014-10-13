<?php

class IzeeCombox {

	private $_host;

	/**
	 * Constructeur
	 * 
	 * @param string $host Adresse/domaine du serveur cloud
	 * @author  Kevin B. Apizee Inc
	 */
	public function __construct($host) {
		$this->_host = $host;
	}

	/**
	 * Script to load comBox on frontend
	 * 
	 * @param  string $siteKey Site Key unique
	 * @return string          Loader IzeeChat
	 * @author  Kevin B. Apizee Inc
	 */
	public function visitor($siteKey) {
		if(IzeeContainer::getUserInst()->agentInfos[0][box_display] == 'enable') {
			return ' 
			<script type="text/javascript" src="//cloud.apizee.com/contactBox/loaderIzeeChat.js"></script>
			<script>loaderIzeeChat("'.$siteKey.'", { "serverDomainRoot" : "//cloud.apizee.com/"});</script>
			';
		}
		return false;
	}

	/**
	 * Script to load comBox on backend
	 * 
	 * @param  string $userId   ID de l'agent
	 * @param  string $apiCCkey ApiCCKey unique
	 * @param  string $nickname Nom qui sera affiché dans la box
	 * @return string           Loader Agent
	 * @author  Kevin B. Apizee Inc
	 */
	public function agent($userId, $apiCCkey, $nickname) {
		return '
		<script type="text/javascript" src="'.$this->_host.'/agent/loaderAgent.js"></script>
        <script type="text/javascript">
          	var userInfos = {
                id:'.$userId.',
                nickname:"'.$nickname.'",
                photoUrl: "'.$this->_host.'/frontend_dev.php/sf_guard_user_profile/getImage?id='.$userId.'"
            };
            console.log("userInfos",userInfos);
           	loadAgent( "'.$this->_host.'/", "'.$apiCCkey.'", userInfos, null, true, {"culture":"fr"} );
		</script>';
	}
}

?>