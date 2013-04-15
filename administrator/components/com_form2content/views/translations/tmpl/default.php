<?php 
defined('_JEXEC') or die('Restricted acccess');

JHtml::_('behavior.mootools');

require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');

$f2cConfig	= F2cFactory::getConfig();
$dateFormat = str_replace('%', '', $f2cConfig->get('date_format'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_form2content&view=translations');?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="translations_filter_search" id="translations.filter_search" value="<?php echo $this->escape($this->state->get('translations.filter.search')); ?>" title="<?php echo JText::_('COM_FORM2CONTENT_TRANSLATION_FILTER_SEARCH_DESC'); ?>" />

			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('translations.filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-search fltrt">
			<select name="translations_filter_translationstate" id="translations.filter_translationstate" class="inputbox" onchange="this.form.submit()">
				<option value="">- <?php echo JText::_('COM_FORM2CONTENT_SELECT_TRANSLATION_STATE');?> -</option>
				<?php echo JHtml::_('select.options', $this->translationStateOptions, 'value', 'text', $this->state->get('translations.filter.translationstate'));?>
			</select>
			<select name="translations_filter_language" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', false, true), 'value', 'text', $this->state->get('translations.filter.language'));?>
			</select>	
			<select name="translations_filter_contenttype_id" class="inputbox" onchange="this.form.submit()">
				<option value="-1">- <?php echo JText::_('COM_FORM2CONTENT_SELECT_CONTENTTYPE');?> -</option>
				<?php echo JHtml::_('select.options', $this->contentTypes, 'value', 'text', $this->state->get('translations.filter.contenttype_id'));?>
			</select>								
		</div>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
	<thead>
		<tr>
			<th width="1%">
				<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
			</th>		
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_DEFAULT_FIELD_NAME'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_PROJECT'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_LANGUAGE'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_TRANSLATION'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_DATE_MODIFIED'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_MODIFIED_BY'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="9">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php
		foreach ($this->items as $i => $item)
		{
	  		if($item->translation_id)
	  		{
	  			$link = 'index.php?option=com_form2content&task=translation.edit&id='.$item->translation_id.'&reference_id='.$item->fieldid;
	  		}
	  		else
	  		{
	  			$link = 'index.php?option=com_form2content&task=translation.add&reference_id='.$item->fieldid.'&lang_code='.urlencode($item->lang_code);
	  		}
		?>
		<tr class="row<?php echo $i % 2; ?>">
			<td class="center">
				<?php echo JHtml::_('grid.id', $i, $item->translation_id ? $item->translation_id : 'R'.$item->fieldid.'L'.$item->lang_code); ?>
			</td>
			<td>
				<a href="<?php echo JRoute::_($link);?>">
					<?php echo $this->escape($item->fieldtitle . ' (' . $item->fieldname . ')'); ?>
				</a>
			</td>
			<td>
				<?php echo $item->projecttitle; ?>
			</td>
			<td>
				<?php echo $item->lang_code; ?>
			</td>
			<td>
				<?php echo $item->title_translation; ?>
			</td>
			<td>
				<?php
				if($item->modified)
				{
					echo JHTML::_('date',$item->modified, $dateFormat);
				}
				?>
			</td>
			<td>
				<?php echo $item->modifier; ?>
			</td>
		</tr>
		<?php
		}
		?>
	</tbody>
	</table>
	<?php echo DisplayCredits(); ?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
