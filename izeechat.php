<?php
/*
* Plugin Name: izeechat
* Plugin URI: http://www.apizee.com/izeechat/
* Description: IzeeChat is a video conferencing solution with text messaging, allowing you to communicate directly with your visitors. So, you can offer your help and your advice instantly to your visitors when they need it.
* Version: 1.1
* Author: Apizee
* Author URI: http://www.apizee.com/
* License: GPL2
*/

define("DS", DIRECTORY_SEPARATOR);

define("ROOT_FILE", __FILE__);
define("MYPLUGIN_ROOT", ABSPATH.PLUGINDIR.DS.'izeechat');
define("MYPLUGIN_CORE", MYPLUGIN_ROOT.DS."core");
define("MYPLUGIN_DATA", MYPLUGIN_ROOT.DS."data");

define("MYPLUGIN_WEBROOT", MYPLUGIN_ROOT.DS."includes");
define("MYPLUGIN_IMG", MYPLUGIN_WEBROOT.'img');
define("MYPLUGIN_CSS", MYPLUGIN_WEBROOT.'css');
define("MYPLUGIN_JS", MYPLUGIN_WEBROOT.'js');

require 'includes.php';

new IzeeCore();

require 'processing.php';

define("PLUGIN_DISPLAY", IzeeContainer::getUserInst()->agentInfos[0]["box_display"]);
define("PLUGIN_STATUS", IzeeContainer::getUserInst()->agentInfos[0]["status"]);

?>