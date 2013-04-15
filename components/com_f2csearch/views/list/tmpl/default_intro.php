<?php
// no direct access
defined('_JEXEC') or die;?>
<div class="blog">
<div class="items">

<?php foreach($this->articles as $article) : ?>

<div class="item">
	<?php echo $article->introtext; ?>
</div>

<?php endforeach; ?>

</div>
</div>