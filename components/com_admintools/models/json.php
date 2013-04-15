<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// JSON API version number
define('ADMINTOOLS_JSON_API_VERSION', '319');

// Load framework base classes
jimport('joomla.application.component.model');

class AdmintoolsModelJson extends JModel
{
	const	STATUS_OK					= 200;	// Normal reply
	const	STATUS_NOT_AUTH				= 401;	// Invalid credentials
	const	STATUS_NOT_ALLOWED			= 403;	// Not enough privileges
	const	STATUS_NOT_FOUND			= 404;  // Requested resource not found
	const	STATUS_INVALID_METHOD		= 405;	// Unknown JSON method
	const	STATUS_ERROR				= 500;	// An error occurred
	const	STATUS_NOT_IMPLEMENTED		= 501;	// Not implemented feature
	const	STATUS_NOT_AVAILABLE		= 503;	// Remote service not activated

	const	ENCAPSULATION_RAW			= 1;	// Data in plain-text JSON
	const	ENCAPSULATION_AESCTR128		= 2;	// Data in AES-128 stream (CTR) mode encrypted JSON
	const	ENCAPSULATION_AESCTR256		= 3;	// Data in AES-256 stream (CTR) mode encrypted JSON
	const	ENCAPSULATION_AESCBC128		= 4;	// Data in AES-128 standard (CBC) mode encrypted JSON
	const	ENCAPSULATION_AESCBC256		= 5;	// Data in AES-256 standard (CBC) mode encrypted JSON

	private	$json_errors = array(
			'JSON_ERROR_NONE' => 'No error has occurred (probably emtpy data passed)',
			'JSON_ERROR_DEPTH' => 'The maximum stack depth has been exceeded',
			'JSON_ERROR_CTRL_CHAR' => 'Control character error, possibly incorrectly encoded',
			'JSON_ERROR_SYNTAX' => 'Syntax error'
			);

	/** @var int The status code */
	private	$status = 200;
	/** @var int Data encapsulation format */
	private $encapsulation = 1;
	/** @var mixed Any data to be returned to the caller */
	private $data = '';
	/** @var string A password passed to us by the caller */
	private $password = null;
	/** @var string The method called by the client */
	private $method_name = null;
	
	private function _getOption($key, $default)
	{
		static $config = null;
		
		if(is_null($config)) {
			if(defined('JVERSION')) {
				$j16 = version_compare(JVERSION,'1.6.0','ge');
			} else {
				$j16 = false;
			}
			
			$db =& JFactory::getDBO();
			
			if(!$j16) {
			$sql = "SELECT ".$db->nameQuote('params')." FROM ".$db->nameQuote('#__components')." WHERE (".
				$db->nameQuote('link')." = ".$db->Quote('option=com_admintools').") AND (".$db->nameQuote('parent')." = ".
				$db->Quote('0').")";
			$db->setQuery($sql);
			$config_ini  = $db->loadResult();
			} else {
				$config_ini = null;
			}

			if( $db->getErrorNum() || is_null($config_ini) )
			{
				// Maybe it's Joomla! 1.6?
				$sql = "SELECT ".$db->nameQuote('params')." FROM ".$db->nameQuote('#__extensions')." WHERE (".
					$db->nameQuote('type').' = '.$db->Quote('component').') AND ('.
					$db->nameQuote('element')." = ".$db->Quote('com_admintools').")";
				$db->setQuery($sql);
				$config_ini  = $db->loadResult();

				// OK, Joomla! 1.6 stores values JSON-encoded so, what do I do? Right!
				$config_ini = json_decode($config_ini, true);
				return $config_ini;
			}
			
			$config = self::parse_ini_file_php($config_ini, false, true);
		}
		
		if(array_key_exists($key, $config)) {
			return $config[$key];
		} else {
			return $default;
		}
	}
	
