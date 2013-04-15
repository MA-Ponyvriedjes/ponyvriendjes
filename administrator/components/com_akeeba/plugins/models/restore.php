<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 3.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');
if(!class_exists('AkeebaHelperEscape')) JLoader::import('helpers.escape', JPATH_COMPONENT_ADMINISTRATOR);

/**
 * Integrated restoration Model
 *
 */
class AkeebaModelRestore extends JModel
{
	private $data;
	private $extension;
	private $path;

	public $password;
	public $id;

	/**
	 * Generates a pseudo-random password
	 * @param int $length The length of the password in characters
	 * @return string The requested password string
	 */
	function makeRandomPassword( $length = 32 )
	{
		$chars = "abcdefghijkmnopqrstuvwxyz023456789!@#$%&*";
		srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;

		while ($i <= $length) {
			$num = rand() % 40;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}

		return $pass;
	}

	function getId()
	{
		$id = null;
		$cid = JRequest::getVar('cid', array(), 'default', 'array');
		if(!empty($cid))
		{
			$id = intval($cid[0]);
			if($id <= 0) $id = null;
		}
		if(empty($id)) $id = JRequest::getInt('id', -1);
		if($id <= 0) $id = null;

		if( empty($id) )
		{
			return null;
		}

		return $id;
	}

	/**
	 * Validates the data passed to the request.
	 * @return mixed True if all is OK, an error string if something is wrong
	 */
	function validateRequest()
	{
		// Is this a valid backup entry?
		$id = $this->getId();
		if(empty($id))
		{
			return JText::_('RESTORE_ERROR_INVALID_RECORD');
		}

		$data = AEPlatform::getInstance()->get_statistics($id);
		if(empty($data))
		{
			return JText::_('RESTORE_ERROR_INVALID_RECORD');
		}

		if($data['status'] != 'complete')
		{
			return JText::_('RESTORE_ERROR_INVALID_RECORD');
		}

		// Load the profile ID (so that we can find out the output directory)
		$profile_id = $data['profile_id'];
		AEPlatform::getInstance()->load_configuration($profile_id);

		$path = $data['absolute_path'];
		$exists = @file_exists($path);
		if(!$exists)
		{
			// Let's try figuring out an alternative path
			$config =& AEFactory::getConfiguration();
			$path = $config->get('akeeba.basic.output_directory', '').'/'.$data['archivename'];
			$exists = @file_exists($path);
		}

		if(!$exists)
		{
			return JText::_('RESTORE_ERROR_ARCHIVE_MISSING');
		}

		$filename = basename($path);
		$lastdot = strrpos($filename, '.');
		$extension = strtoupper( substr($filename, $lastdot+1) );
		if( !in_array($extension, array('JPA','ZIP')) )
		{
			return JText::_('RESTORE_ERROR_INVALID_TYPE');
		}

		$this->data =& $data;
		$this->path = $path;
		$this->extension = $extension;

		return true;
	}

	function createRestorationINI()
	{
		// Get a password
		$this->password = $this->makeRandomPassword(32);
		JRequest::setVar('password', $this->password);

		// Do we have to use FTP?
		$procengine = JRequest::getCmd('procengine','direct');

		// Get the absolute path to site's root
		$siteroot = JPATH_SITE;

		// Get the JPS password
		$password = AkeebaHelperEscape::escapeJS(JRequest::getVar('jps_key',''));

		$data = "<?php\ndefined('_AKEEBA_RESTORATION') or die('Restricted access');\n";
		$data .= '$restoration_setup = array('."\n";
		$data .= <<<ENDDATA
	'kickstart.security.password' => '{$this->password}',
	'kickstart.tuning.max_exec_time' => '5',
	'kickstart.tuning.run_time_bias' => '75',
	'kickstart.tuning.min_exec_time' => '0',
	'kickstart.procengine' => '$procengine',
	'kickstart.setup.sourcefile' => '{$this->path}',
	'kickstart.setup.destdir' => '$siteroot',
	'kickstart.setup.restoreperms' => '0',
	'kickstart.setup.filetype' => '{$this->extension}',
	'kickstart.setup.dryrun' => '0',
	'kickstart.jps.password' => '$password'
ENDDATA;

		if($procengine == 'ftp')
		{
			$ftp_host	= JRequest::getVar('ftp_host','');
			$ftp_port	= JRequest::getVar('ftp_port', '21');
			$ftp_user	= JRequest::getVar('ftp_user', '');
			$ftp_pass	= JRequest::getVar('ftp_pass', '', 'default', 'none', 2); // Password should be allowed as raw mode, otherwise !@<sdf34>43H% would be trimmed to !@43H% which is plain wrong :@
			$ftp_root	= JRequest::getVar('ftp_root', '');
			$tempdir	= JRequest::getVar('tmp_path', '');
			$data.=<<<ENDDATA
	,
	'kickstart.ftp.ssl' => '0',
	'kickstart.ftp.passive' => '1',
	'kickstart.ftp.host' => '$ftp_host',
	'kickstart.ftp.port' => '$ftp_port',
	'kickstart.ftp.user' => '$ftp_user',
	'kickstart.ftp.pass' => '$ftp_pass',
	'kickstart.ftp.dir' => '$ftp_root',
	'kickstart.ftp.tempdir' => '$tempdir'
ENDDATA;
		}

		$data .= ');';

		// Remove the old file, if it's there...
		jimport('joomla.filesystem.file');
		$configpath = JPATH_COMPONENT_ADMINISTRATOR.'/restoration.php';
		if( JFile::exists($configpath) )
		{
			JFile::delete($configpath);
		}

		// Write new file
		$result = JFile::write( $configpath, $data );
		return $result;
	}

	function getFTPParams()
	{
		$config =& JFactory::getConfig();
		return array(
			'procengine'	=> $config->get('config.ftp_enable', 0) ? 'ftp' : 'direct',
			'ftp_host'		=> $config->get('config.ftp_host', 'localhost'),
			'ftp_port'		=> $config->get('config.ftp_port', '21'),
			'ftp_user'		=> $config->get('config.ftp_user', ''),
			'ftp_pass'		=> $config->get('config.ftp_pass', ''),
			'ftp_root'		=> $config->get('config.ftp_root', ''),
			'tempdir'		=> $config->get('config.tmp_path', '')
		);
	}

	function getExtractionModes()
	{
		$options = array();
		$options[] = JHTML::_('select.option', 'direct', JText::_('RESTORE_LABEL_EXTRACTIONMETHOD_DIRECT'));
		$options[] = JHTML::_('select.option', 'ftp', JText::_('RESTORE_LABEL_EXTRACTIONMETHOD_FTP'));
		return $options;
	}
	
	function doAjax()
	{
		$ajax = $this->getState('ajax');
		switch($ajax)
		{
			// FTP Connection test for DirectFTP
			case 'testftp':
				// Grab request parameters
				$config = array(
					'host' => JRequest::getVar('host'),
					'port' => JRequest::getVar('port'),
					'user' => JRequest::getVar('user'),
					'pass' => JRequest::getVar('pass'),
					'initdir' => JRequest::getVar('initdir'),
					'usessl' => JRequest::getVar('usessl') == 'true',
					'passive' => JRequest::getVar('passive') == 'true'
				);

				// Perform the FTP connection test
				$test = new AEArchiverDirectftp();
				$test->initialize('', $config);
				$errors = $test->getError();
				if(empty($errors))
				{
					$result = true;
				}
				else
				{
					$result = $errors;
				}
				break;

			// Unrecognized AJAX task
			default:
				$result = false;
				break;
		}
		
		return $result;
	}
}