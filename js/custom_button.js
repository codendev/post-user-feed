jQuery(function ($) {
	
$clone= jQuery("body.post-type-pur_user_feed input#publish").clone();

$clone.attr("id","save-draft");

$clone.attr("class","button button-highlighted");

$clone.attr("name","pending");

$clone.attr("value","Save Draft");

$clone.prependTo("body.post-type-pur_user_feed div#publishing-action");

});