	public function execute($json)
	{
		// Check if we're activated
		$enabled = $this->_getOption('frontend_enable', 0);
		if(!$enabled)
		{
			$this->data = 'Access denied';
			$this->status = self::STATUS_NOT_AVAILABLE;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return $this->getResponse();
		}

		// Try to JSON-decode the request's input first
		$request = @$this->json_decode($json, false);
		if(is_null($request))
		{
			// Could not decode JSON
			$this->data = 'JSON decoding error';
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return $this->getResponse();
		}

		// Decode the request body
		// Request format: {encapsulation, body{ [key], [challenge], method, [data] }} or {[challenge], method, [data]}
		if( isset($request->encapsulation) && isset($request->body) )
		{
			// @todo Port AEUtilEncrypt from Akeeba Backup to Admin Tools
			if(!class_exists('AEUtilEncrypt') && !($request->encapsulation == self::ENCAPSULATION_RAW))
			{
				// Encrypted request found, but there is no encryption class available!
				$this->data = 'This server does not support encrypted requests';
				$this->status = self::STATUS_NOT_AVAILABLE;
				$this->encapsulation = self::ENCAPSULATION_RAW;
				return $this->getResponse();
			}

			// Fully specified request
			switch( $request->encapsulation )
			{
				case self::ENCAPSULATION_AESCBC128:
					if(!isset($body))
					{
						$request->body = base64_decode($request->body);
						$body = AEUtilEncrypt::AESDecryptCBC($request->body, $this->serverKey(), 128);
					}
					break;

				case self::ENCAPSULATION_AESCBC256:
					if(!isset($body))
					{
						$request->body = base64_decode($request->body);
						$body = AEUtilEncrypt::AESDecryptCBC($request->body, $this->serverKey(), 256);
					}
					break;

				case self::ENCAPSULATION_AESCTR128:
					if(!isset($body))
					{
						$body = AEUtilEncrypt::AESDecryptCtr($request->body, $this->serverKey(), 128);
					}
					break;

				case self::ENCAPSULATION_AESCTR256:
					if(!isset($body))
					{
						$body = AEUtilEncrypt::AESDecryptCtr($request->body, $this->serverKey(), 256);
					}
					break;

				case self::ENCAPSULATION_RAW:
					$body = $request->body;
					break;
			}

			if(!empty($request->body))
			{
				$body = rtrim( $body, chr(0) );
				$request->body = $this->json_decode($body);
				if(is_null($request->body))
				{
					// Decryption failed. The user is an imposter! Go away, hacker!
					$this->data = 'Authentication failed';
					$this->status = self::STATUS_NOT_AUTH;
					$this->encapsulation = self::ENCAPSULATION_RAW;
					return $this->getResponse();
				}
			}
		}
		elseif( isset($request->body) )
		{
			// Partially specified request, assume RAW encapsulation
			$request->encapsulation = self::ENCAPSULATION_RAW;
			$request->body = $this->json_decode($request->body);
		}
		else
		{
			// Legacy request
			$legacyRequest = clone $request;
			$request = (object) array( 'encapsulation' => self::ENCAPSULATION_RAW, 'body' => null );
			$request->body = $this->json_decode($legacyRequest);
			unset($legacyRequest);
		}

		// Authenticate the user. Do note that if an encrypted request was made, we can safely assume that
		// the user is authenticated (he already knows the server key!)
		if($request->encapsulation == self::ENCAPSULATION_RAW)
		{
			$authenticated = false;
			if(isset($request->body->challenge))
			{
				list($challenge,$check) = explode(':', $request->body->challenge);
				$crosscheck = strtolower(md5($challenge.$this->serverKey()));
				$authenticated = ($crosscheck == $check);
			}
			if(!$authenticated)
			{
				// If the challenge was missing or it was wrong, don't let him go any further
				$this->data = 'Invalid login credentials';
				$this->status = self::STATUS_NOT_AUTH;
				$this->encapsulation = self::ENCAPSULATION_RAW;
				return $this->getResponse();
			}
		}

		// Replicate the encapsulation preferences of the client for our own output
		$this->encapsulation = $request->encapsulation;

		// Store the client-specified key, or use the server key if none specified and the request
		// came encrypted.
		$this->password = isset($request->body->key) ? $request->body->key : null;
		$hasKey = property_exists($request->body, 'key') ? !is_null($request->body->key) : false;
		if(!$hasKey && ($request->encapsulation != self::ENCAPSULATION_RAW) )
		{
			$this->password = $this->serverKey();
		}

		// Does the specified method exist?
		$method_exists = false;
		$method_name = '';
		if(isset($request->body->method))
		{
			$method_name = ucfirst($request->body->method);
			$this->method_name = $method_name;
			$method_exists = method_exists($this, '_api'.$method_name );
		}
		if(!$method_exists)
		{
			// The requested method doesn't exist. Oops!
			$this->data = "Invalid method $method_name";
			$this->status = self::STATUS_INVALID_METHOD;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return $this->getResponse();
		}

		// Run the method
		$params = array();
		if(isset($request->body->data)) $params = (array)$request->body->data;
		$this->data = call_user_func( array($this, '_api'.$method_name) , $params);

		return $this->getResponse();
	}

