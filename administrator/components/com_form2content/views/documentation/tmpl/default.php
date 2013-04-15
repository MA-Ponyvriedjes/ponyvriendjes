<?php
defined('_JEXEC') or die( 'Restricted access' );
 
JHtml::_('behavior.mootools');

require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');
?>
<table width="100%" border="0">
<tr>
	<td width="99%" align="right" valign="top">
		<div align="center">
			<a href="http://www.opensourcedesign.nl" target="_blank">
			<img src="../media/com_form2content/images/logo_opensource_design.gif" alt="Logo OpenSource Design" width="230" height="122" border="0" />
			</a>
			<h2>F2C Documentation</h2>
			<a href="http://documentation.form2content.com" target="blank">documentation.form2content.com</a>
			<h2>F2C Forum</h2>
			<a href="http://forum.form2content.com" target="blank">forum.form2content.com</a>			
			<h2>F2C Blog</h2>
			<script id="articlewidget4cbab210b9114021184289" type="text/javascript" src="http://www.form2content.com/index.php?option=com_articlewidget&controller=server&task=init&view=server&layout=init&format=raw&id=articlewidget4cbab210b9114021184289"></script>
		</div>
	</td>
</tr>
</table>
<?php echo DisplayCredits(); ?>
