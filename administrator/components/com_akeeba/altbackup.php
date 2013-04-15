<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 3.0
 */

// Make sure we're being called from the command line, not a web interface
if( array_key_exists('REQUEST_METHOD', $_SERVER) )
{
	die('You are not supposed to access this script from the web. You have to run it from the command line. If you don\'t understand what this means, you must not try to use this file before reading the documentation. Thank you.');
}

// Basic Akeeba Engine and utility defines
define('AKEEBAENGINE', 1); // Enable Akeeba Engine
define('AKEEBAPLATFORM', 'joomla15'); // Joomla! 1.5 platform
define('AKEEBACLI', 1); // Force CLI mode
define('_JEXEC', 1 ); // Allow inclusion of Joomla! files
define('DS',DIRECTORY_SEPARATOR); // Still required by Joomla! :(

// Our script allows being placed inside administrator/components/com_akeeba or
// administrator/components itself. Let's compensate for the different paths.
$currentDirectory = dirname(__FILE__);
if(substr($currentDirectory, -10) != 'com_akeeba') $currentDirectory .= '/com_akeeba';
// Define JPATH_BASE to point to site's administrator directory
define('JPATH_COMPONENT_ADMINISTRATOR', $currentDirectory);
$parts = explode( DIRECTORY_SEPARATOR, JPATH_COMPONENT_ADMINISTRATOR );
array_pop( $parts ); array_pop( $parts );
define('JPATH_BASE', implode( DS, $parts ) );
array_pop( $parts );
define('JPATH_SITE', implode( DS, $parts ) );
// Use Joomla!'s defines.php to set the rest of required defines
define( 'JPATH_ROOT',			JPATH_SITE );
define( 'JPATH_CONFIGURATION', 	JPATH_ROOT );
define( 'JPATH_ADMINISTRATOR', 	JPATH_ROOT.'/administrator' );
define( 'JPATH_XMLRPC', 		JPATH_ROOT.'/xmlrpc' );
define( 'JPATH_LIBRARIES',	 	JPATH_ROOT.'/libraries' );
define( 'JPATH_PLATFORM',	 	JPATH_ROOT.'/libraries' );
define( 'JPATH_PLUGINS',		JPATH_ROOT.'/plugins'   );
define( 'JPATH_INSTALLATION',	JPATH_ROOT.'/installation' );
define( 'JPATH_THEMES'	   ,	JPATH_BASE.'/templates' );
define( 'JPATH_CACHE',			JPATH_BASE.'/cache');

// Load the version defines
include_once JPATH_COMPONENT_ADMINISTRATOR.'/version.php';

/**
 * Parses POSIX command line options and returns them as an associative array. Each array item contains
 * a single dimensional array of values. Arguments without a dash are silently ignored.
 * @return array
 */
function parseOptions()
{
	global $argc, $argv;

	// Workaround for PHP-CGI
	if(!isset($argc) && !isset($argv))
	{
		$query = "";
		if(!empty($_GET)) foreach($_GET as $k => $v) {
			$query .= " $k";
			if($v != "") {
				$query .= "=$v";
			}
		}
		$query = ltrim($query);
		$argv = explode(' ',$query);
		$argc = count($argv);
	}
	
	$len			= sizeof($argv);
	$currentName	= "";
	$options		= array();

	for ($i = 1; $i < $argc; $i++) {
		$argument = $argv[$i];
		if(strpos($argument,"-")===0)
		{
			$argument = ltrim($argument, '-');
			if( strstr($argument, '=') )
			{
				list($name, $value) = explode( '=', $argument, 2);
			}
			else
			{
				$name = $argument;
				$value = null;
			}
			$currentName=$name;
			if($options[$currentName]==NULL)
			{
				$options[$currentName]=array();
			}
		}
		else
		{
			$value = $argument;
		}
		if( (!is_null($value)) && (!is_null($currentName)) )
		{
			$values=$options[$currentName];
			array_push($values,$value);
			$options[$currentName]=$values;
		}
	}
	return $options;
}

/**
 * Returns the value of a command line option
 * @param string $key The full name of the option, e.g. "foobar"
 * @param mixed $default The default value to return
 * @param bool $first_item_only Return only the first value specified (default = true)
 * @return mixed
 */
function getOption($key, $default = null, $first_item_only = true)
{
	static $options = null;
	if( is_null($options) )
	{
		$options = parseOptions();
	}

	if( !array_key_exists($key, $options) )
	{
		return $default;
	}
	else
	{
		if( (count($options[$key]) == 1) || $first_item_only )
		{
			return $options[$key][0];
		}
		else
		{
			return $options[$key];
		}
	}
}

