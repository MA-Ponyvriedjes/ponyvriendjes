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

$exitCode = 0;

// Basic Akeeba Engine and utility defines
define('AKEEBAENGINE', 1); // Enable Akeeba Engine
define('AKEEBAPLATFORM', 'joomla15'); // Joomla! 1.5 platform
define('AKEEBACLI', 1); // Force CLI mode
define('_JEXEC', 1 ); // Allow inclusion of Joomla! files

// Our script allows being placed inside administrator/components/com_akeeba or
// administrator/components itself. Let's compensate for the different paths.
$currentDirectory = dirname(__FILE__);
if(substr($currentDirectory, -10) != 'com_akeeba') $currentDirectory .= '/com_akeeba';
// Define JPATH_BASE to point to site's administrator directory
define('JPATH_COMPONENT_ADMINISTRATOR', $currentDirectory);
$parts = explode( DIRECTORY_SEPARATOR, JPATH_COMPONENT_ADMINISTRATOR );
array_pop( $parts ); array_pop( $parts );
define('JPATH_BASE', implode( DIRECTORY_SEPARATOR, $parts ) );
array_pop( $parts );
define('JPATH_SITE', implode( DIRECTORY_SEPARATOR, $parts ) );
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
if(file_exists(JPATH_COMPONENT_ADMINISTRATOR.'/version.php'))
	include_once JPATH_COMPONENT_ADMINISTRATOR.'/version.php';

/**
 * Returns a fancy formatted ttime lapse code
 * @param $referencedate
 * @param $timepointer
 * @param $measureby
 * @param $autotext
 * @return unknown_type
 */
function timeago($referencedate=0, $timepointer='', $measureby='', $autotext=true){    ## Measureby can be: s, m, h, d, or y
    if($timepointer == '') $timepointer = time();
    $Raw = $timepointer-$referencedate;    ## Raw time difference
    $Clean = abs($Raw);
    $calcNum = array(array('s', 60), array('m', 60*60), array('h', 60*60*60), array('d', 60*60*60*24), array('y', 60*60*60*24*365));    ## Used for calculating
    $calc = array('s' => array(1, 'second'), 'm' => array(60, 'minute'), 'h' => array(60*60, 'hour'), 'd' => array(60*60*24, 'day'), 'y' => array(60*60*24*365, 'year'));    ## Used for units and determining actual differences per unit (there probably is a more efficient way to do this)

    if($measureby == ''){    ## Only use if nothing is referenced in the function parameters
        $usemeasure = 's';    ## Default unit

        for($i=0; $i<count($calcNum); $i++){    ## Loop through calcNum until we find a low enough unit
            if($Clean <= $calcNum[$i][1]){        ## Checks to see if the Raw is less than the unit, uses calcNum b/c system is based on seconds being 60
                $usemeasure = $calcNum[$i][0];    ## The if statement okayed the proposed unit, we will use this friendly key to output the time left
                $i = count($calcNum);            ## Skip all other units by maxing out the current loop position
            }
        }
    }else{
        $usemeasure = $measureby;                ## Used if a unit is provided
    }

    $datedifference = floor($Clean/$calc[$usemeasure][0]);    ## Rounded date difference

    if($autotext==true && ($timepointer==time())){
        if($Raw < 0){
            $prospect = ' from now';
        }else{
            $prospect = ' ago';
        }
    }
    else
    {
    	$prospect = '';
    }

    if($referencedate != 0){        ## Check to make sure a date in the past was supplied
        if($datedifference == 1){    ## Checks for grammar (plural/singular)
            return $datedifference . ' ' . $calc[$usemeasure][1] . ' ' . $prospect;
        }else{
            return $datedifference . ' ' . $calc[$usemeasure][1] . 's ' . $prospect;
        }
    }else{
        return 'No input time referenced.';
    }
}

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
			if( !isset($options[$currentName]) || ($options[$currentName]==NULL) )
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
			if(strstr($value,'='))
			{
				$parts = explode('=',$value,2);
				$key = $parts[0];
				$value = $parts[1];
			}
			else
			{
				$key = null;
			}

			$values=$options[$currentName];
			if(is_null($key)) {
				array_push($values,$value);
			} else {
				$values[$key] = $value;
			}
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
		if( $first_item_only )
		{
			return $options[$key][0];
		}
		else
		{
			return $options[$key];
		}
	}
}

