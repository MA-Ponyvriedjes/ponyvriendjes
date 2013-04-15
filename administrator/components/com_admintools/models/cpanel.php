<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

/**
 * The Control Panel model
 *
 */
class AdmintoolsModelCpanels extends FOFModel
{
	/**
	 * Constructor; dummy for now
	 *
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getPluginID()
	{
		$db = $this->getDBO();
		if(ADMINTOOLS_JVERSION == '15') {
			$sql = 'SELECT id'
				. ' FROM #__plugins'
				. ' WHERE published >= 1'
				. ' AND (folder = "system")'
				. ' AND (element = "admintools")'
				. ' ORDER BY ordering'
				. ' LIMIT 0,1';
			$db->setQuery( $sql );
			$id = $db->loadResult();			
		} else {
			$query = FOFQueryAbstract::getNew($db)
				->select($db->qn('extension_id'))
				->from($db->qn('#__extensions'))
				->where($db->qn('enabled').' >= '.$db->quote('1'))
				->where($db->qn('folder').' = '.$db->quote('system'))
				->where($db->qn('element').' = '.$db->quote('admintools'))
				->where($db->qn('type').' = '.$db->quote('plugin'))
				->order($db->qn('ordering').' ASC');
			$db->setQuery( $query );
			$id = $db->loadResult();
		}
		return $id;
	}
	
	/**
	 * Automatically migrates settings from the component's parameters storage
	 * to our version 2.1+ dedicated storage table.
	 */
	public function autoMigrate()
	{
		// First, load the component parameters
		// FIX 2.1.13: Load the component parameters WITHOUT using JComponentHelper
		$db = JFactory::getDbo();
		if( version_compare(JVERSION,'1.6.0','ge') ) {
			$query = FOFQueryAbstract::getNew($db)
				->select($db->qn('params'))
				->from($db->qn('#__extensions'))
				->where($db->qn('type').' = '.$db->quote('component'))
				->where($db->qn('element').' = '.$db->quote('com_admintools'));
			$db->setQuery($query);
		} else {
			$sql = 'SELECT '.$db->nameQuote('params').' FROM '.$db->nameQuote('#__components').
				' WHERE '.$db->nameQuote('option').' = '.$db->Quote('com_admintools').
				" AND `parent` = 0 AND `menuid` = 0";
			$db->setQuery($sql);
		}
		$rawparams = $db->loadResult();
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$cparams = new JRegistry();
			$cparams->loadJSON($rawparams);
		} else {
			$cparams = new JParameter($rawparams);
		}
		
		// Migrate parameters
		$allParams = $cparams->toArray();
		$safeList = array('liveupdate','downloadid','lastversion','minstability');
		$params = JModel::getInstance('Storage','AdmintoolsModel');
		$modified = 0;
		foreach($allParams as $k => $v) {
			if(in_array($k, $safeList)) continue;
			if($v == '') continue;
			
			$modified++;
			
			$cparams->setValue($k, null);
			$params->setValue($k, $v);
		}
		
		if($modified == 0) return;
		
		// Save new parameters
		$params->save();
		
		// Save component parameters
		$db = JFactory::getDBO();
		$data = $cparams->toString();

		if( ADMINTOOLS_JVERSION != '15' )
		{
			$sql = FOFQueryAbstract::getNew($db)
				->update($db->qn('#__extensions'))
				->set($db->qn('params').' = '.$db->q($data))
				->where($db->qn('element').' = '.$db->q('com_admintools'))
				->where($db->qn('type').' = '.$db->q('component'));
		}
		else
		{
			// Joomla! 1.5
			$sql = 'UPDATE `#__components` SET `params` = '.$db->Quote($data).' WHERE '.
				"`option` = 'com_admintools' AND `parent` = 0 AND `menuid` = 0";
		}