function fetchURL($url, $method)
{
	switch($method)
	{
		case 'curl':
			$ch = curl_init($url);
			@curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).'/akeeba/assets/cacert.pem');
			@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			@curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
			@curl_setopt($ch, CURLOPT_HEADER, false);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 180);
			@curl_setopt($ch, CURLOPT_TIMEOUT, 180);
			$result = curl_exec($ch);
			curl_close($ch);
			return $result;
			break;

		case 'fsockopen':
			$pos = strpos($url, '://');
			$protocol = strtolower(substr($url, 0, $pos));
			$req = substr($url, $pos+3);
	        $pos = strpos($req, '/');
	        if($pos === false)
	            $pos = strlen($req);
	        $host = substr($req, 0, $pos);

	        if(strpos($host, ':') !== false)
	        {
	            list($host, $port) = explode(':', $host);
	        }
	        else
	        {
	            $host = $host;
	            $port = ($protocol == 'https') ? 443 : 80;
	        }

	        $uri = substr($req, $pos);
	        if($uri == '')
	            $uri = '/';

			$crlf = "\r\n";
			$req = 'GET ' . $uri . ' HTTP/1.0' . $crlf
            .    'Host: ' . $host . $crlf
            .    $crlf;

	        $fp = fsockopen(($protocol == 'https' ? 'ssl://' : '') . $host, $port);
	        fwrite($fp, $req);
	        $response = '';
	        while(is_resource($fp) && $fp && !feof($fp))
	            $response .= fread($fp, 1024);
	        fclose($fp);

			// split header and body
	        $pos = strpos($response, $crlf . $crlf);
	        if($pos === false)
	            return($response);
	        $header = substr($response, 0, $pos);
	        $body = substr($response, $pos + 2 * strlen($crlf));

	        // parse headers
	        $headers = array();
	        $lines = explode($crlf, $header);
	        foreach($lines as $line)
	            if(($pos = strpos($line, ':')) !== false)
	                $headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1));

			//redirection?
	        if(isset($headers['location']))
	        {
	        	return fetchURL($headers['location'], $method);
	        }
	        else
	        {
	            return($body);
	        }

			break;

		case 'fopen':
			$opts = array(
			  'http'=>array(
			    'method'=>"GET",
			    'header'=>"Accept-language: en\r\n"
			  )
			);

			$context = stream_context_create($opts);
			$result = @file_get_contents($url, false, $context);
			break;
	}

	return $result;
}

// Initialize the options
$profile = intval( getOption('profile', 1) );

$version = AKEEBA_VERSION;
$date = AKEEBA_DATE;
echo <<<ENDBLOCK
Akeeba Backup Alternate CRON Helper Script version $version ($date)
Copyright (C) 2010-2011 Nicholas K. Dionysopoulos
-------------------------------------------------------------------------------
Akeeba Backup is Free Software, distributed under the terms of the GNU General
Public License version 3 or, at your option, any later version.
This program comes with ABSOLUTELY NO WARRANTY as per sections 15 & 16 of the
license. See http://www.gnu.org/licenses/gpl-3.0.html for details.
-------------------------------------------------------------------------------


ENDBLOCK;

// Attempt to use an infinite time limit, in case you are using the PHP CGI binary instead
// of the PHP CLI binary. This will not work with Safe Mode, though.
$safe_mode = true;
if(function_exists('ini_get')) {
	$safe_mode = ini_get('safe_mode');
}
if(!$safe_mode && function_exists('set_time_limit')) {
	echo "Unsetting time limit restrictions.\n";
	@set_time_limit(0);
} elseif (!$safe_mode) {
	echo "Could not unset time limit restrictions; you may get a timeout error\n";
} else {
	echo "You are using PHP's Safe Mode; you may get a timeout error\n";	
}
echo "\n";

// Log some paths
echo "Site paths determined by this script:\n";
echo "JPATH_BASE : ".JPATH_BASE."\n";
echo "JPATH_COMPONENT_ADMINISTRATOR : ".JPATH_COMPONENT_ADMINISTRATOR."\n\n";

// Load the engine
$factoryPath = JPATH_COMPONENT_ADMINISTRATOR.'/akeeba/factory.php';
if(!file_exists($factoryPath)) {
	echo "ERROR!\n";
	echo "Could not load the backup engine; file does not exist. Technical information:\n";
	echo "Path to backup.php: ".dirname(__FILE__)."\n";
	echo "Path to factory file: $factoryPath\n";
} else {
	require_once $factoryPath;
}

$startup_check = true;