function hasOption($key)
{
	static $options = null;
	if( is_null($options) )
	{
		$options = parseOptions();
	}

	return array_key_exists($key, $options);
}

function memUsage()
{
	if(function_exists('memory_get_usage')) {
		$size = memory_get_usage();
		$unit=array('b','Kb','Mb','Gb','Tb','Pb');
    	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	} else {
		return "(unknown)";
	}
}

function peakMemUsage()
{
	if(function_exists('memory_get_peak_usage')) {
		$size = memory_get_peak_usage();
		$unit=array('b','Kb','Mb','Gb','Tb','Pb');
    	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	} else {
		return "(unknown)";
	}
}

// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
if(function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set')) {
	if(function_exists('error_reporting')) {
		$oldLevel = error_reporting(0);
	}
	$serverTimezone = @date_default_timezone_get();
	if(empty($serverTimezone) || !is_string($serverTimezone)) $serverTimezone = 'UTC';
	if(function_exists('error_reporting')) {
		error_reporting($oldLevel);
	}
	@date_default_timezone_set( $serverTimezone);
}

// Initialize the options
$profile = getOption('profile', 1);
$description = getOption('description', 'Command-line backup');
$overrides = getOption('override', array(), false);

if(!empty($overrides))
{
	$override_message = "\nConfiguration variables overriden in the command line:\n";
	$override_message .= implode(', ', array_keys($overrides) );
	$override_message .= "\n";
}
else
{
	$override_message = "";
}

$version = AKEEBA_VERSION;
$date = AKEEBA_DATE;
$start_backup = time();
$memusage = memUsage();
if(!hasOption('quiet')) echo <<<ENDBLOCK
Akeeba Backup CLI $version ($date)
Copyright (C) 2010-2011 Nicholas K. Dionysopoulos
-------------------------------------------------------------------------------
Akeeba Backup is Free Software, distributed under the terms of the GNU General
Public License version 3 or, at your option, any later version.
This program comes with ABSOLUTELY NO WARRANTY as per sections 15 & 16 of the
license. See http://www.gnu.org/licenses/gpl-3.0.html for details.
-------------------------------------------------------------------------------

Starting a new backup with the following parameters:
Profile ID  $profile
Description "$description"
$override_message
Current memory usage: $memusage


ENDBLOCK;

// Attempt to use an infinite time limit, in case you are using the PHP CGI binary instead
// of the PHP CLI binary. This will not work with Safe Mode, though.
$safe_mode = true;
if(function_exists('ini_get')) {
	$safe_mode = ini_get('safe_mode');
}
if(!$safe_mode && function_exists('set_time_limit')) {
	if(!hasOption('quiet')) echo "Unsetting time limit restrictions.\n";
	@set_time_limit(0);
} elseif (!$safe_mode) {
	if(!hasOption('quiet')) echo "Could not unset time limit restrictions; you may get a timeout error\n";
} else {
	if(!hasOption('quiet')) echo "You are using PHP's Safe Mode; you may get a timeout error\n";	
}
if(!hasOption('quiet')) echo "\n";

// Log some paths
if(!hasOption('quiet')) {
	echo "Site paths determined by this script:\n";
	echo "JPATH_BASE : ".JPATH_BASE."\n";
	echo "JPATH_COMPONENT_ADMINISTRATOR : ".JPATH_COMPONENT_ADMINISTRATOR."\n\n";
}

// Load the engine
$factoryPath = JPATH_COMPONENT_ADMINISTRATOR.'/akeeba/factory.php';
if(!file_exists($factoryPath)) {
	echo "ERROR!\n";
	echo "Could not load the backup engine; file does not exist. Technical information:\n";
	echo "Path to backup.php: ".dirname(__FILE__)."\n";
	echo "Path to factory file: $factoryPath\n";
	die("\n");
} else {
	require_once $factoryPath;
}


