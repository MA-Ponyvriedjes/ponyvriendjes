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
			<p style="text-align:justify; width: 600px;"><?php echo JText::_('COM_FORM2CONTENT_ABOUT_LINE1'); ?></p>
			<p style="text-align:justify; width: 600px;"><?php echo JText::_('COM_FORM2CONTENT_ABOUT_LINE2'); ?></p>
			<p style="text-align:justify; width: 600px;"><?php echo JText::_('COM_FORM2CONTENT_ABOUT_LINE3'); ?> <a href="http://www.opensourcedesign.nl" target="_blank">www.opensourcedesign.nl</a></p>
		</div>
	</td>
</tr>
</table>
	
<?php echo DisplayCredits(); ?>