// Get the live site's URL
$url = AEPlatform::getInstance()->get_platform_configuration_option('siteurl','');
if( empty($url) )
{
	echo <<<ENDTEXT
ERROR:
       This script could not detect your live site's URL. Please visit Akeeba
       Backup's Control Panel page at least once before running this script, so
       that this information can be stored for use by this script.

ENDTEXT;
	$startup_check = false;
}

// Get the front-end backup settings
$frontend_enabled = AEPlatform::getInstance()->get_platform_configuration_option('frontend_enable','');
$secret = AEPlatform::getInstance()->get_platform_configuration_option('frontend_secret_word','');

if(!$frontend_enabled)
{
	echo <<<ENDTEXT
ERROR:
       Your Akeeba Backup installation's front-end backup feature is currently
       disabled. Please log in to your site's back-end as a Super Administra-
       tor, go to Akeeba Backup's Control Panel, click on the Parameters icon
       in the top right corner and enable the front-end backup feature. Do not
       forget to also set a Secret Word!

ENDTEXT;
	$startup_check = false;
}
elseif(empty($secret))
{
	echo <<<ENDTEXT
ERROR:
       You have enabled the front-end backup feature, but you forgot to set a
       secret word. Without a valid secret word this script can not continue.
       Please log in to your site's back-end as a Super Administrator, go to
       Akeeba Backup's Control Panel, click on the Parameters icon in the top
       right corner set a Secret Word.

ENDTEXT;
	$startup_check = false;

}

// Detect cURL or fopen URL
$method = null;
if(function_exists('curl_init'))
{
	$method = 'curl';
}  elseif( function_exists('fsockopen') ) {
	$method = 'fsockopen';
}

if(empty($method))
{
	if(function_exists('ini_get'))
	{
		if( ini_get('allow_url_fopen') )
		{
			$method = 'fopen';
		}
	}
}

$overridemethod = getOption('method','');
if( !empty($overridemethod) )
{
	$method = $overridemethod;
}

if(empty($method))
{
	echo <<<ENDTEXT
ERROR:
       Could not find any supported method for running the front-end backup
       feature of Akeeba Backup. Please check with your host that at least
       one of the following features are supported in your PHP configuration:
       1. The cURL extension
       2. The fsockopen() function
       3. The fopen() URL wrappers, i.e. allow_url_fopen is enabled
       If neither method is available you will not be able to backup your
       site using this CRON helper script.

ENDTEXT;
	$startup_check = false;
}


if(!$startup_check)
{
	echo "\n\nBACKUP ABORTED DUE TO CONFIGURATION ERRORS\n\n";
	die();
}

echo <<<ENDBLOCK
Starting a new backup with the following parameters:
Profile ID    : $profile
Backup Method : $method


ENDBLOCK;

// Perform the backup
$url = rtrim($url, '/');
$secret = urlencode($secret);
$url .= "/index.php?option=com_akeeba&view=backup&key={$secret}&noredirect=1&profile=$profile";

$inLoop = true;
$step = 0;
$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Beginning backing up\n";

while($inLoop)
{
	$result = fetchURL($url, $method);
	if(empty($result) || ($result === false))
	{
$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] No message received\n";
		echo <<<ENDTEXT
ERROR:
       Your backup attempt has timed out, or a fatal PHP error has occurred.
       Please check the backup log and your server's error log for more
       information.

Backup failed.

ENDTEXT;
		$inLoop = false;
	}
	elseif( strpos('301 More work required', $result) !== false )
	{
		if($step == 0) $old_url = $url;
		$step++;
		$url = $old_url.'&task=step&step='.$step;
		$timestamp = date('Y-m-d H:i:s');
		echo "[{$timestamp}] Backup progress signal received\n";
	}
	elseif( strpos('200 OK', $result) !== false )
	{
$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Backup finalization message received\n";
		echo <<<ENDTEXT

Your backup has finished successfully.

Please review your backup log file for any warning messages. If you see any
such messages, please make sure that your backup is working properly by trying
to restore it on a local server.

ENDTEXT;
		$inLoop = false;
	}
	elseif( strpos('500 ERROR -- ', $result) !== false )
	{
		// Backup error
$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Error signal received\n";
		echo <<<ENDTEXT
ERROR:
       A backup error has occured. The server's reponse was:

$result

Backup failed.

ENDTEXT;
		$inLoop = false;
	}
	elseif( strpos('403 ', $result) !== false )
	{
		// This should never happen: invalid authentication or front-end backup disabled
$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Connection denied (403) message received\n";
		echo <<<ENDTEXT
ERROR:
       The server denied the connection. Please make sure that the front-end
       backup feature is enabled and a valid secret word is in place.

       Server response: $result

Backup failed.

ENDTEXT;
		$inLoop = false;
	}

}