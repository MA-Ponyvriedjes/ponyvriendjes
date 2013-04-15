<?php
/*
 *  Administrator Tools
 *  Copyright (C) 2010-2011  Nicholas K. Dionysopoulos / AkeebaBackup.com
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

jimport('joomla.application.plugin');

class plgSystemAdmintoolsCore extends JPlugin
{
	private $cparams = null;

	public function __construct(& $subject, $config = array())
	{
		jimport('joomla.html.parameter');
		jimport('joomla.plugin.helper');
		jimport('joomla.application.component.helper');
		$plugin =& JPluginHelper::getPlugin('system', 'admintools');
		$defaultConfig = (array)($plugin);
		
		$config = array_merge($defaultConfig, $config);
		
		// Use the parent constructor to create the plugin object
		parent::__construct($subject, $config);
		
		// Load the components parameters
		jimport('joomla.application.component.model');
		require_once JPATH_ROOT.'/administrator/components/com_admintools/models/storage.php';
		$this->cparams = JModel::getInstance('Storage','AdmintoolsModel');
	}
	
	public function onAfterInitialise()
	{
		$app = JFactory::getApplication();
		
		if(in_array($app->getName(),array('administrator','admin'))) {
			// Back-end stuff
		} else {
			// Front-end stuff
		}
	}
	
	public function onAfterRender()
	{
		$app = JFactory::getApplication();
		if(in_array($app->getName(),array('administrator','admin'))) return;
		
		if($this->cparams->getValue('linkmigration',0) == 1) {
			$this->linkMigration();
		}
		
		if($this->cparams->getValue('httpsizer',0) == 1) {
			$this->httpsizer();
		}
	}
	
	/**
	 * Provides link migration services. All absolute links pointing to any of the old domain names
	 * are being rewritten to point to the current domain name. This runs a full page replacement
	 * using Regular Expressions, so even menus with absolute URLs will be migrated!
	 */
	private function linkMigration()
	{
		$buffer = JResponse::getBody();
		
		$pattern = '/(href|src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $buffer, $matches, PREG_OFFSET_CAPTURE);
		
		if($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';
	
			// Loop all URLs
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($buffer, $last_position, $entry[1]-$last_position);
				// Add the new URL
				$temp .= $this->replaceDomain($entry[0]);
				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($buffer))
				$temp .= substr($buffer, $last_position);
			// Replace content with the processed one
			unset($buffer);
			JResponse::setBody($temp);
			unset($temp);
		}		
	}
	
	/**
	 * Replaces a URL's domain name (if it is in the substitution list) with the
	 * current site's domain name
	 * @param $url string The URL to process
	 * @return string The processed URL
	 */
	private function replaceDomain($url)
	{
		static $old_domains;
		static $mydomain;

		if(empty($old_domains))
		{
			$temp = explode("\n", $this->cparams->getValue('migratelist',''));
			if(!empty($temp))
			{
				foreach($temp as $entry)
				{
					if(substr($entry,-1) == '/') $entry = substr($entry,0,-1);
					if(substr($entry,0,7) == 'http://') $entry = substr($entry,7);
					if(substr($entry,0,8) == 'https://') $entry = substr($entry,8);
					$old_domains[] = $entry;
				}
			}
		}
		if(empty($mydomain))
		{
			$mydomain = JURI::base(false);
			if(substr($mydomain,-1) == '/') $mydomain = substr($mydomain,0,-1);
			if(substr($mydomain,0,7) == 'http://') $mydomain = substr($mydomain,7);
			if(substr($mydomain,0,8) == 'https://') $mydomain = substr($mydomain,8);
		}
	
		if(!empty($old_domains))
			foreach($old_domains as $domain)
			{
				if (substr($url, 0, strlen($domain)) == $domain)
				{
					return $mydomain.substr($url, strlen($domain));
				} elseif (substr($url, 0, strlen($domain)+7) == 'http://'.$domain)
				{
					return 'http://'.$mydomain.substr($url, strlen($domain)+7);
				} elseif (substr($url, 0, strlen($domain)+8) == 'https://'.$domain)
				{
					return 'https://'.$mydomain.substr($url, strlen($domain)+8);
				}
			}
	
		return $url;
	}
	
	
	/**
	 * Converts all HTTP URLs to HTTPS URLs when the site is accessed over SSL
	 */
	private function httpsizer()
	{
		// Make sure we're accessed over SSL (HTTPS)
		$uri = JURI::getInstance();
		$protocol = $uri->toString(array('scheme'));
		if($protocol != 'https://') return;
		
		$buffer = JResponse::getBody();
		$buffer = str_replace('http://','https://',$buffer);
		JResponse::setBody($buffer);
	}
}