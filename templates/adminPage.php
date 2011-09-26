<?php 



function cb_enableUDPLog() {
	print wis_logger_checkbox('enableUDPLog', 'false');
}

function cb_UDPip() {
	print wis_logger_textfield('UDPIP', '127.0.0.1');
}

function cb_UDPport() {
	print wis_logger_textfield('UDPPort', '8080');
}

function cb_enableFileLog() {
	print wis_logger_checkbox('enableFileLog', 'false');
}

function cb_filePath() {
	$defaulPath = dirname(dirname(__FILE__)).'/log/log.txt';
	print wis_logger_textfield('filePath', $defaulPath);
}

function cb_wis_logger_section() {

}


function wis_logger_textfield($setting, $default) {
	$options = get_option( 'wis-logger-settings' );
	if (!$options) $options = array();
	print  '<input type="text" name="wis-logger-settings['.$setting.']" value="' . (isset($options[$setting])&&!empty($options[$setting])?$options[$setting]:$default) .'" class="regular-text" />';
}
function wis_logger_passwordfield($setting, $default) {
	$options = get_option( 'wis-logger-settings' );
	if (!$options) $options = array();
	print  '<input type="password" name="wis-logger-settings['.$setting.']" value="' . (isset($options[$setting])&&!empty($options[$setting])?$options[$setting]:$default) .'" class="regular-text" />';
}

function wis_logger_checkbox($setting, $default) {
	$options = get_option( 'wis-logger-settings' );
	if (!$options) $options = array();
	print  '<input type="checkbox" name="wis-logger-settings['.$setting.']"' . (isset($options[$setting])?' checked="checked"':$default) .'" class="regular-text" />';
}


function wis_logger_dropdown($setting, $default, $availible) {
	$options = get_option( 'wis-logger-settings' );
	if (!$options) $options = array();
	
	$selectedVal = isset($options[$setting])?$options[$setting]:$default;
	
	$list = '<select name="wis-logger-settings['.$setting.']">';
	foreach($availible as $id=>$val) {
		$list .= '<option value="'.$id.'"'.($selectedVal==$id?'selected="selected"':'') .'>'.$val.'</option>';
	}
	$list .= '</select>';
	return $list;
}

  
add_settings_section('wis_logger_section' , "Serverinställningar för loggning", 'cb_wis_logger_section',  'wis_logging_settings_page');
add_settings_field('enableUDPLog', "Använd UDP loggning", 'cb_enableUDPLog', 'wis_logging_settings_page', 'wis_logger_section');
add_settings_field('UDPIP', "IP UDP", 'cb_UDPip', 'wis_logging_settings_page', 'wis_logger_section');
add_settings_field('UDPPort', "Port UDP", 'cb_UDPport', 'wis_logging_settings_page', 'wis_logger_section');


add_settings_field('enableFileLog', "Använd Fil-loggning", 'cb_enableFileLog', 'wis_logging_settings_page', 'wis_logger_section');
add_settings_field('filePath', "Sökväg till loggfil", 'cb_filePath', 'wis_logging_settings_page', 'wis_logger_section');


add_settings_field('enableGrowlLog', "Använd Growl notifiering", function($args) {
		wis_logger_checkbox($args[0],false);
	}, 'wis_logging_settings_page', 'wis_logger_section',array('enableGrowlLog'));
	
	
add_settings_field('GrowlIP', "Growl IP", function($args) {
		wis_logger_textfield($args[0],'127.0.0.1');
	}, 'wis_logging_settings_page', 'wis_logger_section',array('GrowlIP'));
	
add_settings_field('GrowlPassword', "Growl Password", function($args) {
		wis_logger_passwordfield($args[0],'');
	}, 'wis_logging_settings_page', 'wis_logger_section',array('GrowlPassword'));


$availiblePHPerrors = array('WISE_INFO','WISE_WARN','WISE_DEBUG','WISE_ERROR','WISE_FATAL');

foreach($availiblePHPerrors as $type){
	add_settings_field($type, $type, function($args) {
		wis_logger_checkbox($args[0],false);
	}, 'wis_logging_settings_page', 'wis_logger_section', array($type));	
}

$availiblePHPerrors = array('E_PARSE','E_CORE_ERROR','E_CORE_WARNING','E_COMPILE_ERROR','E_COMPILE_WARNING','E_NOTICE',
'E_USER_NOTICE','E_WARNING','E_USER_WARNING','E_ERROR','E_USER_ERROR','E_DEPRECATED','E_USER_DEPRECATED',
'E_STRICT','E_OTHER');

foreach($availiblePHPerrors as $type){
	add_settings_field('PHP_'.$type, 'PHP_'.$type, function($args) {
		wis_logger_checkbox($args[0],false);
	}, 'wis_logging_settings_page', 'wis_logger_section', array($type));
}




?>
<div class="wrap">
	<div class="icon32" id="icon-options-general"><br /></div>
	<h2>Inställningar för loggning</h2>


	<form method="post" action="options.php">	
		<?php 
			settings_fields( 'wis_logger_settings' );
			do_settings_sections('wis_logging_settings_page'); 
		?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
	
	
	<p>
		USAGE:<br />
		do_action('LogInfo', 'Sample LogInfo'); //shows if WISE_INFO i enabled<br />
		do_action('LogDebug', 'Sample LogDebug'); //shows if WISE_DEBUG i enabled<br />
		do_action('LogWarn', 'Sample LogWarn'); //shows if WISE_WARN i enabled<br />
		do_action('LogError', array('Sample array')); //shows if WISE_ERROR i enabled<br />
		do_action('LogFatal', 'Sample LogFatal'); //shows if WISE_FATAL i enabled<br />
	</p>
	
</div>