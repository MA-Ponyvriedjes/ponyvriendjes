<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.'default.php';

/**
 * The Live Update controller class
 *
 */
class AdmintoolsControllerUpdate extends AdmintoolsControllerDefault
{
	function display()
	{
		parent::display();
	}

	function update()
	{
		// Make sure there are updates available
		$model =& $this->getModel('Update', 'AdmintoolsModel');
		$updates =& $model->getUpdates(false);
		if(!$updates->update_available)
		{
			$url = JURI::base().'index.php?option=com_admintools';
			$msg = JText::_('ATOOLS_ERR_UPDATE_NOUPDATES');
			$this->setRedirect($url, $msg, 'error');
			$this->redirect();
			return;
		}

		// Download the package
		$package = $updates->package_url.$updates->package_url_suffix;

		$updater = $this->getModel('Update','AdmintoolsModel');
		$config =& JFactory::getConfig();
		$target = $config->getValue('config.tmp_path').DS.'admintools_update.zip';
		$result = $updater->downloadPackage($package, $target);

		if($result === false)
		{
			$url = JURI::base().'index.php?option=com_admintools';
			$msg = JText::_('ATOOLS_ERR_UPDATE_CANTDOWNLOAD');
			$this->setRedirect($url, $msg, 'error');
			$this->redirect();
			return;
		}

		// Extract the package
		jimport('joomla.installer.helper');
		$package = $config->getValue('config.tmp_path').DS.$result;
		$result = JInstallerHelper::unpack($package);

		if($result === false)
		{
			$url = JURI::base().'index.php?option=com_admintools';
			$msg = JText::_('ATOOLS_ERR_UPDATE_CANTEXTRACT');
			$this->setRedirect($url, $msg, 'error');
			$this->redirect();
			return;
		}

		// Package extracted; run the installer
		$tempdir = $result['dir'];
		@ob_end_clean();
?>
<html>
<head>
</head>
<body>
<form action="index.php" method="post" name="frm" id="frm">
	<input type="hidden" name="option" value="com_installer" />
	<input type="hidden" name="task" value="doInstall" />
	<input type="hidden" name="installtype" value="folder" />
	<input type="hidden" name="install_directory" value="<?php echo htmlspecialchars($tempdir) ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken() ?>" value="1" />
</form>
<script type="text/javascript">
document.frm.submit();
</script>
</body>
<html>
<?php
		die();
	}
}