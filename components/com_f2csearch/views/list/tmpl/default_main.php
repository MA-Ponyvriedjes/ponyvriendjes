<?php
// no direct access
defined('_JEXEC') or die;?>
<div class="blog <?php echo $this->pageclass_sfx;?>">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>
	<?php var_dump($params);?>
	<div class="items">

		<?php foreach($this->articles as $article) : ?>

		<div class="item">
			<?php echo $article->fulltext; ?>
		</div>

		<?php endforeach; ?>

	</div>
</div>