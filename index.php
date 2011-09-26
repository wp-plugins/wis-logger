<?php
/*                                                                                                                                                                                                                                                             
Plugin Name: wis-logger
Plugin URI: http://warting.se/wis-logger
Description: Logging errors to udp, file or Growl
Version: 0.1
Author: Stefan Wärting
Author URI: http://www.warting.se
Author Email: stefan@warting.se
*/


require_once 'lib/WISLogger.php';

add_action('admin_menu', array(WISLogger::getInstance(), 'adminMenu'));

add_action('LogInfo', array(WISLogger::getInstance(), 'LogInfo'));
add_action('LogDebug', array(WISLogger::getInstance(), 'LogDebug'));
add_action('LogWarn', array(WISLogger::getInstance(), 'LogWarn'));
add_action('LogError', array(WISLogger::getInstance(), 'LogError'));
add_action('LogFatal', array(WISLogger::getInstance(), 'LogFatal'));

set_error_handler(array(WISLogger::getInstance(), 'php_error_handler'));

/* USAGE:
do_action('LogInfo', 'Tjoho LogInfo');
do_action('LogDebug', 'Tjoho LogDebug');
do_action('LogWarn', 'Tjoho LogWarn');
do_action('LogError', array('dasd'));
do_action('LogFatal', 'Tjoho LogFatal');
*/

?>