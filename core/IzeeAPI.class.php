<?php

class IzeeAPI {

	private $_apihost;

	/**
	* Function __construct, construct a new object with this attributs
	* 
	* @param $apihost = url of Cloud Api's Host
	* @author  Kevin B. Apizee Inc
	*/
	function __construct($apihost) {
		$this->_apihost	 = $apihost;
	}


	/**
	* Functions getEnterpriseInfo
	* 
	* @param $admin_password	= password of the admin user
	* @param $enterpriseId		= id of the enterprise
	* @param $domain 			= domain url
	* @return $tab      		= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	protected function getEnterpriseInfo($admin_username, $admin_password, $enterpriseId, $domain = NULL) {
		//prepare new request
		$setdata = http_build_query(
			array(
				'admin_username' => $admin_username,
				'admin_password' => $admin_password,			
				'enterpriseId'   => $enterpriseId,
				'domain' 		 => $domain
			)
		);
		$opts = array('http'=>
			array(
				'header'	=>	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
                'method'	=>	'POST',
                'content' 	=>	$setdata
			)
		);
		$context = stream_context_create($opts);
		$request = $this->_apihost.'/index.php/api/getEnterpriseUsers?'.$setdata;
		$json	 = file_get_contents($request, false, $context);

		$obj = json_decode($json, true);

		//--------//
		$tab = array();
		$tab['result']        = ( $obj['resultCode'] == "OK" ) ? true : false;
		$tab['eid']           = ( isset( $obj['enterpriseId'] ) && !empty( $obj['enterpriseId']) ) ? $obj['enterpriseId'] : NULL;
		$tab['apiRtcKey'] 	  = ( isset( $obj['enterpriseKey'] ) && !empty( $obj['enterpriseKey']) ) ? $obj['enterpriseKey'] : NULL;
		$tab['siteKey']       = ( isset( $obj['siteKey'] ) && !empty( $obj['siteKey']) ) ? $obj['siteKey'] : NULL;
		$tab['json']		  = serialize($obj);
		//--------//
		$tmp          		  = ( isset( $obj['users']) && !empty( $obj['users']) ) ? $obj['users'] : NULL;
		$tab['users'] 		  = array();
		$tab['ids'] 	  	  = array();
		$tab['profileIds'] 	  = array();
		for ( $i=0;$i<count($tmp);$i++ ) : array_push( $tab['ids'], $tmp[$i]['id'] ); endfor;
		$tab['ids'] = serialize($tab['ids']);
		for ( $i=0;$i<count($tmp);$i++ ) : array_push( $tab['users'], $tmp[$i]['email'] ); endfor;
		$tab['users'] = serialize($tab['users']);
		for ( $i=0;$i<count($tmp);$i++ ) : array_push( $tab['profileIds'], $tmp[$i]['profileId'] ); endfor;
		$tab['profileIds'] = serialize($tab['profileIds']);

		foreach ($obj['users'] as $value) {
			if ($value["email"] == $admin_username) {
				$tab['firstname'] = $value["firstName"];
				$tab['lastname'] = $value["lastName"];
			}
		}
		//--------//

		return $tab;
	}

	/**
	* Functions getConfiguration
	* 
	* @param $admin_password	= password of the admin user
	* @param $enterpriseId		= id of the enterprise
	* @param $domain 			= domain url
	* @return $tab      		= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	public function getConfiguration($admin_username, $admin_password, $enterpriseId, $domain = NULL) {
		return $this->getEnterpriseInfo($admin_username, $admin_password, $enterpriseId, $domain);
	}

	/**
	* Function subscription
	* 
	* @param $last_name			= last name of enterprise admin
	* @param $first_name		= first name of enterprise admin
	* @param $email 			= email of enterprise admin
	* @param $domain 			= domain name/url of enterprise where subscriber is admin
	* @return $tab      		= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	public function subscription($first_name, $last_name, $password, $email, $domain) {
		//prepare new request
		$setdata = http_build_query(
			array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'password'	 => $password,
				'email'      => $email,				
				'domain'     => $domain,
				'cms'		 => "wordpress"
			)
		);
		$opts = array('http'=>
			array(
				'header'	=>	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
                'method'	=>	'POST',
                'content' 	=>	$setdata
			)
		);
		$context = stream_context_create($opts);
		$request = $this->_apihost.'/index.php/api/selfCreateAccount?'.$setdata;
		$json	 = file_get_contents($request, false, $context);

		$obj = json_decode($json, true);

		//--------//
		$tab = array();
		$tab['result'] 		  = ( $obj['resultCode'] == "OK" ) ? true : false;
		$tab['apiRtcKey'] 	  = ( isset( $obj['apiRtcKey'] ) && !empty( $obj['apiRtcKey']) ) ? $obj['apiRtcKey'] : NULL;
		$tab['siteKey']       = ( isset( $obj['siteKey'] ) && !empty( $obj['siteKey']) ) ? $obj['siteKey'] : NULL;
		$tab['json']		  = serialize($obj);
		//--------//

		return $tab;
	}

	/**
	* Function subscriptionAgent
	* 
	* @param $last_name			= last name of enterprise admin
	* @param $first_name		= first name of enterprise admin
	* @param $email 			= email of enterprise admin
	* @param $domain 			= domain url
	* @return $tab      		= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	public function subscriptionAgent($admin_username, $admin_password, $last_name, $first_name, $email, $password, $enterpriseId) {
		//prepare new request
		$setdata = http_build_query(
			array(
				'admin_username'  => $admin_username,
				'admin_password'  => $admin_password,
				'last_name'       => $last_name,
				'first_name'      => $first_name,
				'email'           => $email,
				'password'		  => $password,
				'enterpriseId'    => $enterpriseId
			)
		);
		$opts = array('http'=>
			array(
				'header'	=>	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
                'method'	=>	'POST',
                'content' 	=>	$setdata
			)
		);
		$context = stream_context_create($opts);
		$request = $this->_apihost.'/index.php/api/createUser?'.$setdata;
		$json	 = file_get_contents($request, false, $context);

		$obj = json_decode($json, true);

		//--------//
		$tab = array();
		$tab['result'] 	= ( $obj['resultCode'] == "OK" ) ? true : false;
		$tab['json']	= serialize($obj);
		//--------//

		return $tab;
	}

	/**
	* Function remove
	*
	* @param $admin_username 	= email of the admin user
	* @param $admin_password	= password of the admin user
	* @param $userId			= ID of the user 
	* @return $tab 				= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	public function remove($admin_username, $admin_password, $userId) {
		$setdata = http_build_query(
            array(
            	// 
                'admin_username' 	=> $admin_username,
                'admin_password' 	=> $admin_password,
                'userId'  			=> $userId
            )
        );
        $opts = array('http'=>
            array(
                'header' 	=> 	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
               	'method'  	=> 	'POST',
               	'content' 	=> 	$setdata
            )
        );
        $context  = stream_context_create($opts);
        $request  = $this->_apihost.'/index.php/api/deleteUser?'.$setdata;
        $json     = file_get_contents($request, false, $context);

        $obj = json_decode($json, true);
		
		//--------//
		$tab = array();
		$tab['result'] 	= ( $obj['resultCode'] == "OK" ) ? true : false;
		$tab['json']	= serialize($obj);
		//--------//

		return $tab;
	}

	/**
	* Function newSite
	* 
	* @param $admin_username	= email of the admin user
	* @param $admin_password	= password of the admin user
	* @param $sitename 			= domain name
	* @param $domain 			= domain url
	* @return $tab      		= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	public function newSite($admin_username, $admin_password, $sitename, $domain) {
		//prepare new request
		$setdata = http_build_query(
			array(
				'admin_username' => $admin_username,
				'admin_password' => $admin_password,
				'name'           => $sitename,				
				'domain'         => $domain
			)
		);
		$opts = array('http'=>
			array(
				'header'	=>	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
                'method'	=>	'POST',
                'content' 	=>	$setdata
			)
		);
		$context = stream_context_create($opts);
		$request = $this->_apihost.'/index.php/api/createSite?'.$setdata;
		$json	 = file_get_contents($request, false, $context);

		$obj = json_decode($json, true);

		//--------//
		$tab = array();
		$tab['result']    = ( $obj['resultCode'] == "OK" ) ? true : false;
		$tab['apiRtcKey'] = ( isset( $obj['apiRtcKey'] ) && !empty( $obj['apiRtcKey']) ) ? $obj['apiRtcKey'] : NULL;
		$tab['siteKey']   = ( isset( $obj['siteKey'] ) && !empty( $obj['siteKey']) ) ? $obj['siteKey'] : NULL;
		$tab['json']      = serialize($obj);
		//--------//

		return $tab;
	}

	/**
	* Function getSiteKey
	* 
	* @param $admin_username	= email of the admin user
	* @param $admin_password	= password of the admin user
	* @param $domain 			= domain url
	* @return $tab      		= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	public function getSiteKey($admin_username, $admin_password, $domain) {
		//prepare new request
		$setdata = http_build_query(
			array(
				'admin_username' => $admin_username,
				'admin_password' => $admin_password,			
				'domain'         => $domain
			)
		);
		$opts = array('http'=>
			array(
				'header'	=>	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
                'method'	=>	'POST',
                'content' 	=>	$setdata
			)
		);
		$context = stream_context_create($opts);
		$request = $this->_apihost.'/index.php/api/GetSiteKey?'.$setdata;
		$json	 = file_get_contents($request, false, $context);

		$obj = json_decode($json, true);

		//--------//
		$tab = array();
		$tab['result'] 	= ( $obj['resultCode'] == "OK" ) ? true : false;
		$tab['json']	= serialize($obj);
		//--------//

		return $tab;
	}

	/**
	* Function getUserID
	* 
	* @param $admin_username	= admin of the admin user 	
	* @param $admin_password	= pasword of the admin user
	* @param $email 			= email of the user
	* @return $tab      		= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	public function getUserID($admin_username, $admin_password, $email) {
		//prepare new request
		$setdata = http_build_query(
			array(
				'admin_username' => $admin_username,
				'admin_password' => $admin_password,			
				'email'          => $email
			)
		);
		$opts = array('http'=>
			array(
				'header'	=>	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
                'method'	=>	'POST',
                'content' 	=>	$setdata
			)
		);
		$context = stream_context_create($opts);
		$request = $this->_apihost.'/index.php/api/getUserId?'.$setdata;
		$json	 = file_get_contents($request, false, $context);

		$obj = json_decode($json, true);

		//--------//
		$tab = array();
		$tab['result']     = ( $obj['resultCode'] == "OK" ) ? true : false;
		$tab['first_name'] = ( isset( $obj['firstName'] ) && !empty( $obj['firstName']) ) ? $obj['firstName'] : " ";
		$tab['last_name']  = ( isset( $obj['lastName'] ) && !empty( $obj['lastName']) ) ? $obj['lastName'] : " ";
		$tab['uid']        = ( isset($obj['userId']) && !empty($obj['userId']) ) ? $obj['userId'] : NULL;
		$tab['json']       = serialize($obj);
		$tab['reason']     = ( isset($obj['reason']) && !empty($obj['reason']) ) ? $obj['reason'] : NULL;
		//--------//

		return $tab;
	}

	/**
	* Function setUserStatus
	* 
	* @param $admin_username	= admin of the admin user 	
	* @param $admin_password	= pasword of the admin user
	* @param $userId 			= email of the user
	* @param $isActive			= Statut du compte utilisateur (true=actif/false=non actif)
	* @return $tab      		= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	public function setUserStatus($admin_username, $admin_password, $userId, $isActive, $enterpriseId = NULL) {
		//prepare new request
		$setdata = http_build_query(
			array(
				'admin_username' => $admin_username,
				'admin_password' => $admin_password,			
				'userId'         => $userId,
				'isActive'       => $isActive,
				'enterpriseId'   => $enterpriseId
			)
		);
		$opts = array('http'=>
			array(
				'header'	=>	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
                'method'	=>	'POST',
                'content' 	=>	$setdata
			)
		);
		$context = stream_context_create($opts);
		$request = $this->_apihost.'/index.php/api/setUserStatus?'.$setdata;
		$json	 = file_get_contents($request, false, $context);

		$obj = json_decode($json, true);

		//--------//
		$tab = array();
		$tab['result']     = ( $obj['resultCode'] == "OK" ) ? true : false;
		$tab['json']       = serialize($obj);
		$tab['reason']     = ( isset($obj['reason']) && !empty($obj['reason']) ) ? $obj['reason'] : NULL;
		//--------//

		return $tab;
	}

	/**
	* Function sendPasswordToUser
	* 
	* @param $username			= email of the destination
	* @param $password			= pasword to send
	* @param $mailType 			= ( "create" (default) | "reminder")
	* @return $tab      		= return true if request is execute with susccess else return false
	* @author  Kevin B. Apizee Inc
	*/
	public function sendPasswordToUser($username, $password, $mailType) {
		//prepare new request
		$setdata = http_build_query(
			array(
				'username' => $username,
				'password' => $password,			
				'mailType' => $mailType
			)
		);
		$opts = array('http'=>
			array(
				'header'	=>	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
                'method'	=>	'POST',
                'content' 	=>	$setdata
			)
		);
		$context = stream_context_create($opts);
		$request = $this->_apihost.'/index.php/api/getLoginByMail?'.$setdata;
		$json	 = file_get_contents($request, false, $context);

		$obj = json_decode($json, true);

		//--------//
		$tab = array();
		$tab['result']     = ( $obj['resultCode'] == "OK" ) ? true : false;
		$tab['json']       = serialize($obj);
		$tab['reason']     = ( isset($obj['reason']) && !empty($obj['reason']) ) ? $obj['reason'] : NULL;
		//--------//

		return $tab;
	}

    /**
	* Function login
	*
	* @param $username 	= email of the user
	* @param $password	= password of the user
	* @author  Kevin B. Apizee Inc
	*/
	public function login($username, $password) {
		$setdata = http_build_query(
            array(
            	// 
                'username' 	=> $username,
                'password' 	=> $password
            )
        );
        $opts = array('http'=>
            array(
                'header' 	=> 	"Content-Type: application/x-www-form-urlencoded\r\n".
                            	"Content-Length: ".strlen($setdata)."\r\n".
                            	"User-Agent:MyAgent/1.0\r\n",
               	'method'  	=> 	'POST',
               	'content' 	=> 	$setdata
            )
        );
        $context  = stream_context_create($opts);
        $request  = $this->_apihost.'/index.php/api/login?'.$setdata;
        $json     = file_get_contents($request, false, $context);

        $obj = json_decode($json, true);
		
		//--------//
		$tab = array();
		$tab['result'] 	= $obj['resultCode'];
		$tab['json']	= serialize($obj);
		//--------//

		return $tab;
	}
}
?>