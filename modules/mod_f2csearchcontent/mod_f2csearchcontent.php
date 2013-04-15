<?php defined('_JEXEC') or die('Restricted access');


//require_once('components'.DS.'com_f2csearch'.DS.'models'.DS.'list.php');
require_once (dirname(__FILE__).DS.'helper.php');


$list = ModF2CHelper::getList($params->toArray());

$paramsArray = $params->toArray();

$layout = $paramsArray['template'];

$path = JModuleHelper::getLayoutPath('mod_f2csearchcontent', $layout);

if (file_exists($path))
{
 require($path);
}