<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js" type="text/javascript"></script>

<link rel="stylesheet" href="<?php echo $css; ?>">

<?php if($_GET["success"]==1):?>
<span class="pur-success-message">Your Feed Post is Submitted!</span>
<?php endif?>

<form class="pur-user-feed" action="" name="pur_post_user_rss" method="post" >

<input name="pur_post_hidden" type="hidden" id="post_hidden" value="1">

<div class="title"> <span id="alertmessage"></span> </div>
<div class="title"> Blog title </div>
<div class="field">
<input name="pur_post_title" class="textbox" type="text" id="post_title" maxlength="120" style="width:300px;">
</div>
<div class="title"> Feed URL </div>
<div class="field">
<input name="pur_post_url" class="textbox" type="text" id="post_url" maxlength="120" style="width:300px;"><span>e.g http://www.engadget.com/rss.xml</span>
</div>
<div class="title"> Categories </div>
<div class="field">
<select id="pur_category" name="pur_post_category" maxlength="120" style="width:310px;">
<option value="">--Select category--</option>
<?php foreach($categories as $item):?>
<option value="<?php echo $item[label];?>"><?php echo $item[value];?></option>
<?php endforeach;?>
</select>
</div>
<div class="title"> Tags </div>
<div class="title">
<input name="pur_post_tag" class="textbox" type="text" id="pur_tags" maxlength="150"  style="width:300px;"><span>e.g simple,new,test</span>
</div>
<div class="title"> Your email </div>
<div class="field">
<input name="pur_post_email" class="textbox" type="text" id="pur_post_email" maxlength="120" style="width:300px;">
</div>
<div class="title"> Enter below security code </div>
<div class="field">
<input name="pur_post_captcha" class="textbox" type="text" id="captcha" maxlength="6" autocomplete="off">
</div>
<div class="title">
<img id='pur_captcha' src="<?php echo $captcha ?>" /> <span><a title="refresh" href="javascript:void(null);" onclick="document.getElementById('pur_captcha').src = '<?php echo $captcha ?>&rnd=' + Math.random();"><img alt="refresh" src="<?php echo PUR_url?>img/refresh.gif"/></a></span>
</div>
<div class="field">
<input type="submit" name="pur_post_button" value="Submit" />
</div>
</form>

<script>
$(function() {
	var availableTags =
		<?php echo $tags;?>
	;
	function split( val ) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}

	$( "#pur_tags" )
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === $.ui.keyCode.TAB &&
					$( this ).data( "autocomplete" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			minLength: 0,
			source: function( request, response ) {
				// delegate back to autocomplete, but extract the last term
				response( $.ui.autocomplete.filter(
					availableTags, extractLast( request.term ) ) );
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				
				
				// remove the current input
				terms.pop();
				// add the selected item
				if($.inArray(ui.item.value, terms)==-1){
					
					terms.push( ui.item.value );
				}
				// add placeholder to get the comma-and-space at the end
				terms.push( "" );
				this.value=terms.join(", ");
					
				return false;
			}
		});
});
</script>

