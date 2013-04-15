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

/**
 * A model to handle the administration of remotely stored files
 * @author nicholas
 */
class AkeebaModelRemotefiles extends JModel
{
	/**
	 * Returns an icon definition list for the applicable actions on this backup record 
	 * @param $id int The backup record ID to get the actions for
	 * @return array
	 */
	function getActions($id)
	{
		$actions = array();
		
		// Load the stats record
		$stat = AEPlatform::getInstance()->get_statistics($id);
		
		// Get the post-proc engine from the remote location
		$remote_filename = $stat['remote_filename'];
		if(empty($remote_filename)) return $actions;
		
		$rfparts = explode('://', $remote_filename, 2);
		$engine = AEFactory::getPostprocEngine($rfparts[0]);
		
		$filename = $rfparts[1];
		
		// Does the engine support local d/l and we need to d/l the file locally?
		if( $engine->can_download_to_file && !$stat['filesexist'] ) {
			// Add a "Fetch back to server" button
			$action = array(
				'label'				=> JText::_('REMOTEFILES_FETCH'),
				'link'				=> "index.php?option=com_akeeba&view=remotefiles&task=dltoserver&tmpl=component&id={$stat['id']}&part=-1",
				'type'				=> 'button',
				'icon'				=> 'download'
			);
			$actions[] = $action;
		}
		
		// Does the engine support remote deletes?
		if($engine->can_delete) {
			// Add a Delete button
			$action = array(
				'label'				=> JText::_('REMOTEFILES_DELETE'),
				'link'				=> "index.php?option=com_akeeba&view=remotefiles&task=delete&tmpl=component&id={$stat['id']}&part=-1",
				'type'				=> 'button',
				'icon'				=> 'delete'
			);
			$actions[] = $action;
		}

		// Does the engine support downloads to browser?
		if($engine->can_download_to_browser) {
			$parts = $stat['multipart'];
			if($parts == 0) $parts++;
			for($i = 0; $i < $parts; $i++)
			{
				$action = array(
					'label'				=> JText::sprintf('REMOTEFILES_PART', $i),
					'link'				=> "index.php?option=com_akeeba&view=remotefiles&task=dlfromremote&id={$stat['id']}&part=$i",
					'type'				=> 'link'
				);
				$actions[] = $action;
			}
		}
		
		return $actions;
	}
}