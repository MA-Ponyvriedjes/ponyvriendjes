<?php
/**
 * NoNumber Framework Helper File: Parameters
 *
 * @package			NoNumber Framework
 * @version			12.4.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Assignments
 * $assignment = no / include / exclude / none
 */

class NNePparameters extends NNParameters
{
	// for backward compatibility
}

class NNParameters
{
	public static $instance = null;

	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new NNFrameworkParameters;
		}

		return self::$instance;
	}

	public static function getParameters()
	{
		// backward compatibility
		return self::getInstance();
	}
}

class NNFrameworkParameters
{
	var $_version = '12.4.4';

	var $_xml = array();

	function getParams($params, $path = '')
	{
		$xml = $this->_getXML($path);

		if (!$params) {
			return (object) $xml;
		}

		if (!is_object($params)) {
			$registry = new JRegistry();
			$registry->loadString($params);
			$params = $registry->toObject();
		} elseif (method_exists($params, 'toObject')) {
			$params = $params->toObject();
		}

		if (!$params) {
			return (object) $xml;
		}

		if (!empty($xml)) {
			foreach ($xml as $key => $val) {
				if (!isset($params->$key) || $params->$key == '') {
					$params->$key = $val;
				}
			}
		}

		return $params;
	}

	function getComponentParams($name, $params = '')
	{
		$name = 'com_'.preg_replace('#^com_#', '', $name);

		if (empty($params)) {
			$params = JComponentHelper::getParams($name);
		}
		return $this->getParams($params, JPATH_ADMINISTRATOR.'/components/'.$name.'/config.xml');
	}

	function getModuleParams($name, $admin = 1, $params = '')
	{
		$name = 'mod_'.preg_replace('#^mod_#', '', $name);

		if (empty($params)) {
			$params = null;
		}

		return $this->getParams($params, ($admin ? JPATH_ADMINISTRATOR : JPATH_SITE ).'/modules/'.$name.'/'.$name.'.xml');
	}

	function getPluginParams($name, $type = 'system', $params = '')
	{
		if (empty($params)) {
			jimport('joomla.plugin.plugin');
			$plugin = JPluginHelper::getPlugin($type, $name);
			$params = $plugin->params;
		}
		return $this->getParams($params, JPATH_PLUGINS.'/'.$type.'/'.$name.'/'.$name.'.xml');
	}

	// Deprecated: use getPluginParams
	function getPluginParamValues($name, $type = 'system')
	{
		return $this->getPluginParams($name, $type);
	}

	function _getXML($path)
	{
		if (!isset($this->_xml[$path])) {
			$params = $this->_loadXML($path);
			foreach ($params as $key => $val) {
				if ($key['0'] == '@') {
					unset($params[$key]);
				}
			}
			$this->_xml[$path] = $params;
		}

		return $this->_xml[$path];
	}

	function _loadXML($path)
	{
		$xml = array();
		if ($path) {
			$xmlparser = JFactory::getXMLParser('Simple');
			if ($xmlparser->loadFile($path)) {
				$xml = $this->_getParamValues($xmlparser);
			}
		}

		return $xml;
	}

	function _getParamValues(&$xml, $keys = array())
	{
		$params = array();
		$fieldsets = $this->_getFieldSets($xml);

		foreach ($fieldsets as $fieldset) {
			if ($fieldset->name() == 'fieldset') {
				foreach ($fieldset->children() as $field) {
					$key = $field->attributes('name');
					if (!empty($key) && $key['0'] != '@' || $field->attributes('type') != 'spacer') {
						if (empty($keys) || in_array($key, $keys)) {
							$val = $xml->get($key);
							if (!is_array($val) && !strlen($val)) {
								$val = $field->attributes('default');
								if ($field->attributes('type') == 'textarea') {
									$val = str_replace('<br />', "\n", $val);
								}
							}
							$params[$key] = $val;
						}
					}
				}
			}
		}
		return $params;
	}

	function _getFieldSets(&$xml)
	{
		if (isset($xml->document)) {
			$children = $xml->document->children();
			return $this->_getFieldSets($children);
		} else if (is_array($xml) && isset($xml['0']) && is_object($xml['0'])) {
			if (isset($xml['0']->_name) && $xml['0']->_name == 'fieldset') {
				return $xml;
			} else if (isset($xml['0']->_children)) {
				foreach ($xml as $child) {
					if (isset($child->_name) && in_array($child->_name, array('config', 'fields'))) {
						$children = $child->children();
						return $this->_getFieldSets($children);
					}
				}
			}
		}
		return array();
	}

	function getObjectFromXML(&$xml)
	{
		if (!is_array($xml)) {
			$xml = array($xml);
		}
		$class = new stdClass();
		foreach ($xml as $item) {
			$key = $this->_getKeyFromXML($item);
			$val = $this->_getValFromXML($item);

			if (isset($class->$key)) {
				if (!is_array($class->$key)) {
					$class->$key = array($class->$key);
				}
				$class->{$key}[] = $val;
			}
			$class->$key = $val;
		}
		return $class;
	}

	function _getKeyFromXML(&$xml)
	{
		if (!empty($xml->_attributes) && isset($xml->_attributes['name'])) {
			$key = $xml->_attributes['name'];
		} else {
			$key = $xml->_name;
		}
		return $key;
	}

	function _getValFromXML(&$xml)
	{
		if (!empty($xml->_attributes) && isset($xml->_attributes['value'])) {
			$val = $xml->_attributes['value'];
		} else if (empty($xml->_children)) {
			$val = $xml->_data;
		} else {
			$val = new stdClass();
			foreach ($xml->_children as $child) {
				$k = $this->_getKeyFromXML($child);
				$v = $this->_getValFromXML($child);

				if (isset($val->$k)) {
					if (!is_array($val->$k)) {
						$val->$k = array($val->$k);
					}
					$val->{$k}[] = $v;
				} else {
					$val->$k = $v;
				}
			}
		}
		return $val;
	}
}