// Forced CLI mode settings
define('AKEEBA_PROFILE', $profile);
define('AKEEBA_BACKUP_ORIGIN', 'cli');

// Force loading CLI-mode translation class
$dummy = new AEUtilTranslate;

// Load the profile
AEPlatform::getInstance()->load_configuration($profile);

// Reset Kettenrad and its storage
AECoreKettenrad::reset();
AEUtilTempvars::reset(AKEEBA_BACKUP_ORIGIN);

// Setup
$kettenrad =& AEFactory::getKettenrad();
$options = array(
	'description'	=> $description,
	'comment'		=> ''
);
if(!empty($overrides)) {
	AEPlatform::getInstance()->configOverrides = $overrides;
}
$kettenrad->setup($options);

// Dummy array so that the loop iterates once
$array = array(
	'HasRun'	=> 0,
	'Error'		=> ''
);

$warnings_flag = false;

while( ($array['HasRun'] != 1) && (empty($array['Error'])) )
{
	AEUtilLogger::openLog(AKEEBA_BACKUP_ORIGIN);
	AEUtilLogger::WriteLog(true,'');
	// Apply overrides in the command line
	if(!empty($overrides))
	{
		$config = AEFactory::getConfiguration();
		foreach($overrides as $key => $value)
		{
			$config->set($key, $value);
		}
	}
	// Apply engine optimization overrides
	$config = AEFactory::getConfiguration();
	$config->set('akeeba.tuning.min_exec_time',0);
	$config->set('akeeba.tuning.nobreak.beforelargefile',1);
	$config->set('akeeba.tuning.nobreak.afterlargefile',1);
	$config->set('akeeba.tuning.nobreak.proactive',1);
	$config->set('akeeba.tuning.nobreak.finalization',1);
	$config->set('akeeba.tuning.settimelimit',0);
	$config->set('akeeba.tuning.nobreak.domains',0);

	$kettenrad->tick();
	AEFactory::getTimer()->resetTime();
	$array = $kettenrad->getStatusArray();
	AEUtilLogger::closeLog();
	$time = date('Y-m-d H:i:s \G\M\TO (T)');
	$memusage = memUsage();
	
	$warnings = "no warnings issued (good)";
	$stepWarnings = false;
	if(!empty($array['Warnings'])) {
		$warnings_flag = true;
		$warnings = "POTENTIAL PROBLEMS DETECTED; ". count($array['Warnings'])." warnings issued (see below).\n";
		foreach($array['Warnings'] as $line) {
			$warnings .= "\t$line\n";
		}
		$stepWarnings = true;
		$kettenrad->resetWarnings();
	}
	
if(!hasOption('quiet') || $stepWarnings) echo <<<ENDSTEPINFO
Last Tick   : $time
Domain      : {$array['Domain']}
Step        : {$array['Step']}
Substep     : {$array['Substep']}
Memory used : $memusage
Warnings    : $warnings


ENDSTEPINFO;
}

// Clean up
AEUtilTempvars::reset(AKEEBA_BACKUP_ORIGIN);

if(!empty($array['Error']))
{
	echo "An error has occurred:\n{$array['Error']}\n\n";
	$exitCode = 2;
}
else
{
	if(!hasOption('quiet')) echo "Backup job finished successfully after approximately ".timeago($start_backup, time(), '', false)."\n";
}

if($warnings_flag && !hasOption('quiet')) {
	echo "\n".str_repeat('=',79)."\n";
	echo "!!!!!  W A R N I N G  !!!!!\n\n";
	echo "Akeeba Backup issued warnings during the backup process. You have to review them\n";
	echo "and make sure that your backup has completed successfully. Always test a backup with\n";
	echo "warnings to make sure that it is working properly, by restoring it to a local server.\n";
	echo "DO NOT IGNORE THIS MESSAGE! AN UNTESTED BACKUP IS AS GOOD AS NO BACKUP AT ALL.\n";
	echo "\n".str_repeat('=',79)."\n";
} elseif($warnings_flag) {
	$exitCode = 1;
}

if(!hasOption('quiet')) echo "Peak memory usage: ".peakMemUsage()."\n\n";

exit($exitCode);