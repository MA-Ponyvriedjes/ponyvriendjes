<?php
/**
 * @package AkeebaBackup
 * @subpackage OneClickAction
 * @copyright Copyright (c)2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 3.3
 */

defined('_JEXEC') or die();

// PHP version check
if(defined('PHP_VERSION')) {
	$version = PHP_VERSION;
} elseif(function_exists('phpversion')) {
	$version = phpversion();
} else {
	$version = '5.0.0'; // all bets are off!
}
if(!version_compare($version, '5.0.0', '>=')) return;

jimport('joomla.application.plugin');

class plgSystemAkeebaupdatecheck extends JPlugin
{
	public function onAfterRender()
	{
		// Check for PHP4
		if(defined('PHP_VERSION')) {
			$version = PHP_VERSION;
		} elseif(function_exists('phpversion')) {
			$version = phpversion();
		} else {
			// No version info. I'll lie and hope for the best.
			$version = '5.0.0';
		}

		// Old PHP version detected. EJECT! EJECT! EJECT!
		if(!version_compare($version, '5.2.7', '>=')) return;
		
		// Make sure Akeeba Backup is installed
		if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_akeeba')) {
			return;
		}
		
		jimport('joomla.filesystem.file');
		$db = JFactory::getDBO();
		
		// If another extension using Live Update is already loaded, or if
		// another update check plugin has already run, bail out.
		if(class_exists('LiveUpdate')) return;
		
		// Is Akeeba Backup with Live Update installed?
		$liveUpdateFile = JPATH_ADMINISTRATOR.'/components/com_akeeba/liveupdate/liveupdate.php';
		if(!JFile::exists($liveUpdateFile)) return;
		
