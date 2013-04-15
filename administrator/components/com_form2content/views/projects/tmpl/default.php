<?php 
defined('_JEXEC') or die('Restricted acccess');

JHtml::_('behavior.mootools');
JHTML::stylesheet( 'default.css', 'administrator/components/com_form2content/media/css/');

require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');

$dateFormat = str_replace('%', '', $this->f2cConfig->get('date_format'));
?>
<script type="text/javascript">
Joomla.submitbutton = function(task) 
{
	if (task == 'projects.syncorder') 
	{
		if(!confirm('<?php echo JText::_('COM_FORM2CONTENT_SYNC_ORDER_CONFIRMATION', true); ?>'))
		{
			return false;
		}
	}
	else if(task == 'project.upload')
	{
		var upload = document.getElementById('upload');
		
		if(!upload.value)
		{
			alert('<?php echo JText::_('COM_FORM2CONTENT_ERROR_CONTENTTYPE_FILE_UPLOAD_EMPTY'); ?>');
			return false;
		}				
	}
	
	Joomla.submitform(task);
	return true;	
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_form2content&view=projects');?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		<fieldset id="filter-bar" style="height: 60px;">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="contenttypes_filter_search" id="contenttypes.filter_search" value="<?php echo $this->escape($this->state->get('contenttypes.filter.search')); ?>" title="<?php echo JText::_('COM_FORM2CONTENT_CONTENTTYPE_FILTER_SEARCH_DESC'); ?>" />
			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('contenttypes.filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-search fltrt">
			<fieldset class="actions">
				<legend><?php echo JText::_('COM_FORM2CONTENT_IMPORT_CONTENTTYPE'); ?> [ <?php echo JText::_('COM_FORM2CONTENT_MAX_SIZE'); ?> =&nbsp;<?php echo ini_get('post_max_size'); ?> ]</legend>
		        <input class="inputbox" type="file" name="upload" id="upload" size="63" />
		    </fieldset>
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
				<?php echo JText::_('COM_FORM2CONTENT_TITLE'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_FIELDS'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_CREATED_BY'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_DATE_CREATED'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_DATE_MODIFIED'); ?>
			</th>
			<th align="left">
				<?php echo JText::_('COM_FORM2CONTENT_PUBLISHED'); ?>
			</th>
			<th align="center" width="1%">
				<?php echo JText::_('COM_FORM2CONTENT_EXPORT'); ?>
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
		<?php foreach ($this->items as $i => $item) :
		?>
		<tr class="row<?php echo $i % 2; ?>">
			<td>
				<?php echo $item->id; ?>
			</td>
			<td class="center">
				<?php echo JHtml::_('grid.id', $i, $item->id); ?>
			</td>
			<td>
				<a href="<?php echo JRoute::_('index.php?option=com_form2content&task=project.edit&id='.$item->id);?>">
					<?php echo $this->escape($item->title); ?>
				</a>
			</td>		
			<td align="center">
				<a href="<?php echo JRoute::_('index.php?option=com_form2content&view=projectfields&projectid='. $item->id); ?>">
					<img src="../media/com_form2content/images/contenttype_fields.png" width="16" height="16" alt="<?php echo JText::_('COM_FORM2CONTENT_PROJECTFIELDS'); ?>">
				</a>
			</td>
			<td>
				<?php echo $this->escape($item->username); ?>
			</td>
			<td class="center nowrap">
				<?php    ?>
				<?php echo JHTML::_('date',$item->created, $dateFormat); ?>
			</td>
			<td class="center nowrap">
				<?php echo JHTML::_('date',$item->modified, $dateFormat); ?>
			</td>
			<td class="center">
				<?php echo JHtml::_('jgrid.published', $item->published, $i, 'projects.', true, 'cb', null, null); ?>
			</td>			
			<td align="center">
				<a href="<?php echo JRoute::_('index.php?option=com_form2content&task=project.export&view=project&format=raw&id='.$item->id);?>" target="_blank" title="<?php echo JText::_('COM_FORM2CONTENT_EXPORT'); ?>">
					<img src="../media/com_form2content/images/save.png" width="16" height="16" alt="<?php echo JText::_('COM_FORM2CONTENT_EXPORT'); ?>" />
				</a>
			</td>						
		</tr>
		<?php endforeach; ?>
	</tbody>	
	</table>

	<?php echo DisplayCredits(); ?>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
