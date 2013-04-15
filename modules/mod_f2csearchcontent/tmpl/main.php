<?php
// no direct access
defined('_JEXEC') or die;?>

<div class="items">

<?php foreach($list as $article) : ?>

<div class="item">
	<?php echo $article->fulltext; ?>
</div>

<?php endforeach; ?>

</div>