		// Is Akeeba Backup enabled?
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$db->setQuery('SELECT `enabled` FROM `#__extensions` WHERE `element` = "com_akeeba" AND `type` = "component"');
			$enabled = $db->loadResult();
		} else {
			$db->setQuery('SELECT `enabled` FROM `#__components` WHERE `link` = "option=com_akeeba"');
			$enabled = $db->loadResult();
		}
		if(!$enabled) return;
		
		// Is the One Click Action plugin enabled?
		$app = JFactory::getApplication();
		$jResponse = $app->triggerEvent('onOneClickActionEnabled');
		if(empty($jResponse)) return;
		$status = false;
		foreach($jResponse as $response)
		{
			$status = $status || $response;
		}
		if(!$status) return;
		
		// Do we have to run (at most once per 3 hours)?
		jimport('joomla.html.parameter');
		jimport('joomla.application.component.helper');
		$component =& JComponentHelper::getComponent('com_akeeba');
		if(!($component->params instanceof JRegistry)) {
			$params = new JParameter($component->params);
		} else {
			$params = $component->params;
		}
		$last = $params->getValue('plg_akeebaupdatecheck', 0);
		$now = time();
		if(abs($now-$last) < 10800) return;
		
		// Use a 20% chance of running; this allows multiple concurrent page
		// requests to not cause double update emails being sent out.
		$random = rand(1, 5);
		if($random != 3) return;
		
		// Update last run status
		$params->setValue('plg_akeebaupdatecheck', $now);
		$db = JFactory::getDBO();
		if( version_compare(JVERSION,'1.6.0','ge') ) {
			// Joomla! 1.6
			$data = $params->toString('JSON');
			$db->setQuery('UPDATE `#__extensions` SET `params` = '.$db->Quote($data).' WHERE '.
				"`element` = ".$db->quote('com_akeeba')." AND `type` = 'component'");
		} else {
			// Joomla! 1.5
			$data = $params->toString('INI');
			$db->setQuery('UPDATE `#__components` SET `params` = '.$db->Quote($data).' WHERE '.
				"`option` = ".$db->quote('com_akeeba')." AND `parent` = 0 AND `menuid` = 0");
		}
		
		// If a DB error occurs, return null
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			try {
				$db->query();
			} catch (Exception $e) {
				return;
			}
		} else {
			$db->query();
			if($db->getErrorNum()) return;
		}
		
		// Load the Live Update code
		require_once $liveUpdateFile;
		if(!defined('AKEEBAENGINE')) {
			define('AKEEBAENGINE', 1); // Required for accessing Akeeba Engine's factory class
			define('AKEEBAPLATFORM', 'joomla15'); // So that platform-specific stuff can get done!
		}
		include_once JPATH_ADMINISTRATOR.'/components/com_akeeba/version.php';
		require_once JPATH_ADMINISTRATOR.'/components/com_akeeba/akeeba/factory.php';
		
		// If this is not the Professional release, bail out. So far I have only
		// received complaints about this feature from users of the Core release
		// who never bothered to read the documentation. FINE! If you are bitching
		// about it, you don't get this feature (unless you are a developer who can
		// come here and edit the code). Fair enough.
		if(!defined('AKEEBA_PRO')) return;
		if(!AKEEBA_PRO) return;
		
		// OK, cool. Let's run Live Update fetch, OK?
		$updateInfo = LiveUpdate::getUpdateInformation();
		if(!$updateInfo->hasUpdates) return; // No point continuing if there are no udpates available
		if(!$updateInfo->supported) return; // No point continuing if Live Update is not supported
		if($updateInfo->stuck) return; // No point continuing if Live Update is stuck
		if(empty($updateInfo->version)) return; // No point continuing if the version is empty (something went wrong)
		if(empty($updateInfo->stability)) return; // Ditto
		
		// If we're here, we have updates. Let's create an OTP.
		$uri = JURI::base();
		$uri = rtrim($uri,'/');
		$uri .= (substr($uri,-13) != 'administrator') ? '/administrator/' : '/';
		$link = 'index.php?option=com_akeeba&view=liveupdate&task=startupdate';
		
		$superAdmins = $this->_getSuperAdministrators();
		if(empty($superAdmins)) return;
		
		$this->_loadLanguage();
		$email_subject	= JText::_('PLG_AKEEBAUPDATECHECK_EMAIL_SUBJECT');
		$email_body		= JText::_('PLG_AKEEBAUPDATECHECK_EMAIL_BODY');
		
		$jconfig = JFactory::getConfig();
		$substitutions = array(
			'[VERSION]'			=> $updateInfo->version,
			'[DATE]'			=> $updateInfo->date,
			'[STABILITY]'		=> ucfirst($updateInfo->stability),
			'[SITENAME]'		=> $jconfig->getValue('config.sitename')
		);
		
		foreach($superAdmins as $sa)
		{
			$otp = plgSystemOneclickaction::addAction($sa->id, $link);
			if(is_null($otp)) {
				// If the OTP is null, a database error occurred
				return;
			} elseif(empty($otp)) {
				// If the OTP is empty, an OTP for the same action was already
				// created and it hasn't expired.
				continue;
			}
			$emaillink = $uri.'index.php?oneclickaction='.$otp;
			
			$substitutions['[LINK]'] = $emaillink."\n\n".JText::_('PLG_AKEEBAUPDATECHECK_EMAIL_IMPORTANTNOTES');
			foreach($substitutions as $k => $v) {
				$email_subject = str_replace($k, $v, $email_subject);
				$email_body = str_replace($k, $v, $email_body);
			}
			
			$mailer = JFactory::getMailer();
			$mailer->setSender(array( $jconfig->getvalue('config.mailfrom'), $jconfig->getvalue('config.fromname') ));
			$mailer->addRecipient($sa->email);
			$mailer->setSubject($email_subject);
			$mailer->setBody($email_body);
			$mailer->Send();
		}
	}
	
	private function _getSuperAdministrators()
	{
		$db = JFactory::getDBO();
		if(version_compare(JVERSION, '1.6', 'ge')) {
			$db->setQuery('SELECT u.id, u.email FROM #__user_usergroup_map AS g INNER JOIN #__users AS u ON(g.user_id = u.id) WHERE g.group_id = 8 AND u.block = 0 AND u.sendEmail = 1');
		} else {
			$db->setQuery('SELECT `id`, `email` FROM `#__users` WHERE `usertype` = "Super Administrator" AND `block` = 0 AND `sendEmail` = 1');
		}
		
		return $db->loadObjectList();
	}
	
	private function _loadLanguage()
	{
		$jlang =& JFactory::getLanguage();
		$jlang->load('plg_system_akeebaupdatecheck', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('plg_system_akeebaupdatecheck', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('plg_system_akeebaupdatecheck', JPATH_ADMINISTRATOR, null, true);		
	}
}