<?php
	/* 
	 * Author	: Stefan Wärting
	 * Date	: 2011-08-07
	 * Website	: http://warting.se/
	 * Version	: 1.0
	 *
	 * Usage: 
	 *		do_action('LogInfo', 'info log');
	 *		do_action('LogDebug', 'debug log');
	 *		do_action('LogWarn', 'warn log');
	 *		do_action('LogError', array('error array log'));
	 *		do_action('LogFatal', 'Fatal log');
	*/

	class WISLogger {
		const DEBUG 	= 1;	// Most Verbose
		const INFO 		= 2;	// ...
		const WARN 		= 3;	// ...
		const ERROR 	= 4;	// ...
		const FATAL 	= 5;	// Least Verbose
		const OFF 		= 6;	// Nothing at all.

		
		/* Public members: Not so much of an example of encapsulation, but that's okay. */

		public $DateFormat	= "Y-m-d G:i:s";
		public $MessageQueue;
		
		private $UDPEnabled = false;
		private $UDPIP = '127.0.0.1';
		private $UDPPort = 8080;
		
		private $GrowlEnabled = false;
		private $Growl;
		
		private $fileEnabled = false;
		private $filePath = './';
		
		private $alloptions = array();
		
		private static $instance = NULL;
		
		private $file_handle;
		
	    public static function getInstance() {
	    	if (!WISLogger::$instance) {
	    		WISLogger::$instance = new WISLogger();
	    	}
	    	return WISLogger::$instance;
	    }
		
		
		public function __construct(  ) {
			
			$this->allOptions = get_option( 'wis-logger-settings' );
			if (!$this->allOptions) $this->allOptions = array();
			
			$this->UDPEnabled = isset($this->allOptions['enableUDPLog']);
			if(isset($this->allOptions['UDPIP'])) $this->UDPIP = $this->allOptions['UDPIP'];
			if(isset($this->allOptions['UDPPort']) && !empty($this->allOptions['UDPPort'])) $this->UDPPort = $this->allOptions['UDPPort'];
			
			
			$this->GrowlEnabled = isset($this->allOptions['enableGrowlLog']);
			
			if($this->GrowlEnabled) {
				$GrowlIP = '127.0.0.1';
				$GrowlPassword = '';
				
				if(isset($this->allOptions['GrowlIP'])) $GrowlIP = $this->allOptions['GrowlIP'];
				if(isset($this->allOptions['GrowlPassword']) && !empty($this->allOptions['GrowlPassword'])) $GrowlPassword = $this->allOptions['GrowlPassword'];
				include_once(dirname(__FILE__).'/Growl.php');
				$this->Growl = new Growl($GrowlIP, $GrowlPassword, 'wis-logger');
				
				$this->Growl->addNotification('wis-logger');
				$this->Growl->register();
			}
			
			 
			$this->fileEnabled = isset($this->allOptions['enableFileLog']);
			
			$this->filePath = isset($this->allOptions['filePath'])&&!empty($this->allOptions['filePath'])?$this->allOptions['filePath']:dirname(dirname(__FILE__)).'/log/log.txt';
			
			if($this->fileEnabled) {
				if ( $this->file_handle = @fopen( $this->filePath , "a" ) )
				{
					$this->MessageQueue[] = "The log file was opened successfully.";
				}
				else {
					$this->fileEnabled = false;
					$this->MessageQueue[] = "The file could not be opened. Check permissions.";
					//$this->LogWarn("The file [".$this->filePath."] could not be opened for writing. Check permissions.");
				}
			}

			
			
			$this->MessageQueue = array();
		}
		public function __destruct() {
			if ($this->fileEnabled && $this->file_handle ) {
				fclose( $this->file_handle );
			}
		}
		
		public function adminMenu() {
			global $blog_id;
			register_setting('wis_logger_settings', 'wis-logger-settings');
			if ($blog_id == BLOG_ID_CURRENT_SITE) {
				add_submenu_page(
						"options-general.php", 
						"Loggning", 
						"Loggning", 
						"manage_sites", 
						"wis_logging_settings_page", 
				array($this, 'settingsPage'));
			}
		}
		
		
		public function settingsPage() {
			include dirname(dirname(__FILE__))."/templates/adminPage.php";
		}
		
		public function LogDebug($line) {
			$this->Log( $line , WISLogger::DEBUG );
		}
		
		public function LogInfo($line) {
			$this->Log( $line , WISLogger::INFO );
		}
		
		public function LogWarn($line) {
			$this->Log( $line , WISLogger::WARN );	
		}
		
		public function LogError($line) {
			$this->Log( $line , WISLogger::ERROR );		
		}

		public function LogFatal($line) {
			$this->Log( $line , WISLogger::FATAL );
		}
		
		private function Log($line, $priority)
		{
			if(is_object($line)) $line = print_r($line,true);
			if(is_array($line)) $line = print_r($line,true);
			$status = $this->getTimeLine( $priority );
			if($status != false) {
				$this->WriteFreeFormLine ( "$status $line \n" );
			}
		}
		
		
		private function LogPHP($errno, $line)
		{
			$status = $this->getPHPTimeLine( $errno);
			if($status != false) {
				$this->WriteFreeFormLine ( "$status $line \n" );
			}
		}
		
		private function getTimeLine( $level ) {
			$time = date( $this->DateFormat );
			switch( $level ) {
				case WISLogger::INFO:
					return isset($this->allOptions['WISE_INFO'])?"$time - INFO  -->":false;
				case WISLogger::WARN:
					return isset($this->allOptions['WISE_WARN'])?"$time - WARN  -->":false;
				case WISLogger::DEBUG:
					return isset($this->allOptions['WISE_DEBUG'])?"$time - DEBUG -->":false;
				case WISLogger::ERROR:
					return isset($this->allOptions['WISE_ERROR'])?"$time - ERROR -->":false;
				case WISLogger::FATAL:
					return isset($this->allOptions['WISE_FATAL'])?"$time - FATAL -->":false;
				default:
					return "$time - LOG   -->";
			}
		}
		
		private function getPHPTimeLine( $level, $default = 'LOG' ) {
			$time = date( $this->DateFormat );
			switch( $level ) {
				case E_PARSE:
					return isset($this->allOptions['E_PARSE'])?"$time - E_PARSE  -->":false;
				case E_CORE_ERROR:
					return isset($this->allOptions['E_CORE_ERROR'])?"$time - E_CORE_ERROR  -->":false;
				case E_CORE_WARNING:
					return isset($this->allOptions['E_CORE_WARNING'])?"$time - E_CORE_WARNING -->":false;
				case E_COMPILE_ERROR:
					return isset($this->allOptions['E_COMPILE_ERROR'])?"$time - E_COMPILE_ERROR -->":false;
				case E_COMPILE_WARNING:
					return isset($this->allOptions['E_COMPILE_WARNING'])?"$time - E_COMPILE_WARNING -->":false;
				case E_NOTICE:
					return isset($this->allOptions['E_NOTICE'])?"$time - E_NOTICE -->":false;
				case E_USER_NOTICE:
					return isset($this->allOptions['E_USER_NOTICE'])?"$time - E_USER_NOTICE -->":false;
				case E_WARNING:
					return isset($this->allOptions['E_WARNING'])?"$time - E_WARNING -->":false;
				case E_USER_WARNING:
					return isset($this->allOptions['E_USER_WARNING'])?"$time - E_USER_WARNING -->":false;
				case E_ERROR:
					return isset($this->allOptions['E_ERROR'])?"$time - E_ERROR  -->":false;
				case E_USER_ERROR:
					return isset($this->allOptions['E_USER_ERROR'])?"$time - E_USER_ERROR  -->":false;
				case E_DEPRECATED:
					return isset($this->allOptions['E_DEPRECATED'])?"$time - E_DEPRECATED  -->":false;
				case E_USER_DEPRECATED:
					return isset($this->allOptions['E_USER_DEPRECATED'])?"$time - E_USER_DEPRECATED  -->":false;
				case E_STRICT:
					return isset($this->allOptions['E_STRICT'])?"$time - E_STRICT  -->":false;
				default:
					return isset($this->allOptions['E_OTHER'])?"$time - $level   -->":false;
			}
		}
		
		public function php_error_handler($errno, $errstr, $errfile, $errline) {
			$this->LogPHP($errno,  $errstr.' in '.$errfile.' on line ' . $errline);
			
		    if (WP_DEBUG && ini_get("display_errors")) {
		        //printf ("<br />\n<b>%s</b>: %s in <b>%s</b> on line <b>%d</b><br /><br />\n", $errors, $errstr, $errfile, $errline);
		    }
		    if (WP_DEBUG && ini_get('log_errors')) {
		        //error_log(sprintf("PHP %s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline));
		    }
		    return true;
		}
		
		private function WriteFreeFormLine( $line ) {
			if($this->UDPEnabled) {
				$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
				$len = strlen($line);
				socket_sendto($sock, $line, $len,MSG_DONTROUTE, $this->UDPIP, $this->UDPPort);
				//socket_sendto($sock, $line, $len, MSG_DONTROUTE, '10.44.43.66', 8080);
				socket_close($sock);
			}
			
			if($this->GrowlEnabled) {
				$this->Growl->notify('wis-logger', 'log', $line, -2);
			}
			
			if ($this->fileEnabled && $this->file_handle ) {
				if (fwrite( $this->file_handle , $line ) === false) {
					$this->MessageQueue[] = "The file could not be written to. Check that appropriate permissions have been set.";
				}
			}
				
		}
		
		
	}


?>