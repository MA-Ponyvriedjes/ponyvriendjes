<?php
// No direct access 
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');

$saveOrder	= $listOrder == 'a.ordering';
?>

<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/marvinvk/js/libs/jquery.sortable.min.js"></script>
<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/marvinvk/js/com_form2content/forms/default.js"></script>


<div class="com_form2content forms default">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_form2content&task=forms.display&view=forms');?>" method="post" name="adminForm" id="adminForm" class="form2content">
		<div id="cms-top-block">
			<div class="top-title">
				<h1>Artikelen</h1>
			</div>
			<div class="submitbuttons-top">

				<?php if($this->menuParms->get('show_new_button', 1)) : ?>
					<form action="<?php echo JRoute::_('index.php?option=com_form2content&view=form&layour=edit');?>">
					    <button class="new active" type="submit">Nieuw</button>
					</form>
				<?php endif; ?>

				<?php if($this->menuParms->get('show_publish_button', 1)) : ?>
					<button class="publishbutton" type="button" onclick="javascript:
						if(document.adminForm.boxchecked.value==0){
							alert('<?php echo JText::sprintf(JText::_('COM_FORM2CONTENT_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO'), 
							JText::_('COM_FORM2CONTENT_PUBLISH')); ?>');
						}else{  
							Joomla.submitbutton('forms.publish')
						}">
						Publiceer
					</button>
					<button class="unpublishbutton" type="button" onclick="javascript:
						if(document.adminForm.boxchecked.value==0){
							alert('<?php echo JText::sprintf(JText::_('COM_FORM2CONTENT_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO'), 
							JText::_('COM_FORM2CONTENT_UNPUBLISH')); ?>');
						}else{  
							Joomla.submitbutton('forms.unpublish')
						}">
						Depubliceer
					</button>
				<?php endif; ?>

				<?php if($this->menuParms->get('show_copy_button', 1)) : ?>
					<button class="copybutton" type="button" onclick="javascript:
						if(document.adminForm.boxchecked.value==0){
							alert('<?php echo JText::sprintf(JText::_('COM_FORM2CONTENT_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO'), 
							JText::_('COM_FORM2CONTENT_COPY')); ?>');
						}else{ 
							Joomla.submitbutton('forms.copy')
						}">
						Kopieer
					</button>
				<?php endif; ?>

				<?php if($this->menuParms->get('show_delete_button', 1)) : ?>
					<button class="deletebutton" type="button" onclick="javascript:
						if(document.adminForm.boxchecked.value==0){
							alert('<?php echo JText::sprintf(JText::_('COM_FORM2CONTENT_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST_TO'), 
							JText::_('COM_FORM2CONTENT_DELETE')); ?>');
						}else{  
							Joomla.submitbutton('forms.delete')
						}">
						Verwijder
					</button>
				<?php endif; ?>	

			</div>
		</div>
		<div id="cms-middle-block-top">
			
			<fieldset id="filter-bar">	
				<div class="filter-search fltlft filter-group">
					<?php if($this->menuParms->get('show_search_filter')) : ?>
						<!--<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>-->
						<input type="text" name="forms_filter_search" id="forms_filter_search" value="<?php echo $this->escape($this->state->get('forms.filter.search')); ?>" title="<?php echo JText::_('COM_FORM2CONTENT_FILTER_SEARCH_DESC'); ?>" />
						<button type="submit" class="btn"><?php echo JText::_('zoek'); ?></button>
						<button type="button" onclick="document.id('forms_filter_search').value='';this.form.submit();"><?php echo JText::_('leeg'); ?></button>
					<?php endif; ?>
				</div>
				<div class="filter-select fltrt selection-group">
					<?php if($this->menuParms->get('show_category_filter')) : ?>
					<select name="forms_filter_category_id" class="inputbox" onchange="this.form.submit()">
						<option value="">- Selecteer categorie -<!--<?php echo JText::_('JOPTION_SELECT_CATEGORY');?>--></option>
						
						<?php echo JHtml::_('select.options', $this->categoryOptions, 'id', 'title', $this->state->get('forms.filter.category_id'));?>
					</select>
					<?php endif; ?>
				</div>
			</fieldset>
		</div>
		<div id="cms-middle-block-middle">
			<div class="tabel">
				<ul class="head-row">
					<li class="column column1">
						<?php echo JHtml::_('grid.sort', 'COM_FORM2CONTENT_GRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</li>				
					<li class="column column2">
						<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
					</li>
					<li class="column column3" align="left">
						<?php echo JHtml::_('grid.sort', 'COM_FORM2CONTENT_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</li>
					<?php if($this->menuParms->get('show_published_column')) : ?>
						<li class="column column4">
							<?php echo JHtml::_('grid.sort', 'COM_FORM2CONTENT_PUBLISHED', 'a.state', $listDirn, $listOrder); ?>
						</li>
					<?php endif; ?>
					<?php if($this->menuParms->get('show_ordering')) : ?>
						<li class="column column5">
							<?php echo JHtml::_('grid.sort',  'COM_FORM2CONTENT_GRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
							<?php if ($saveOrder) :?>
								<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'forms.saveorder'); ?>
							<?php endif; ?>
						</li>
					<?php endif; ?>
					<?php if($this->menuParms->get('show_author_column')) : ?>
						<li class="column column6">
							<?php echo JHtml::_('grid.sort', 'COM_FORM2CONTENT_GRID_HEADING_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
						</li>
					<?php endif; ?>
					<?php if($this->menuParms->get('show_created_column')) : ?>
					<!--<li class="table-created">
						<?php //echo JHtml::_('grid.sort', 'COM_FORM2CONTENT_CREATED', 'a.created', $listDirn, $listOrder); ?>
					</li>-->
					<?php endif; ?>

					<?php if($this->menuParms->get('show_modified_column')) : ?>
						<li class="column column7">
							<?php echo JHtml::_('grid.sort', 'COM_FORM2CONTENT_MODIFIED', 'a.modified', $listDirn, $listOrder); ?>
						</li>
					<?php endif; ?>
					<?php if($this->menuParms->get('show_created_column')) : ?>
						<li class="column column8">
							<?php echo JHtml::_('grid.sort', 'Publiceren vanaf', 'a.publish_up', $listDirn, $listOrder); ?>
						</li>
						<li class="column column9">
							<?php echo JHtml::_('grid.sort', 'Gepubliceerd tot', 'a.publish_down', $listDirn, $listOrder); ?>
						</li>
					<?php endif; ?>
					<?php if($this->menuParms->get('show_category')) : ?>
						<li class="column column10">
							<?php echo JHtml::_('grid.sort', 'COM_FORM2CONTENT_CATEGORY', 'category_title', $listDirn, $listOrder); ?>
						</li>
					<?php endif; ?>
					
						
				
					<?php if($this->menuParms->get('show_language_column')) : ?>
						<li class="column column11">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
						</li>
					<?php endif; ?>
				</ul><!-- end head-row -->
				

				<?php foreach ($this->items as $i => $item) :
					$item->max_ordering = 0; //??
					
					$ordering	= ($listOrder == 'a.ordering');
					$canEdit	= $user->authorise('core.edit', 'com_form2content.form.'.$item->id);
					$canCheckin = true;
					$canEditOwn	= $user->authorise('core.edit.own', 'com_form2content.form.'.$item->id) && $item->created_by == $userId;
					$canChange	= $user->authorise('core.edit.state', 'com_form2content.form.'.$item->id) && $canCheckin;

				?>

					<ul class="row<?php echo $i % 2; ?> body-row">


						<input type="hidden" name="itemid[]" value="itemsRowID<?php echo (int) $item->id; ?>"/>


						<li class="column column1">
							<span><?php echo (int) $item->id; ?></span>
						</li>			
						<li class="column column2">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</li>
						<li class="column column3">
							<?php if ($canEdit || $canEditOwn) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_form2content&task=form.edit&id='.$item->id);?>">
									<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->title); ?>
							<?php endif; ?>
						</li>
						<?php if($this->menuParms->get('show_published_column')) : ?>
							<li class="column column4">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'forms.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
							</li>
						<?php endif; ?>
						<?php if($this->menuParms->get('show_ordering')) : ?>				
							<li class="column column5">
								<?php if ($canChange) : ?>
									<?php if ($saveOrder) :?>
										<?php if ($listDirn == 'asc') : ?>
											<span><?php echo $this->pagination->orderUpIcon($i, ($item->catid == @$this->items[$i-1]->catid), 'forms.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
											<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->catid == @$this->items[$i+1]->catid), 'forms.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
										<?php elseif ($listDirn == 'desc') : ?>
											<span><?php echo $this->pagination->orderUpIcon($i, ($item->catid == @$this->items[$i-1]->catid), 'forms.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
											<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->catid == @$this->items[$i+1]->catid), 'forms.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
										<?php endif; ?>
									<?php endif; ?>
									<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
									<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
								<?php else : ?>
									<?php echo $item->ordering; ?>
								<?php endif; ?>
							</li>
						<?php endif; ?>
						<?php if($this->menuParms->get('show_author_column')) : ?>								
							<li class="column column6">
								<?php echo $this->escape($item->creator); ?>
							</li>
						<?php endif; ?>
						
						<?php if($this->menuParms->get('show_created_column')) : ?>				
							<!--<li class="table-created-item">
								<?php //echo JHTML::_('date',$item->created, JText::_('DATE_FORMAT_LC4')); ?>
							</li>-->
						<?php endif; ?>
						<?php if($this->menuParms->get('show_modified_column')) : ?>				
							<li class="column column7">
								<?php
								if($item->modified && ($item->modified != $this->nullDate))
								{
									echo JHTML::_('date',$item->modified, JText::_('DATE_FORMAT_LC4'));
								} 
								?>
							</li>
						<?php endif; ?>
						<?php if($this->menuParms->get('show_created_column')) : ?>	
							<li class="column column8">
								<span><?php echo JHTML::_('date',$item->publish_up, JText::_('DATE_FORMAT_LC4')); ?></span>
							</li>
							<li class="column column9">
								<span><?php echo JHTML::_('date',$item->publish_down, JText::_('DATE_FORMAT_LC4')); ?></span>
							</li>
						<?php endif; ?>
						<?php if($this->menuParms->get('show_category')) : ?>
							<li class="column column10">
								<span><?php echo $this->escape($item->name); ?></span>
							</li>
						<?php endif; ?>
						
						<?php if($this->menuParms->get('show_language_column')) : ?>				
							<li class="column column11">
								<?php if ($item->language=='*'):?>
									<?php echo JText::alt('JALL','language'); ?>
								<?php else:?>
									<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('COM_FORM2CONTENT_UNDEFINED'); ?>
								<?php endif;?>
							</li>				
						<?php endif; ?>
					</ul><!-- end body-row -->
				<?php endforeach; ?>
			</div><!-- end table -->

		</div><!-- end cms-middle-block-middle -->

		<div id="cms-middle-block-bottom">
				<?php echo $this->pagination->getListFooter(); ?>
		</div>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>

	</form>
</div><!-- end com_form2content forms default -->