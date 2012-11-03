<?php
function load_plugin_template($path,$data=array(),$value=FALSE) {

	if(is_array($data)){
		extract($data);
	}
	ob_start();

	require plugin_dir_path( __FILE__ )."../template/".$path;

	$applied_template = ob_get_contents();
	ob_end_clean();

	if($value) {
		return $applied_template;
	}
	else {

		echo $applied_template;
	}
}
?>
