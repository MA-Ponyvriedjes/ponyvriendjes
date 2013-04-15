<?
// no direct access
defined('_JEXEC') or die;?>
<div class="blog<?php echo $this->menuparams->get('pageclass_sfx');?>">
	<?php if ($this->menuparams->get('show_page_heading', 1)) : ?>
	<h1>
		<?php echo $this->escape($this->menuparams->get('page_heading')); ?>
	</h1>
	<?php endif;?>

	<div class="items">
		<?php foreach($this->articles as $article) : ?>

		<div class="item">
			
		<?php if ($this->menuparams->get('layout_type')=='intro') : ?>	
			<?php echo $article->introtext; ?>
		<?php else: ?>
			<?php echo $article->fulltext; ?>
		<?php endif;?>
		</div>

		<?php endforeach; ?>
	</div>
</div>