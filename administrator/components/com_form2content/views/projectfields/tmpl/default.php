<?php 
defined('_JEXEC') or die('Restricted acccess');

JHtml::_('behavior.mootools');

require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');
require_once(JPATH_COMPONENT_SITE.DS.'shared.form2content.php');
require_once(JPATH_COMPONENT_SITE.DS.'class.form2content.php');

$saveOrder 	= true;
$ordering 	= true;
$listDirn	= 'asc';
?>
<form action="<?php echo JRoute::_('index.php?option=com_form2content&view=projectfields&projectid='.(int)$this->contentTypeId);?>" method="post" name="adminForm" id="adminForm">
<fieldset id="filter-bar">
	<div class="filter-search fltlft">
		<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
		<input type="text" name="projectfields_filter_search" id="projectfields.filter_search" value="<?php echo $this->escape($this->state->get('projectfields.filter.search')); ?>" title="<?php echo JText::_('COM_FORM2CONTENT_PROJECTFIELDS_FILTER_SEARCH_DESC'); ?>" />
		<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
		<button type="button" onclick="document.id('contenttypes.filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>
</fieldset>
<div class="clr"></div>

<table class="adminlist">
<thead>
	<tr>
		<th width="5">
			<?php echo JText::_('COM_FORM2CONTENT_NUM'); ?>
		</th>
		<th width="1%">
			<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
		</th>		
		<th align="left">
			<?php echo JText::_('COM_FORM2CONTENT_FIELDNAME'); ?>
		</th>
		<th align="left">
			<?php echo JText::_('COM_FORM2CONTENT_FIELD_CAPTION'); ?>
		</th>
		<th align="left">
			<?php echo JText::_('COM_FORM2CONTENT_DESCRIPTION'); ?>
		</th>
		<th align="left">
			<?php echo JText::_('COM_FORM2CONTENT_FIELDTYPE'); ?>
		</th>
		<th align="left">
			<?php echo JText::_('COM_FORM2CONTENT_FRONT_END_VISIBLE'); ?>
		</th>
		<th align="left">
			<?php echo JText::_('COM_FORM2CONTENT_REQUIRED_FIELD'); ?>
		</th>
		<th width="10%">
			<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'projectfields.saveorder'); ?>
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
		$frontendvisible = '<img src="'.JURI::root().'/media/com_form2content/images/' . (($item->frontvisible) ? 'tick.png' : 'publish_r.png') . '">';		
	
		if($item->settings)
		{
			$registry = new JRegistry;
			$registry->loadString($item->settings);
			$requiredField = '<img src="'.JURI::root().'/media/com_form2content/images/' . (($registry->get('requiredfield')) ? 'tick.png' : 'publish_r.png') . '">';	
		}
		else
		{
			$requiredField = '<img src="'.JURI::root().'/media/com_form2content/images/publish_r.png">';
		}
		?>
		<tr class="row<?php echo $i % 2; ?>">
			<td>
				<?php echo $item->id; ?>
			</td>
			<td class="center">
				<?php echo JHtml::_('grid.id', $i, $item->id); ?>
			</td>
			<td>
				<a href="<?php echo JRoute::_('index.php?option=com_form2content&task=projectfield.edit&id='.$item->id);?>">
					<?php echo $this->escape($item->fieldname); ?>
				</a>
			</td>		
			<td>
				<?php
				if($item->fieldtypeid == F2C_FIELDTYPE_INFOTEXT)
				{
					echo JText::_('COM_FORM2CONTENT_N_A');
				}
				else
				{
					echo $item->title;
				} 
				?>
			</td>
			<td>
				<?php echo $this->escape($item->description); ?>
			</td>
			<td>
				<?php echo $this->escape($item->fieldtype); ?>
			</td>
			<td align="center">
				<?php echo $frontendvisible; ?>
			</td>
			<td align="center">
				<?php echo $requiredField; ?>
			</td>			
			<td class="order">
				<?php if ($saveOrder) :?>
					<?php if ($listDirn == 'asc') : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, true, 'projectfields.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'projectfields.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
					<?php elseif ($listDirn == 'desc') : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, true, 'projectfields.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'projectfields.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
					<?php endif; ?>
				<?php endif; ?>
				<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
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
	<input type="hidden" name="projectid" value="<?php echo $this->contentTypeId ?>" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>