	/**
	 * Packages the response to a JSON-encoded object, optionally encrypting the
	 * data part with a caller-supplied password.
	 * @return string The JSON-encoded response
	 */
	private function getResponse()
	{
		// Initialize the response
		$response = array(
			'encapsulation'	=> $this->encapsulation,
			'body'		=> array(
				'status'		=> $this->status,
				'data'			=> null
			)
		);

		switch($this->method_name)
		{
			case 'Download':
				$data = json_encode($this->data);
				break;
			default:
				$data = $this->json_encode($this->data);
				break;
		}

		if(empty($this->password)) $this->encapsulation = self::ENCAPSULATION_RAW;

		switch($this->encapsulation)
		{
			case self::ENCAPSULATION_RAW:
				break;

			case self::ENCAPSULATION_AESCTR128:
				$data = AEUtilEncrypt::AESEncryptCtr($data, $this->password, 128);
				break;

			case self::ENCAPSULATION_AESCTR256:
				$data = AEUtilEncrypt::AESEncryptCtr($data, $this->password, 256);
				break;

			case self::ENCAPSULATION_AESCBC128:
				$data = base64_encode(AEUtilEncrypt::AESEncryptCBC($data, $this->password, 128));
				break;

			case self::ENCAPSULATION_AESCBC256:
				$data = base64_encode(AEUtilEncrypt::AESEncryptCBC($data, $this->password, 256));
				break;
		}

		$response['body']['data'] = $data;
		
		switch($this->method_name)
		{
			case 'Download':
				return '###' . json_encode($response) . '###';
				break;
			default:
				return '###' . $this->json_encode($response) . '###';
				break;
		}
	}

	private function serverKey()
	{
		static $key = null;

		if(is_null($key))
		{
			$key = $this->_getOption('frontend_secret_word', '');
		}

		return $key;
	}

	private function _apiGetVersion()
	{
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/liveupdate.php';
		$updateInformation = LiveUpdate::getUpdateInformation();
		
		return (object)array(
			'api'			=> ADMINTOOLS_JSON_API_VERSION,
			'component'		=> ADMINTOOLS_VERSION,
			'date'			=> ADMINTOOLS_DATE,
			'updateinfo'	=> $updateInformation
		);
	}
	
	private function _apiUpdateGetInformation($config)
	{
		$defConfig = array(
			'force'			=> 0
		);
		$config = array_merge($defConfig, $config);
		extract($config);
		
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/liveupdate.php';
		
		$updateInformation = LiveUpdate::getUpdateInformation($force);
		
		return (object)$updateInformation;
	}
	
