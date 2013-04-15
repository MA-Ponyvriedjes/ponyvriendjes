<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 3.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

/**
 * The controller to handle actions against remote files
 * @author nicholas
 */
class AkeebaControllerRemotefiles extends JController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		if(AKEEBA_JVERSION=='16')
		{
			// Access check, Joomla! 1.6 style.
			$user = JFactory::getUser();
			if (!$user->authorise('akeeba.download', 'com_akeeba')) {
				$this->setRedirect('index.php?option=com_akeeba');
				return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
				$this->redirect();
			}
		} else {
			// Custom ACL for Joomla! 1.5
			$aclModel = JModel::getInstance('Acl','AkeebaModel');
			if(!$aclModel->authorizeUser('download')) {
				$this->setRedirect('index.php?option=com_akeeba');
				return JError::raiseWarning(403, JText::_('Access Forbidden'));
				$this->redirect();
			}
		}
		$base_path = JPATH_COMPONENT_ADMINISTRATOR.'/plugins';
		$model_path = $base_path.'/models';
		$view_path = $base_path.'/views';
		$this->addModelPath($model_path);
		$this->addViewPath($view_path);
	}
	
	/**
	 * This controller does not support a default task, thank you.
	 * 
	 * @see libraries/joomla/application/component/JController#display($cachable)
	 */
	public function display()
	{
		JError::raiseError(500, 'Invalid task');
		return false;
	}
	
	/**
	 * Lists the available remote storage actions for a specific backup entry
	 */
	public function listactions()
	{
		// List available actions
		$id = $this->getAndCheckId();
		
		if($id === false) {
			JError::raiseError(500, 'Invalid ID');
			return false;
		}
		
		parent::display(false);
	}
	
	
	/**
	 * Fetches a complete backup set from a remote storage location to the local (server)
	 * storage so that the user can download or restore it.
	 */
	public function dltoserver()
	{
		// Get the parameters
		$id = $this->getAndCheckId();
		$part = JRequest::getInt('part', -1);
		$frag = JRequest::getInt('frag', -1);
		
		// Check the ID
		if($id === false) {
			$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
			$this->setRedirect($url, JText::_('REMOTEFILES_ERR_INVALIDID'), 'error');
			return;
		}
		
		// Gather the necessary information to perform the download 
		$stat = AEPlatform::getInstance()->get_statistics($id);
		$remoteFilename = $stat['remote_filename'];
		$rfparts = explode('://', $remoteFilename);
		$engine = AEFactory::getPostprocEngine($rfparts[0]);
		$remote_filename = $rfparts[1];
		
		// Load the correct backup profile
		AEPlatform::getInstance()->load_configuration($stat['profile_id']);
		$config = AEFactory::getConfiguration();
		
		// Get a reference to the session object (used to pass around data since MVC objects are not
		// singletons in the Joomla! API... I hate myself for having to do that!)
		$session =& JFactory::getSession();
		
		// Start timing ourselves
		$timer = AEFactory::getTimer(); // The core timer object
		$start = $timer->getRunningTime(); // Mark the start of this download
		$break = false; // Don't break the step
		
		while($timer->getTimeLeft() && !$break && ($part < $stat['multipart']) )
		{
			// Get the remote and local filenames
			$basename = basename($remote_filename);
			$extension = strtolower(str_replace(".", "", strrchr($basename, ".")));
			
			if($part > 0) {
				$new_extension = substr($extension,0,1) . sprintf('%02u', $part); 
			} else {
				$new_extension = $extension;
			}
			
			$filename = $basename.'.'.$new_extension;
			$remote_filename = substr($remote_filename, 0, -strlen($extension)).$new_extension;
			
			// Figure out where on Earth to put that file
			$local_file = $config->get('akeeba.basic.output_directory').'/'.basename($remote_filename);
			
			// Do we have to initialize the process?
			if($part == -1) {
				// Total size to download
				$session->set('dl_totalsize', $stat['total_size'], 'akeeba');
				// Currently downloaded size
				$session->set('dl_donesize', 0, 'akeeba');
				// Init
				$part = 0;
			}
			
			// Do we have to initialize the file?
			if($frag == -1) {
				// Delete and touch the output file
				AEPlatform::getInstance()->unlink($local_file);
				$fp = @fopen($local_file, 'wb');
				if($fp !== false) @fclose($fp);
				// Init
				$frag = 0;
			}
			
			// Calculate from and length
			$length = 1048576;
			$from = $frag * $length + 1;
			
			// Try to download the first frag
			$temp_file = $local_file.'.tmp';
			$staggered = true;
			$required_time = 1.0;
			$result = $engine->downloadToFile($remote_filename, $temp_file, $from, $length);
			if($result == -1) {
				// The engine doesn't support staggered downloads
				$staggered = false;
				$result = $engine->downloadToFile($remote_filename, $temp_file);
			}
			
			if(!$result) {
				// Failed download
				if(
					(
					( ($part < $stat['multipart']) || ( ($stat['multipart'] == 0) && ($part == 0) ) ) &&
					( $frag == 0 )
					)
					||
					!$staggered
				){
					// Failure to download the part's beginning = failure to download. Period.
					$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
					$this->setRedirect($url, JText::_('REMOTEFILES_ERR_CANTDOWNLOAD').$engine->getWarning(), 'error');
					return;
				} elseif( $part >= $stat['multipart']){
					// Just finished! Update the stats record.
					$stat['filesexist'] = 1;
					AEPlatform::getInstance()->set_or_update_statistics($id, $stat, $engine);
					$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
					$this->setRedirect($url, JText::_('REMOTEFILES_LBL_JUSTFINISHED'));
					return;
				} else {
					// Since this is a staggered download, consider this normal and go to the next part.
					$part++; $frag = -1;
				}
			}
			
			// Add the currently downloaded frag to the total size of downloaded files
			if($result) {
				$filesize = (int)@filesize($temp_file);
				$total = $session->get('dl_donesize', 0, 'akeeba');
				$total += $filesize;
				$session->set('dl_donesize', $total, 'akeeba');
			}
			
			// Successful download, or have to move to the next part.
			if($staggered) {
				if($result)
				{
					// Append the file
					$fp = @fopen($local_file,'ab');
					if($fp === false) {
						// Can't open the file for writing
						AEPlatform::getInstance()->unlink($temp_file);
						$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
						$this->setRedirect($url, JText::sprintf('REMOTEFILES_ERR_CANTOPENFILE', $local_file), 'error');
						return;
					}
					$tf = fopen($temp_file,'rb');
					while(!feof($tf)) {
						$data = fread($tf, 262144);
						fwrite($fp, $data);
					}
					fclose($tf);
					fclose($fp);
					AEPlatform::getInstance()->unlink($tf);
				}
				
				// Advance the frag pointer and mark the end
				$end = $timer->getRunningTime();
				$frag++;
			} else {
				if($result)
				{
					// Rename the temporary file
					AEPlatform::getInstance()->unlink($local_file);
					$result = AEPlatform::getInstance()->move($temp_file, $local_file);
					if(!$result) {
						// Renaming failed. Goodbye.
						AEPlatform::getInstance()->unlink($temp_file);
						$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
						$this->setRedirect($url, JText::sprintf('REMOTEFILES_ERR_CANTOPENFILE', $local_file), 'error');
					}
				}
				// In whole part downloads we break the step without second thought
				$break = true;
				$end = $timer->getRunningTime();
				$frag = -1;
				$part++;
			}
			
			// Do we predict that we have enough time?
			$required_time = max(1.1 * ($end - $start), $required_time);
			if( $timer->getTimeLeft() < $required_time ) $break = true; 
			$start = $end;
		}
		
		// Pass the id, part, frag in the request so that the view can grab it
		JRequest::setVar('id', $id);
		JRequest::setVar('part', $part);
		JRequest::setVar('frag', $frag);
		
		if($part >= $stat['multipart']) {
			// Just finished!
			$stat['filesexist'] = 1;
			AEPlatform::getInstance()->set_or_update_statistics($id, $stat, $engine);
			$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
			$this->setRedirect($url, JText::_('REMOTEFILES_LBL_JUSTFINISHED'));
			return;			
		}
		
		parent::display(false);
	}
	
	/**
	 * Downloads a file from the remote storage to the user's browsers
	 */
	public function dlfromremote()
	{
		$id = $this->getAndCheckId();
		$part = JRequest::getInt('part', 0);
		
		if($id === false) {
			$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
			$this->setRedirect($url, JText::_('REMOTEFILES_ERR_INVALIDID'), 'error');
			return;
		}
		
		$stat = AEPlatform::getInstance()->get_statistics($id);
		$remoteFilename = $stat['remote_filename'];
		$rfparts = explode('://', $remoteFilename);
		$engine = AEFactory::getPostprocEngine($rfparts[0]);
		$remote_filename = $rfparts[1];
		
		$basename = basename($remote_filename);
		$extension = strtolower(str_replace(".", "", strrchr($basename, ".")));
		
		if($part > 0) {
			$new_extension = substr($extension,0,1) . sprintf('%02u', $part); 
		} else {
			$new_extension = $extension;
		}
		
		$filename = $basename.'.'.$new_extension;
		$remote_filename = substr($remote_filename, 0, -strlen($extension)).$new_extension;
		
		if($engine->downloads_to_browser_inline)
		{
			@ob_end_clean();
			@clearstatcache();
			// Send MIME headers
			header('MIME-Version: 1.0');
			header('Content-Disposition: attachment; filename='.$filename);
			header('Content-Transfer-Encoding: binary');
			switch($extension)
			{
				case 'zip':
					// ZIP MIME type
					header('Content-Type: application/zip');
					break;
	
				default:
					// Generic binary data MIME type
					header('Content-Type: application/octet-stream');
					break;
			}
			// Disable caching
			header('Expires: Mon, 20 Dec 1998 01:00:00 GMT');
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
		}
		
		AEPlatform::getInstance()->load_configuration($stat['profile_id']);
		$result = $engine->downloadToBrowser($remote_filename);
		
		if(is_string($result) && ($result !== true) && $result !== false)
		{
			// We have to redirect
			$result = str_replace('://%2F','://', $result);
			@ob_end_clean();
			header('Location: '.$result);
			flush();
			JFactory::getApplication()->close();
		} elseif($result === false ) {
			// Failed to download
			$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
			$this->setRedirect($url, $engine->getWarning(), 'error');
		}
		
		return;
	}
	
	
	/**
	 * Deletes a file from the remote storage
	 */
	public function delete()
	{
		// Get the parameters
		$id = $this->getAndCheckId();
		$part = JRequest::getInt('part', -1);
		
		// Check the ID
		if($id === false) {
			$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
			$this->setRedirect($url, JText::_('REMOTEFILES_ERR_INVALIDID'), 'error');
			return;
		}
		
		// Gather the necessary information to perform the delete 
		$stat = AEPlatform::getInstance()->get_statistics($id);
		$remoteFilename = $stat['remote_filename'];
		$rfparts = explode('://', $remoteFilename);
		$engine = AEFactory::getPostprocEngine($rfparts[0]);
		$remote_filename = $rfparts[1];
		
		// Load the correct backup profile
		AEPlatform::getInstance()->load_configuration($stat['profile_id']);
		$config = AEFactory::getConfiguration();
		
		// Start timing ourselves
		$timer = AEFactory::getTimer(); // The core timer object
		$start = $timer->getRunningTime(); // Mark the start of this download
		$break = false; // Don't break the step
		
		while($timer->getTimeLeft() && !$break && ($part < $stat['multipart']) )
		{
			// Get the remote filename
			$basename = basename($remote_filename);
			$extension = strtolower(str_replace(".", "", strrchr($basename, ".")));
			
			if($part > 0) {
				$new_extension = substr($extension,0,1) . sprintf('%02u', $part); 
			} else {
				$new_extension = $extension;
			}
			
			$filename = $basename.'.'.$new_extension;
			$remote_filename = substr($remote_filename, 0, -strlen($extension)).$new_extension;
			
			// Do we have to initialize the process?
			if($part == -1) {
				// Init
				$part = 0;
			}
			
			// Try to delete the part
			$required_time = 1.0;
			$result = $engine->delete($remote_filename);
			
			if(!$result) {
				// Failed delete
				$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
				$this->setRedirect($url, JText::_('REMOTEFILES_ERR_CANTDELETE').$engine->getWarning(), 'error');
				return;
			} else {
				// Successful delete
				$end = $timer->getRunningTime();
				$part++;
			}
			
			// Do we predict that we have enough time?
			$required_time = max(1.1 * ($end - $start), $required_time);
			if( $timer->getTimeLeft() < $required_time ) $break = true; 
			$start = $end;
		}
		
		if($part >= $stat['multipart']) {
			// Just finished!
			$stat['remote_filename'] = '';
			AEPlatform::getInstance()->set_or_update_statistics($id, $stat, $engine);
			$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=listactions&id='.$id;
			$this->setRedirect($url, JText::_('REMOTEFILES_LBL_JUSTFINISHEDELETING'));
			return;			
		} else {
			// More work to do...
			$url = 'index.php?option=com_akeeba&view=remotefiles&tmpl=component&task=delete&id='.$id.'&part='.$part;
			$this->setRedirect($url);
			return;
		}
		
		parent::display(false);
	}
	
	/**
	 * Gets the stats record ID from the request and checks that it does exist
	 * 
	 * @return bool|int False if an invalid ID is found, the numeric ID if it's valid
	 */
	private function getAndCheckId()
	{
		$id = JRequest::getInt('id',0);
		
		if($id <= 0) return false;

		$statObject = AEPlatform::getInstance()->get_statistics($id);
		if(empty($statObject) || !is_array($statObject)) return false;

		return $id;
	}
}