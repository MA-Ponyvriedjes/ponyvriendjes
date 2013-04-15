<?php
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_SITE.DS.'class.form2content.php');

JHtml::_('behavior.mootools');
JHtml::_('behavior.keepalive');

$fieldId 	= JRequest::getInt('fieldid');
$projectId 	= JRequest::getInt('projectid');
$formId 	= JRequest::getInt('formid');
?>
<script type="text/javascript">
var jTextUp = '<?php echo JText::_('UP', true); ?>';
var jTextDown = '<?php echo JText::_('DOWN', true); ?>';
var jTextAdd = '<?php echo JText::_('ADD', true); ?>';
var jTextDelete = '<?php echo JText::_('DELETE', true); ?>';
var jImagePath = '<?php echo JURI::root(true).'/media/com_form2content/images/'; ?>';
var jGalleryPathTmp = '<? echo F2cImageGallery::getGalleryBaseUrl($formId, $projectId, $fieldId, true); ?>';
var jAltTag = <?php echo json_encode(JText::_('COM_FORM2CONTENT_ALT_TEXT')); ?>;
var jTitleTag = <?php echo json_encode(JText::_('COM_FORM2CONTENT_TITLE')); ?>;

function getNextKey(tableId)
{
	var fldMaxKey = parent.document.getElementById(tableId + 'MaxKey');
	fldMaxKey.value = parseInt(fldMaxKey.value) + 1;
	return fldMaxKey.value;
}

function addNewRow(tableId, rowId, fileName, width, height, thumbWidth, thumbHeight)
{
	var table =  parent.document.getElementById(tableId);
	var index = table.rows.length - 1;
	var elmImage = (width > height) ? new Element('img', {src: jGalleryPathTmp + fileName, width: 100}) : new Element('img', {src: jGalleryPathTmp + fileName, height: 100});
	
	for(i = 1; i < table.rows.length; i++)
	{
		var row = table.rows[i];
		
		if(row.id == rowId)
		{
			index = i;
		}
	}
	
	var row = table.insertRow(index+1);
	row.id = tableId + '_' + getNextKey(tableId);

	var cellLeft = row.insertCell(0);
	var cellMove = row.insertCell(1);
	var cellDelete = row.insertCell(2);
	
	var imgTable = new HtmlTable({properties: {border: 0, cellspacing: 1, cellpadding: 1}});
	imgTable.push([{ content: elmImage, properties: {rowspan: 2}}, jAltTag, new Element('input', { type: 'text', id: row.id + 'alt', name: row.id + 'alt', size: 40, maxLength: 255 })]);
	imgTable.push([jTitleTag, new Element('input', {type: 'text', name: row.id + 'title', id: row.id + 'title', size: 40, maxLength: 255})]);
	cellLeft.appendChild(imgTable.toElement());
	
	cellLeft.appendChild(new Element('input', {type: 'hidden', name: tableId + 'RowKey[]', value: row.id}));
	cellLeft.appendChild(new Element('input', {type: 'hidden', name: row.id + 'filename', value: fileName}));
	cellLeft.appendChild(new Element('input', {type: 'hidden', name: row.id + 'width', value: width}));
	cellLeft.appendChild(new Element('input', {type: 'hidden', name: row.id + 'height', value: height}));
	cellLeft.appendChild(new Element('input', {type: 'hidden', name: row.id + 'thumbwidth', value: thumbWidth}));
	cellLeft.appendChild(new Element('input', {type: 'hidden', name: row.id + 'thumbheight', value: thumbHeight}));
	cellLeft.appendChild(new Element('input', {type: 'hidden', name: row.id + 'isnew', value: 1}));
	
	var lnkUp = new Element('a', { href: 'javascript:moveUp(\''+tableId+'\',\'' + row.id + '\');' });
	var imgUp = new Element('img', { src: jImagePath+'uparrow.png', alt: jTextUp});
	var lnkDown = new Element('a', { href: 'javascript:moveDown(\''+tableId+'\',\'' + row.id + '\');' });
	var imgDown = new Element('img', { src: jImagePath+'downarrow.png', alt: jTextDown});
	var lnkDel = new Element('a', { href: 'javascript:removeRow(\'' + row.id + '\');' });
	var imgDel = new Element('img', { src: jImagePath+'remove.png', alt: jTextDelete});
	lnkUp.inject(cellMove);
	imgUp.inject(lnkUp);
	lnkDown.inject(cellMove);
	imgDown.inject(lnkDown);
	lnkDel.inject(cellDelete);
	imgDel.inject(lnkDel);
}

function uploadImage()
{
	Joomla.submitform('imagegallery.upload', document.getElementById('item-form'));
}
</script>
<form method="post" name="adminForm" id="item-form" enctype="multipart/form-data">
	<input type="file" id="upload" name="upload" />
	<input type="button" value="Upload" onclick="uploadImage();" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="formid" value="<?php echo $formId; ?>" />
	<input type="hidden" name="projectid" value="<?php echo $projectId; ?>" />
	<input type="hidden" name="fieldid" value="<?php echo $fieldId; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
