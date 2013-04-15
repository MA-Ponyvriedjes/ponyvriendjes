<?php
// No direct access
defined('JPATH_BASE') or die;

abstract class F2cFactory
{
	public static $config = null;
	
	/**
	 * Get a configuration object
	 *
	 * Returns the global {@link JRegistry} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return JRegistry object
	 */
	public static function getConfig()
	{
		if (!self::$config) 
		{
			self::$config = self::_createConfig();
		}

		return self::$config;
	}
	
	private function _createConfig()
	{
		$config 		= new JRegistry();		
		$paramvalues 	= JComponentHelper::getParams('com_form2content');
		
		$config->loadString($paramvalues);

		// Some hard-coded read-only settings
		$config->set('f2c_pro', true);
		$config->set('template_path',JPATH_SITE.DS.'media'.DS.'com_form2content'.DS.'templates'.DS);
		
		// Set some defaults
		if($config->get('generate_sample_template') == '')
		{
			$config->set('generate_sample_template', '1');
		}

		if($config->get('default_thumbnail_width') == '')
		{
			$config->set('default_thumbnail_width', '100');
		}

		if($config->get('default_thumbnail_height') == '')
		{
			$config->set('default_thumbnail_height', '100');
		}

		if($config->get('jpeg_quality') == '')
		{
			$config->set('jpeg_quality', '75');
		}
		
		if($config->get('date_format') == '')
		{
			$config->set('date_format', '%d-%m-%Y');
		}
		
		if($config->get('autosync_article_order') == '')
		{
			$config->set('autosync_article_order', '1');
		}

		if($config->get('edit_items_level') == '')
		{
			$config->set('edit_items_level', '0');
		}
		
		if($config->get('front_end_publish') == '')
		{
			$config->set('front_end_publish', '1');
		}
		
		return $config;
	}
}
?>