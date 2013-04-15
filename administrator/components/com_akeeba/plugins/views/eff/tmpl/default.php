<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 1.3
 */

defined('_JEXEC') or die('Restricted access');
if(!class_exists('AkeebaHelperEscape')) JLoader::import('helpers.escape', JPATH_COMPONENT_ADMINISTRATOR);
?>
<div id="akeeba-container" style="width:100%">


<div id="dialog" title="<?php echo JText::_('CONFIG_UI_AJAXERRORDLG_TITLE') ?>"></div>

<div id="browser" title="<?php echo JText::_('CONFIG_UI_BROWSER_TITLE') ?>"></div>

<fieldset>
	<legend><?php echo JText::_('CPANEL_PROFILE_TITLE'); ?>: #<?php echo $this->profileid; ?></legend>
	<?php echo $this->profilename; ?>
</fieldset>

<fieldset>
	<div id="ak_list_container">
		<table id="ak_list_table" class="adminlist">
			<thead>
				<tr>
					<!-- Delete -->
					<td width="20px">&nbsp;</td>
					<!-- Edit -->
					<td width="50px">&nbsp;</td>
					<!-- Directory path -->
					<td><?php echo JText::_('EFF_LABEL_DIRECTORY') ?></td>
				</tr>
			</thead>
			<tbody id="ak_list_contents">
			</tbody>
		</table>
	</div>
</fieldset>

<script type="text/javascript">

akeeba.jQuery(document).ready(function($){
	// Set the AJAX proxy URL
	akeeba_ajax_url = '<?php echo AkeebaHelperEscape::escapeJS(JURI::base().'index.php?option=com_akeeba&view=eff&task=ajax') ?>';
	// Set the media root
	akeeba_ui_theme_root = '<?php echo $this->mediadir ?>';
	// Create the error dialog
	$("#dialog").dialog({
		autoOpen: false,
		closeOnEscape: false,
		height: 200,
		width: 300,
		hide: 'slide',
		modal: true,
		position: 'center',
		show: 'slide'
	});
	// Create the browser dialog
	$("#browser").dialog({
		autoOpen: false,
		closeOnEscape: false,
		height: 400,
		width: 640,
		hide: 'slide',
		modal: true,
		position: 'center',
		show: 'slide'
	});
	// Create an AJAX error trap
	akeeba_error_callback = function( message ) {
		var dialog_element = $("#dialog");
		dialog_element.html(''); // Clear the dialog's contents
		dialog_element.dialog('option', 'title', '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_AJAXERRORDLG_TITLE')) ?>');
		$(document.createElement('p')).html('<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_AJAXERRORDLG_TEXT')) ?>').appendTo(dialog_element);
		$(document.createElement('pre')).html( message ).appendTo(dialog_element);
		dialog_element.dialog('open');
	};
	// Push translations
	akeeba_translations['UI-ROOT'] = '<?php echo AkeebaHelperEscape::escapeJS(JText::_('FILTERS_LABEL_UIROOT')) ?>';
	akeeba_translations['UI-ERROR-FILTER'] = '<?php echo AkeebaHelperEscape::escapeJS(JText::_('FILTERS_LABEL_UIERRORFILTER')) ?>';

	// Create the browser hook
	akeeba_browser_hook = function( folder, element )
	{
		var dialog_element = $("#browser");
		dialog_element.html(''); // Clear the dialog's contents
		dialog_element.removeClass('ui-state-error');
		dialog_element.dialog('option', 'title', '<?php echo AkeebaHelperEscape::escapeJS(JText::_('CONFIG_UI_BROWSER_TITLE')) ?>');

		// URL to load the browser
		var browserSrc = '<?php echo AkeebaHelperEscape::escapeJS(JURI::base().'index.php?option=com_akeeba&view=browser&processfolder=1&folder=') ?>';
		browserSrc = browserSrc + encodeURIComponent(folder);

		// IFrame holding the browser
		var akeeba_browser_iframe = $(document.createElement('iframe')).attr({
			'id':			'akeeba_browser_iframe',
			width:			'100%',
			height:			'98%',
			marginWidth		: 0,
			marginHeight	: 0,
			frameBorder		: 0,
			scrolling		: 'auto',
			src				: browserSrc
		});
		akeeba_browser_iframe.appendTo( dialog_element );

		// Close dialog callback (user confirmed the new folder)
		akeeba_browser_callback = function( myFolder ) {
			$(element).val( myFolder );
			dialog_element.dialog('close');
		};

		dialog_element.dialog('open');
	};

	// Bootstrap the page display
	var data = JSON.parse('<?php echo AkeebaHelperEscape::escapeJS($this->json); ?>');
	eff_render(data);
});
</script>

</div>