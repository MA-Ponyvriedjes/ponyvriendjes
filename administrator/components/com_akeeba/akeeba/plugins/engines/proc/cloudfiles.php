<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2011 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id$
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

class AEPostprocCloudfiles extends AEAbstractPostproc
{
	public function processPart($absolute_filename)
	{
		// Retrieve engine configuration data
		$config =& AEFactory::getConfiguration();

		$username	= trim( $config->get('engine.postproc.cloudfiles.username', '') );
		$apikey		= trim( $config->get('engine.postproc.cloudfiles.apikey', '') );
		$container	= $config->get('engine.postproc.cloudfiles.container', 0);
		$directory	= $config->get('volatile.postproc.directory', null);
		if(empty($directory)) $directory	= $config->get('engine.postproc.cloudfiles.directory', 0);

		// Sanity checks
		if(empty($username))
		{
			$this->setWarning('You have not set up your CloudFiles user name');
			return false;
		}

		if(empty($apikey))
		{
			$this->setWarning('You have not set up your CoudFiles API Key');
			return false;
		}

		if(empty($container))
		{
			$this->setWarning('You have not set up your CloudFiles container');
			return false;
		}

		// Fix the directory name, if required
		if(!empty($directory))
		{
			$directory = trim($directory);
			$directory = ltrim( AEUtilFilesystem::TranslateWinPath( $directory ) ,'/');
		}
		else
		{
			$directory = '';
		}

		// Parse tags
		$directory = AEUtilFilesystem::replace_archive_name_variables($directory);
		$config->set('volatile.postproc.directory', $directory);

		// Calculate relative remote filename
		$filename = basename($absolute_filename);
		if( !empty($directory) && ($directory != '/') ) $filename = $directory . '/' . $filename;
		
		// Store the absolute remote path in the class property
		$this->remote_path = $filename;
		
		// Connect and send
		$dummy = new AEUtilCloudfiles(); // Just to make it load the necessary class file
		$auth = new AEUtilCFAuthentication($username, $apikey);
		try
		{
			$auth->authenticate();
			$conn = new AEUtilCFConnection($auth);
			$cont = $conn->get_container($container);
			$object = $cont->create_object($filename);
			$object->content_type = 'application/octet-stream';
			$object->load_from_filename($absolute_filename);
		}
		catch(Exception $e)
		{
			$this->setWarning($e->getMessage());
			return false;
		}

		return true;
	}
}