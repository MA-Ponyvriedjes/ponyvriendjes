<?php
/**
 * @version   $Id: categories.php 2157 2011-11-18 17:51:49Z silianacom-svn $
 * @package   Subscriptions
 * @copyright Copyright (C) 2010 - Anything Digital. All rights reserved.
 * @copyright Copyright (C) 2010 - Yannick Gaultier. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * Subscriptions is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Security check to ensure this file is being included by a parent file.
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');

class Sh404sefHelperCategories {


  // class cache to hold "uncategorized" category details per extension
  public static $uncategorizedCat = array();

  private static $_sh404sefContentCat = null;


  /**
   *
   * Get details of the "Uncategorized" category for a given extension,
   * storing the result in a cache variable
   *
   * @param string $extension full name of extension, ie "com_content"
   */
  public static function getUncategorizedCat( $extension = 'com_content') {

    // if not already in cache
    if(!isset(self::$uncategorizedCat[$extension])) {

      try {
        // read details from database
        self::$uncategorizedCat[$extension] = Sh404sefHelperDb::selectObject( '#__categories', '*', 'parent_id > 0 and extension = ? and path = ? and level = ?', array( $extension, 'uncategorised', 1));

      } catch( Sh404sefExceptionDefault $e) {

        self::$uncategorizedCat[$extension] = null;

      }

    }

    return self::$uncategorizedCat[$extension];
  }

  /**
   *
   * Get details of the "Uncategorized" category for a given extension,
   * storing the result in a cache variable
   *
   * @param string $extension full name of extension, ie "com_content"
   */
  public static function getSh404sefContentCat() {

    // if not already in cache
    if(is_null( self::$_sh404sefContentCat)) {

      try {
        // read details from database
        self::$_sh404sefContentCat = Sh404sefHelperDb::selectObject( '#__categories', '*', array('parent_id' => 1, 'extension' => 'com_content', 'path' => 'sh404sef-custom-content', 'level' => 1));

      } catch( Sh404sefExceptionDefault $e) {

        self::$_sh404sefContentCat = null;

      }

    }

    return self::$_sh404sefContentCat;
  }

}

