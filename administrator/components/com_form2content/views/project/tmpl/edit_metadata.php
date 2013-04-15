<?php
defined('_JEXEC') or die;
?>
<?php echo $this->form->getLabel('metadesc'); ?>
<?php echo $this->form->getInput('metadesc'); ?>

<?php echo $this->form->getLabel('metakey'); ?>
<?php echo $this->form->getInput('metakey'); ?>

<?php foreach($this->form->getGroup('metadata') as $field): ?>
	<?php if ($field->hidden): ?>
		<?php echo $field->input; ?>
	<?php else: ?>
		<?php echo $field->label; ?>
		<?php echo $field->input; ?>
	<?php endif; ?>
<?php endforeach; ?>