	private function _apiUpdateDownload($config)
	{
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/liveupdate.php';
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/classes/model.php';
		
		// Do we need to update?
		$updateInformation = LiveUpdate::getUpdateInformation();
		if(!$updateInformation->hasUpdates) {
			return (object)array(
				'download'	=> 0
			);
		}
		
		$model = new LiveupdateModel();
		$ret = $model->download();
		
		$session = JFactory::getSession();
		$target		= $session->get('target', '', 'liveupdate');
		$tempdir	= $session->get('tempdir', '', 'liveupdate');
		
		// Save the target and tempdir
		$session =& JFactory::getSession();
		$session->set('profile', 1, 'akeeba');
		AEPlatform::load_configuration(1);
		$config = AEFactory::getConfiguration();
		$config->set('remoteupdate.target', $target);
		$config->set('remoteupdate.tempdir', $tempdir);
		AEPlatform::save_configuration(1);
		
		if(!$ret) {
			// An error ocurred :(
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return "Could not download the update package";
		} else {
			return (object)array(
				'download'	=> 1
			);
		}
	}
	
	private function _apiUpdateExtract($config)
	{
		$session =& JFactory::getSession();
		$session->set('profile', 1, 'akeeba');
		AEPlatform::load_configuration(1);
		$config = AEFactory::getConfiguration();
		$target = $config->get('remoteupdate.target', '');
		$tempdir = $config->get('remoteupdate.tempdir', '');
		
		$session = JFactory::getSession();
		$session->set('target', $target, 'liveupdate');
		$session->set('tempdir', $tempdir, 'liveupdate');
		
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/liveupdate.php';
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/classes/model.php';
		
		$model = new LiveupdateModel();
		$ret = $model->extract();
		
		jimport('joomla.filesystem.file');
		JFile::delete($target);
		
		if(!$ret) {
			// An error ocurred :(
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return "Could not extract the update package";
		} else {
			return (object)array(
				'extract'	=> 1
			);
		}
	}
	
	private function _apiUpdateInstall($config) {
		$session =& JFactory::getSession();
		$session->set('profile', 1, 'akeeba');
		AEPlatform::load_configuration(1);
		$config = AEFactory::getConfiguration();
		$target = $config->get('remoteupdate.target', '');
		$tempdir = $config->get('remoteupdate.tempdir', '');
		
		$session = JFactory::getSession();
		$session->set('tempdir', $tempdir, 'liveupdate');
		
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/liveupdate.php';
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/classes/model.php';
		
		$model = new LiveupdateModel();
		$ret = $model->install();
		
		if(!$ret) {
			// An error ocurred :(
			$this->status = self::STATUS_ERROR;
			$this->encapsulation = self::ENCAPSULATION_RAW;
			return "Could not install the update package";
		} else {
			return (object)array(
				'install'	=> 1
			);
		}
	}
	
	private function _apiUpdateCleanup($config) {
		$session =& JFactory::getSession();
		$session->set('profile', 1, 'akeeba');
		AEPlatform::load_configuration(1);
		$config = AEFactory::getConfiguration();
		$target = $config->get('remoteupdate.target', '');
		$tempdir = $config->get('remoteupdate.tempdir', '');
		
		$session = JFactory::getSession();
		$session->set('target', $target, 'liveupdate');
		$session->set('tempdir', $tempdir, 'liveupdate');
		
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/liveupdate.php';
		require_once JPATH_ROOT.'/administrator/components/com_admintools/liveupdate/classes/model.php';
		
		$model = new LiveupdateModel();
		$ret = $model->cleanup();
		
		jimport('joomla.filesystem.file');
		JFile::delete($target);

		$config->set('remoteupdate.target', null);
		$config->set('remoteupdate.tempdir', null);
		AEPlatform::save_configuration(1);

		return (object)array(
			'cleanup'	=> 1
		);
	}
	
	private function _apiGetJoomlaVersion()
	{
		JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_admintools'.DS.'models');
		$model = JModel::getInstance('Jupdate','AdmintoolsModel');
		$updateInformation = $model->getUpdateInfo();
		
		// Keys: status; version; current; update; full
		return (object)$updateInformation;
	}
	
