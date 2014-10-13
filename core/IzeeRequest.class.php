<?php

class IzeeRequest {

	public $url;
	public $request_page;
	public $page;

	public function __construct() {
		$page_request = $_REQUEST['page'];
		$page_prefix  = 'izeechat-';
		$page_pattern = '#^('.$page_prefix.')([a-zA-Z]*)$#';
		preg_match($page_pattern, $page_request, $page);
		$this->request_page = $page_request;
		$this->page = $page[2];

		if ( $_SERVER ) {
			if ( (isset($_SERVER['HTTP_HOST']) ) && ( !empty($_SERVER['HTTP_HOST']) ) )
				$this->url = $_SERVER['HTTP_HOST'];
			else
				$this->url = $_SERVER['SERVER_NAME'];
		}
	}

	function getCurrentDomain($domain) {
		if ( $_SERVER ) :
			if ( (isset($_SERVER['HTTP_HOST']) ) && ( !empty($_SERVER['HTTP_HOST']) ) ) :
				$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			else :
				$url = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			endif;
		endif;
		// chrome, opera
		if ( substr($url,-1,1) == "/" ) $url = substr($url,0,-1);

		$domain = strtolower($domain);
		$pattern 	= '#^('.$domain.')(.*)$#';
		preg_match($pattern, $url, $matches);

		$result = ( $matches[1] == $domain ) ? true : false ;

		return $result;
	}
}

?>