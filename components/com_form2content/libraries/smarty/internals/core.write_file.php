<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * write out a file to disk
 *
 * @param string $filename
 * @param string $contents
 * @param boolean $create_dirs
 * @return boolean
 */
function smarty_core_write_file($params, &$smarty)
{
    $_dirname = dirname($params['filename']);

    if ($params['create_dirs']) 
    {
        $_params = array('dir' => $_dirname);
        require_once(SMARTY_CORE_DIR . 'core.create_dir_structure.php');
        smarty_core_create_dir_structure($_params, $smarty);
    }

	/*
	 * Code modification to make the write function work with the Joomla system.
	 * The default Smarty code would fail when the FTP layer was enabled.
	 */
	jimport('joomla.user.helper');
	jimport('joomla.filesystem.file');
	
	$_tmp_file = $_dirname.DS.md5(JUserHelper::genRandomPassword(16));
	
	if(!JFile::write($_tmp_file, $params['contents']))
	{
		JError::raiseError('Smarty template engine could not write file \''.$_tmp_file.'\'');
	}

	if(JFile::exists($params['filename']))
	{
		if(!JFile::delete($params['filename']))
		{
			JError::raiseError('Smarty template engine could not delete file \''.$params['filename'].'\'');
		}
	}

	if(!JFile::move($_tmp_file, $params['filename']))
	{
		JError::raiseError('Smarty template engine could not move file \''.$_tmp_file.'\' to file \''.$params['filename'].'\'');
	}

	/*
    if (!($fd = @fopen($_tmp_file, 'wb'))) {
        $_tmp_file = $_dirname . DIRECTORY_SEPARATOR . uniqid('wrt');
        if (!($fd = @fopen($_tmp_file, 'wb'))) {
            $smarty->trigger_error("problem writing temporary file '$_tmp_file'");
            return false;
        }
    }
	
	/*
    if (!($fd = @fopen($_tmp_file, 'wb'))) {
        $_tmp_file = $_dirname . DIRECTORY_SEPARATOR . uniqid('wrt');
        if (!($fd = @fopen($_tmp_file, 'wb'))) {
            $smarty->trigger_error("problem writing temporary file '$_tmp_file'");
            return false;
        }
    }

    //fwrite($fd, $params['contents']);
    //fclose($fd);
	
    if (DIRECTORY_SEPARATOR == '\\' || !@rename($_tmp_file, $params['filename'])) {
        // On platforms and filesystems that cannot overwrite with rename() 
        // delete the file before renaming it -- because windows always suffers
        // this, it is short-circuited to avoid the initial rename() attempt
        @unlink($params['filename']);
        @rename($_tmp_file, $params['filename']);
    }
    
    @chmod($params['filename'], $smarty->_file_perms);
	*/
	
    return true;
}

/* vim: set expandtab: */

?>