	/**
	 * Encodes a variable to JSON using PEAR's Services_JSON
	 * @param mixed $value The value to encode
	 * @param int $options Encoding preferences flags
	 * @return string The JSON-encoded string
	 */
	private function json_encode($value, $options = 0) {
		$flags = SERVICES_JSON_LOOSE_TYPE;
		if( $options & JSON_FORCE_OBJECT ) $flags = 0;
		$encoder = new Akeeba_Services_JSON($flags);
		return $encoder->encode($value);
	}
	
	/**
	 * Decodes a JSON string to a variable using PEAR's Services_JSON
	 * @param string $value The JSON-encoded string
	 * @param bool $assoc True to return an associative array instead of an object
	 * @return mixed The decoded variable
	 */
	private function json_decode($value, $assoc = false)
	{
		$flags = 0;
		if($assoc) $flags = SERVICES_JSON_LOOSE_TYPE;
		$decoder = new Akeeba_Services_JSON($flags);
		return $decoder->decode($value);
	}
	
	/**
	 * A PHP based INI file parser.
	 * Thanks to asohn ~at~ aircanopy ~dot~ net for posting this handy function on
	 * the parse_ini_file page on http://gr.php.net/parse_ini_file
	 * @param	string	$file	Filename to process
	 * @param	bool	$process_sections	True to also process INI sections
	 * @param	bool	$rawdata	If true, the $file contains raw INI data, not a filename
	 * @return	array	An associative array of sections, keys and values
	 */
	static function parse_ini_file_php($file, $process_sections = false, $rawdata = false)
	{
		$process_sections = ($process_sections !== true) ? false : true;

		if(!$rawdata)
		{
			$ini = file($file);
		}
		else
		{
			$file = str_replace("\r","",$file);
			$ini = explode("\n", $file);
		}

		if (count($ini) == 0) {return array();}

		$sections = array();
		$values = array();
		$result = array();
		$globals = array();
		$i = 0;
		foreach ($ini as $line) {
			$line = trim($line);
			$line = str_replace("\t", " ", $line);

			// Comments
			if (!preg_match('/^[a-zA-Z0-9[]/', $line)) {continue;}

			// Sections
			if ($line{0} == '[') {
				$tmp = explode(']', $line);
				$sections[] = trim(substr($tmp[0], 1));
				$i++;
				continue;
			}

			// Key-value pair
			list($key, $value) = explode('=', $line, 2);
			$key = trim($key);
			$value = trim($value);
			if (strstr($value, ";")) {
				$tmp = explode(';', $value);
				if (count($tmp) == 2) {
					if ((($value{0} != '"') && ($value{0} != "'")) ||
					preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
					preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value) ){
						$value = $tmp[0];
					}
				} else {
					if ($value{0} == '"') {
						$value = preg_replace('/^"(.*)".*/', '$1', $value);
					} elseif ($value{0} == "'") {
						$value = preg_replace("/^'(.*)'.*/", '$1', $value);
					} else {
						$value = $tmp[0];
					}
				}
			}
			$value = trim($value);
			$value = trim($value, "'\"");

			if ($i == 0) {
				if (substr($line, -1, 2) == '[]') {
					$globals[$key][] = $value;
				} else {
					$globals[$key] = $value;
				}
			} else {
				if (substr($line, -1, 2) == '[]') {
					$values[$i-1][$key][] = $value;
				} else {
					$values[$i-1][$key] = $value;
				}
			}
		}

		for($j = 0; $j < $i; $j++) {
			if ($process_sections === true) {
				if( isset($sections[$j]) && isset($values[$j]) )	$result[$sections[$j]] = $values[$j];
			} else {
				if( isset($values[$j]) ) $result[] = $values[$j];
			}
		}

		return $result + $globals;
	}
}