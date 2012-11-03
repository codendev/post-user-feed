<div style="float:right"><strong>Updated: </strong><?php echo $updated?></div>
<div style="clear: both;"></div>
<ul>
<?php if($top_feeds) foreach($top_feeds as $key=>$list):?>
<li class="postbox" >
	<?php $code= md5($list["link"])?>
	<div class="handlediv" href="javascript:void(null)" title="Show thumbs" onclick="jQuery('#<?php echo $code;?>').toggle();"></div>
	<h3 class="hndle"> <span><a target="_blank" href="<?php echo $list["link"]?>"><?php echo $list["link"]?></a> </span></h3>
	<div style="width:80%;display:none;" id="<?php echo $code;?>">
		<?php foreach($list["images"] as $image_key=>$image){ ?>
			<span><input  type="radio" name="images[<?php echo  $code;?>]" value="<?php echo $image;?>" <?php if($image_key==0&&$list["select"]==""):?> checked="checked"<?php endif; ?>  <?php if($list["select"]==$image):?> checked="checked"<?php endif; ?>/>
		<a onclick="jQuery(this).parent().children('input').click();" href="<?php echo $image;?>"class="colorbox"><img alt="image" width="50" src="<?php echo $image;?>"/></a></span>
		<?php }?>
	</div>
	
</li>
<?php endforeach;?>
</ul>