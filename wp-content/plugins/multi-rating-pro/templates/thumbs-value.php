<?php 
$class = $icon_classes['thumbs_up_on'];
?>
<span class="mrp-thumbs">
	<?php 
	if ( $value == 0 ) {
		$class = $icon_classes['thumbs_down_on'];
	}
	?>
	<i class="<?php echo $class; ?>"></i>
</span>