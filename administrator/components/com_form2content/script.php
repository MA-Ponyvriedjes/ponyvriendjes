<?php
defined('_JEXEC') or die('Restricted acccess');

/**
 * Script file of Form2Content component
 */
class com_Form2ContentInstallerScript
{
    /**
     * method to install the component
     *
     * @return void
     */
    function install($parent) 
    {
    	$this->__createPath(JPATH_SITE . '/images/stories/com_form2content');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/templates');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/documents');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/import/archive');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/import/error');
    	$this->__createPath(JPATH_SITE . '/media/com_form2content/export');
		?>	
		<div align="left">
		<img src="../media/com_form2content/images/logo_opensource_design.gif" width="230" height="122" border="0">
		<h2><?php JText::_('COM_FORM2CONTENT_WELCOME_TO_F2C'); ?></h2>
		<p>&nbsp;</p>	
		<?php			
	 	if(!$this->__isGdiLibInstalled())
	 	{
			echo str_pad(JText::_('COM_FORM2CONTENT_ERROR_GDI_NOT_INSTALLED'), 100, '.');
			echo '<br/>';		
	 	}
		?>
		<p><strong><?php echo JText::_('COM_FORM2CONTENT_INSTALL_SAMPLE_DATA_QUESTION'); ?></strong></p>
		<p>
		<a href="index.php?option=com_form2content&amp;task=projects.installsamples"><?php echo JText::_('COM_FORM2CONTENT_YES_INSTALL_SAMPLE_DATA'); ?></a><br/>
		<a href="index.php?option=com_form2content"><?php echo JText::_('COM_FORM2CONTENT_NO_DO_NOT_INSTALL_SAMPLE_DATA'); ?></a><br/>
		</p>
		</div>
		<?php        	
        }
 
        /**
     * method to uninstall the component
     *
     * @return void
     */
    function uninstall($parent) 
    {
    }
 
        /**
     * method to update the component
     *
     * @return void
     */
        function update($parent) 
        {
        	// Update F2C Lite to F2C Pro
	    	$this->__createPath(JPATH_SITE . '/media/com_form2content/documents');
        	$this->__createPath(JPATH_SITE . '/media/com_form2content/import/archive');
	    	$this->__createPath(JPATH_SITE . '/media/com_form2content/import/error');
	    	$this->__createPath(JPATH_SITE . '/media/com_form2content/export');
        				
			$db =& JFactory::getDBO();
			
			// Add missing fields
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 4, \'Check box\' FROM #__f2c_fieldtype Where id = 4 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();			
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 7, \'IFrame\' FROM #__f2c_fieldtype Where id = 7 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 8, \'E-mail\' FROM #__f2c_fieldtype Where id = 8 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 9, \'Hyperlink\' FROM #__f2c_fieldtype Where id = 9 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 10, \'Multi select list (checkboxes)\' FROM #__f2c_fieldtype Where id = 10 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 11, \'Info Text\' FROM #__f2c_fieldtype Where id = 11 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 12, \'Date Picker\' FROM #__f2c_fieldtype Where id = 12 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 13, \'Display List\' FROM #__f2c_fieldtype Where id = 13 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 14, \'File Upload\' FROM #__f2c_fieldtype Where id = 14 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 15, \'Database Lookup\' FROM #__f2c_fieldtype Where id = 15 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();
		
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 16, \'Geo Coder\' FROM #__f2c_fieldtype Where id = 16 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();		
			
			$sql = 'INSERT INTO #__f2c_fieldtype (`id`, `description`) SELECT 17, \'Database Lookup (Multi select)\' FROM #__f2c_fieldtype Where id = 17 HAVING COUNT(*) = 0';
			$db->setQuery($sql);
			$db->query();        	
        }
 
        /**
     * method to run before an install/update/uninstall method
     *
     * @return void
     */
        function preflight($type, $parent) 
        {
        }
 
        /**
     * method to run after an install/update/uninstall method
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
    	if($type == 'install' || $type == 'update')
    	{
    		$this->__setImportExportDefaults();
    	}
    }
	
	function __isGdiLibInstalled()
    {
    	if((!function_exists('imagecreatetruecolor')) 	|| (!function_exists('imagecreatefromgif')) 	||
		   (!function_exists('imagecopyresampled'))		|| (!function_exists('imagegif')) 				||
		   (!function_exists('imagecreatefromgif')) 	|| (!function_exists('imagecreatefromjpeg')) 	||
		   (!function_exists('imagecreatefrompng'))		|| (!function_exists('imagecolorstotal'))		||
		   (!function_exists('imagecolortransparent'))	|| (!function_exists('imagefill'))				||
		   (!function_exists('imagetruecolortopalette'))|| (!function_exists('imagepalettecopy')))
		{		
			return false;
		}
		else
		{
			return true;
		}
    }
    
    function __createPath($path)
    {
		if(!JFolder::exists($path))
		{
			JFolder::create($path, 0775);
		}
    }
    
    function __setImportExportDefaults()
    {
		$db =& JFactory::getDBO();		
		$db->setQuery('SELECT extension_id FROM #__extensions WHERE name=\'com_form2content\'');
		
		$extensionId = $db->loadResult();

    	$configTable =  JTable::getInstance('extension');
		$configTable->load($extensionId);
		
		$params = new JRegistry($configTable->params);

    	if($params->get('import_dir') == '' && $params->get('export_dir') == '' && 
    	   $params->get('import_archive_dir') == '' && $params->get('import_error_dir') == '')
  		{
  			$params->set('import_dir', JPATH_SITE . '/media/com_form2content/import');
  			$params->set('export_dir', JPATH_SITE . '/media/com_form2content/export');
  			$params->set('import_archive_dir', JPATH_SITE . '/media/com_form2content/import/archive');
  			$params->set('import_error_dir', JPATH_SITE . '/media/com_form2content/import/error');
  		}

  		$configTable->params = $params->toString();
		$configTable->store();  		
    }
}
?>