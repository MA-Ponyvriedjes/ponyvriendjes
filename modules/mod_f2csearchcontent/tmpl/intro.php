<?php
// no direct access
defined('_JEXEC') or die;?>

<? if($list) : ?>
<? if(count($list)>1) : ?>
<div class="items">
<? endif; ?>

<?php foreach($list as $article) : ?>

<div class="item">
	<?php echo $article->introtext; ?>
</div>

<?php endforeach; ?>
<? if(count($list)>1) : ?>
</div>
<?php endif; ?>

<?php endif; ?>