		$db->setQuery($sql);
		$db->query();
	}
	
	public function needsDownloadID()
	{
		jimport('joomla.application.component.helper');
		
		// Do I need a Download ID?
		$ret = false;
		$isPro = ADMINTOOLS_PRO;
		if(!$isPro) {
			$ret = true;
		} else {
			$ret = false;
			$params = JComponentHelper::getParams('com_admintools');
			$dlid = $params->getValue('downloadid', '');
			if(!preg_match('/^[0-9a-f]{32}$/i', $dlid)) {
				$ret = true;
			}
		}
		
		// Do not continue on older Joomla! versions
		if(version_compare(JVERSION, '1.7.0', 'lt')) {
			return $ret;
		}
		
		// Deactivate update site for Admin Tools
		$component = JComponentHelper::getComponent('com_admintools');
		
		$db = JFactory::getDbo();
		$query = FOFQueryAbstract::getNew($db)
			->select('update_site_id')
			->from($db->qn('#__update_sites_extensions'))
			->where($db->qn('extension_id').' = '.$db->q($component->id));
		$db->setQuery($query);
		$updateSite = $db->loadResult();
		
		if($updateSite) {
			$query = FOFQueryAbstract::getNew($db)
				->delete($db->qn('#__update_sites'))
				->where($db->qn('update_site_id').' = '.$db->q($updateSite));
			$db->setQuery($query);
			$db->query();
			
			$query = FOFQueryAbstract::getNew($db)
				->delete($db->qn('#__update_sites_extensions'))
				->where($db->qn('update_site_id').' = '.$db->q($updateSite));
			$db->setQuery($query);
			$db->query();
		}
		
		// Deactivate the update site for FOF
		$query = FOFQueryAbstract::getNew($db)
			->from('#__update_sites')
			->select('update_site_id')
			->select('update_site_id')
			->where($db->qn('location').' = '.$db->q('http://cdn.akeebabackup.com/updates/libraries/fof'));
		$db->setQuery($query);
		$updateSite = $db->loadResult();
		
		if($updateSite) {
			$query = FOFQueryAbstract::getNew($db)
				->delete($db->qn('#__update_sites'))
				->where($db->qn('update_site_id').' = '.$db->q($updateSite));
			$db->setQuery($query);
			$db->query();
			
			$query = FOFQueryAbstract::getNew($db)
				->delete($db->qn('#__update_sites_extensions'))
				->where($db->qn('update_site_id').' = '.$db->q($updateSite));
			$db->setQuery($query);
			$db->query();
		}
		
		return $ret;
	}
	
	/**
	 * Makes sure that the Professional release can be updated using Joomla!'s
	 * own update system. THIS IS AN AKEEBA ORIGINAL!
	 * 
	 * @return bool False if the Download ID is of an incorrect format
	 */
	public function applyJoomlaExtensionUpdateChanges($isPro = -1)
	{
		$ret = true;
		
		// Don';'t bother if this is not Joomla! 1.7+
		if(!version_compare(JVERSION, '1.7.0', 'ge')) return $ret;
		
		// Do we have Admin Tools Professional?
		if($isPro === -1) {
			$isPro = ADMINTOOLS_PRO;
		}
		
		// Action parameters
		$action = 'none'; // What to do: none, update, create, delete
		$purgeUpdates = false; // Should I purge existing updates?
		$fetchUpdates = false; // Should I fetch new udpates
		
		// Init
		$db = $this->getDbo();
		
		// Figure out the correct XML update stream URL
		if($isPro) {
			$update_url = 'https://www.akeebabackup.com/index.php?option=com_ars&view=update&task=stream&format=xml&id=6';
			jimport('joomla.application.component.helper');
			$params = JComponentHelper::getParams('com_admintools');
			$dlid = $params->getValue('downloadid','');
			if(!preg_match('/^[0-9a-f]{32}$/i', $dlid)) {
				$ret = false;
				$dlid = '';
			}
			if($dlid) {
				$dlid = $dlid;
				$url = $update_url.'&dlid='.$dlid.'/extension.xml';
			} else {
				$url = '';
			}
		} else {
			$url = 'http://cdn.akeebabackup.com/updates/atcore.xml';
		}
		
		// Get the extension ID
		$extensionID = JComponentHelper::getComponent('com_admintools')->id;
		
		// Get the update site record
		$query = $db->getQuery(true)
			->select(array(
			$db->qn('us').'.*',
		))->from(
			$db->qn('#__update_sites_extensions').' AS '.$db->qn('map')
		)->innerJoin(
			$db->qn('#__update_sites').' AS '.$db->qn('us').' ON ('.
			$db->qn('us').'.'.$db->qn('update_site_id').' = '.
				$db->qn('map').'.'.$db->qn('update_site_id').')'
		)
		->where(
			$db->qn('map').'.'.$db->qn('extension_id').' = '.$db->q($extensionID)
		);
		$db->setQuery($query);
		$update_site = $db->loadObject();		
		
		// Decide on the course of action to take
		if($url) {
			if(!is_object($update_site)) {
				$action = 'create';
				$fetchUpdates = true;
			} else {
				$action = ($update_site->location != $url) ? 'update' : 'none';
				$purgeUpdates = $action == 'update';
				$fetchUpdates = $action == 'update';
			}
		} else {
			// Disable the update site for Admin Tools
			if(!is_object($update_site)) {
				$action = 'none';
			} else {
				$action = 'delete';
				$purgeUpdates = true;
			}
		}
		
		switch($action)
		{
			case 'none':
				// No change
				break;
			
			case 'create':
			case 'update':
				// Remove old update site
				$query = $db->getQuery(true)
					->delete($db->qn('#__update_sites'))
					->where($db->qn('name') .' = '. $db->q('Admin Tools updates'));
				$db->setQuery($query);
				$db->query();
				// Create new update site
				$oUpdateSite = (object)array(
					'name'					=> 'Admin Tools updates',
					'type'					=> 'extension',
					'location'				=> $url,
					'enabled'				=> 1,
					'last_check_timestamp'	=> 0,
				);
				$db->insertObject('#__update_sites', $oUpdateSite);
				// Get the update site ID
				$usID = $db->insertid();
				// Delete existing #__update_sites_extensions records
				$query = $db->getQuery(true)
					->delete($db->qn('#__update_sites_extensions'))
					->where($db->qn('extension_id') .' = '. $db->q($extensionID));
				$db->setQuery($query);
				$db->query();
				// Create new #__update_sites_extensions record
				$oUpdateSitesExtensions = (object)array(
					'update_site_id'		=> $usID,
					'extension_id'			=> $extensionID
				);
				$db->insertObject('#__update_sites_extensions', $oUpdateSitesExtensions);
				break;
			
			case 'delete':
				// Remove update sites
				$query = $db->getQuery(true)
					->delete($db->qn('#__update_sites'))
					->where($db->qn('update_site_id') .' = '. $db->q($update_site->update_site_id));
				$db->setQuery($query);
				$db->query();
				// Delete existing #__update_sites_extensions records
				$query = $db->getQuery(true)
					->delete($db->qn('#__update_sites_extensions'))
					->where($db->qn('extension_id') .' = '. $db->q($extensionID));
				$db->setQuery($query);
				$db->query();
				break;
		}
		
		// Do I have to purge updates?
		if($purgeUpdates) {
			$query = $db->getQuery(true)
				->delete($db->qn('#__updates'))
				->where($db->qn('element').' = '.$db->q('com_admintools'));
			$db->setQuery($query);
			$db->query();
		}
		
		// Do I have to fetch updates?
		if($fetchUpdates) {
			jimport('joomla.update.update');
			$x = new JUpdater();
			$x->findUpdates($extensionID);
		}
		
		return $ret;
	}

}