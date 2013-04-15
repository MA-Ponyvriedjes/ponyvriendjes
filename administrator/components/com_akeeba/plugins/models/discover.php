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

jimport('joomla.application.component.model');

class AkeebaModelDiscover extends JModel
{
	public function getFiles($directory)
	{
		$ret = array();
		
		// Get all archive files
		$allFiles = AEUtilScanner::getFiles($directory, true);
		$files = array();
		if(!empty($allFiles)) foreach($allFiles as $file) {
			$ext = strtoupper(substr($file, -3));
			if(in_array($ext, array('JPA','JPS','ZIP'))) $files[] = $file;
		}
		
		// If nothing found, bail out
		if(empty($files)) return $ret;
		
		// Make sure these files do not already exist in another backup record
		$db = $this->getDBO();
		$sql = 'SELECT '.$db->nameQuote('absolute_path').' FROM '.$db->nameQuote('#__ak_stats').' WHERE '.
			$db->nameQuote('absolute_path').' LIKE \''.$db->getEscaped($directory).'%\' AND '.
			$db->nameQuote('filesexist').' = 1';
		$db->setQuery($sql);
		$existingfiles = $db->loadResultArray();
		
		foreach($files as $file) {
			if(!in_array($file, $existingfiles)) $ret[] = $file;
		}
		
		return $ret;
	}
	
	public function import($directory, $file)
	{
		// Find out how many parts there are
		$multipart = 0;
		$base = substr($file, 0, -4);
		$ext = substr($file, -3);
		$found = true;
		
		$total_size = @filesize($directory.'/'.$file);
		
		while($found)
		{
			$multipart++;
			$newExtension = substr($ext,0,1).sprintf('%02u', $multipart);
			$newFile = $directory.'/'.$base.'.'.$newExtension;
			$found = file_exists($newFile);
			if($found) $total_size += @filesize($newFile);
		}
		
		$multipart--;
		
		$filetime = @filemtime($directory.'/'.$file);
		if(empty($filetime)) $filetime = time();
		
		// Create a new backup record
		$record = array(
			'description'		=> JText::_('DISCOVER_LABEL_IMPORTEDDESCRIPTION'),
			'comment'			=> '',
			'backupstart'		=> date('Y-m-d H:i:s',$filetime),
			'backupend'			=> date('Y-m-d H:i:s',$filetime + 1),
			'status'			=> 'complete',
			'origin'			=> 'backend',
			'type'				=> 'full',
			'profile_id'		=> 1,
			'archivename'		=> $file,
			'absolute_path'		=> $directory.'/'.$file,
			'multipart'			=> $multipart,
			'tag'				=> 'backend',
			'filesexist'		=> 1,
			'remote_filename'	=> '',
			'total_size'		=> $total_size
		);
		$id = null;
		$id = AEPlatform::getInstance()->set_or_update_statistics($id, $record, $this);
	}
}
