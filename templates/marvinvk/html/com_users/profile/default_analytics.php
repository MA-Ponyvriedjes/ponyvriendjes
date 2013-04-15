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

<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/marvinvk/js/libs/oocharts-jquery.js"></script>
<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/marvinvk/js/com_users/analytics.js"></script>

<div class="com_users profile analytics column">
	<h2>Analytics</h2>

	<div class="total-visits">
		<h3>Totaal aantal bezoekers</h3> 
		<span class="visits"></span>
	</div>

	<div class="date">
		<h3>In de periode</h3> 
		<span class="startdate"></span> - <span class="enddate"></span>
	</div>

</div>





