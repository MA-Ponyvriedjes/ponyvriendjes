<?php
// no direct access
defined('_JEXEC') or die;?>

<div class="items">
<?php 
$twothird =array();
 $full =array();
 $onethird = array();
 
 foreach ($list as $article){
 	//$array = ;
 	if( in_array($article->catid, array(48, 8, 44, 45))){
		array_push( $twothird, $article);
 	}
 	else if(in_array($article->catid, array('14','15','11'))){
 		
 		array_push( $onethird, $article);
 	}
 	else if ($article->catid == '49'){
 		array_push( $full, $article);
 	
 	}
 }
 shuffle($onethird);
 $i=3;
 $normalslides = array();
 foreach ($onethird as $article){
 	if($i% 3==0){
 		if(array_key_exists($i/3 - 1,$full)){
 			array_push( $normalslides, $full[$i/3 - 1]);
 		}
 		else if(array_key_exists(0,$full)){
 			array_push( $normalslides, $full[0]);
 		}
 	}
 	if($i % 3==0){
 		
 		if(array_key_exists($i/3 - 1,$twothird)){
 			array_push( $normalslides, $twothird[$i/3 - 1]);
 		}
 	}
 	array_push( $normalslides, $article);
 	$i++;
 } 


 ?>
<?php foreach($normalslides as $article) : ?>

<div class="item">
	<?php echo $article->introtext; ?>
</div>

<?php endforeach; ?>

</div>
