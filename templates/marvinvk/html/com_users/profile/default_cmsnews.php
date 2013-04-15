<?php
/**
 * @version		$Id: default_custom.php 20211 2011-01-09 17:51:47Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */
defined('_JEXEC') or die;

?>

<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/marvinvk/js/com_users/cmsnews.js"></script>

<div class="com_users profile cmsnews column">
	<h2>CMS Nieuws</h2>
	<div class="iframe-container">
		<iframe src="http://www.marvinvk.nl/cms/html/cms-news.php" width="100%" height="300px" frameborder="0" id="cmsnewsframe"></iframe>
	</div>
</div>

