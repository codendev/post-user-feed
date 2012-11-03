<?php
function pur_activate() {

	if ( version_compare( get_bloginfo( 'version' ), '3.2', '<' ) ) {

		deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin
		wp_die( __('This plugin requires WordPress version 3.2 or higher.') );

	}
	pur_schedule_fetch_feeds_cron();
}
function pur_deactivate() {

	if ( wp_next_scheduled( 'pur_cron_hook' ) )
		wp_clear_scheduled_hook( 'pur_cron_hook' );
}

function pur_form_shortcode() {

	ob_start();

	pur_create_rss_form();

	return ob_get_clean();
}

add_shortcode( 'wp_rss_form', 'pur_form_shortcode');


function pur_get_category_id($cat_name){
	$term = get_term_by('name', $cat_name, 'category');
	return $term->term_id;
}


add_action( 'add_meta_boxes', 'pur_remove_meta_boxes', 100 );

function pur_remove_meta_boxes() {
	if ( 'pur_user_feed' !== get_current_screen()->id ) return;
	remove_meta_box( 'sharing_meta', 'pur_user_feed' ,'advanced' );
	remove_meta_box( 'content-permissions-meta-box', 'pur_user_feed' ,'advanced' );
	remove_meta_box( 'wpseo_meta', 'pur_user_feed' ,'normal' );
	remove_meta_box( 'theme-layouts-post-meta-box', 'pur_user_feed' ,'side' );
	remove_meta_box( 'post-stylesheets', 'pur_user_feed' ,'side' );
	remove_meta_box( 'hybrid-core-post-template', 'pur_user_feed' ,'side' );
	remove_meta_box( 'trackbacksdiv22', 'pur_user_feed' ,'advanced' );
	remove_action( 'post_submitbox_start', 'fpp_post_submitbox_start_action' );

}


function pur_create_rss_form(){

	$termArray=array();

	$args = array(

			'child_of'                 => 0,
			'parent'                   => '',
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => 0,
			'hierarchical'             => 1,
			'exclude'                  => '',
			'include'                  => '',
			'number'                   => '',
			'taxonomy'                 => 'category',
			'pad_counts'               => false );

	foreach(get_categories($args) as $key=>$item){


		$categoryArray[$key]["label"]= $item->cat_name;

		$categoryArray[$key]["value"]= $item->category_nicename;

	}

	$posttags =  get_tags(array('hide_empty'=> 0,'orderby'=> 'name'));



	foreach($posttags as $key=>$item){


		$tagArray[$key]["label"]= $item->name;

		$tagArray[$key]["value"]= $item->slug;

	}


	$tags=json_encode($tagArray);


	$data["jQueryUI"]=PUR_URL.trailingslashit("js").trailingslashit("ui")."jquery-ui-1.8.24.custom.min.js";

	$data["css"]=PUR_URL.trailingslashit("js").trailingslashit("css").trailingslashit("ui-lightness")."jquery-ui-1.8.24.custom.css";

	$data["categories"]=$categoryArray;

	$data["tags"]=$tags;

	$data["captcha"]= PUR_URL.trailingslashit("extlib").trailingslashit("captcha")."captcha.php?width=100&height=30&characters=5";

	load_plugin_template("form.tpl.php",$data);


}

function pur_validate_title($title){

	return ! empty($title);

}

function pur_validate_email($email){

	return !filter_var($email_a, FILTER_VALIDATE_EMAIL);

}

function limit_words($text, $limit) {

	$text = strip_tags($text);

	$words = str_word_count($text, 2);

	$pos = array_keys($words);

	if (count($words) > $limit) {

		$text = substr($text, 0, $pos[$limit]) . ' ...';

	}

	return $text;
}

function pur_get_latest_feed($url,$thumbs){

	$top_feeds=array();

	$options = get_option( 'pur_settings' );

	if(!empty($url)&&isset($url)){

		$feed=fetch_feed( $url );

		$maxitems = $feed->get_item_quantity($options["feed_limit"]);

		$items=$feed->get_items(0, $maxitems);

		$top_feeds=array();

		$thumbs=unserialize($thumbs);
	
		foreach($items as $item){

			$page_images=array();

			if($options["page_thumb"]==1)
			{
				if($thumbs!=null&&array_key_exists(md5($item->get_permalink()),$thumbs))
				{
					$select_image=$thumbs[md5($item->get_permalink())];

				}
				$page_images=get_images_url($item->get_permalink());
			}

			$top_feeds[]=array(
					"link"=>$item->get_permalink(),

					"images"=>$page_images,

					"select"=>$select_image);
		}

		return $top_feeds;
	}
	return array();

}

function pur_send_admin_email($blog_title,$email,$feed_url){

	add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));

	$message="<p>Dear admin,</p>
	<p>User has submitted feed for approval.</p>
	<table>
	<tr>
	<td>Blog Title : </td><td>$blog_title</td>
	</tr>
	<tr>
	<td>Email : </td><td>$email</td>
	</tr>
	<tr>
	<td>Feed URL : </td><td>$feed_url</td>
	</tr>
	</table>
	";


	wp_mail(get_option("admin_email"), 'User Submitted a Feed', $message );

}


function pur_send_approve_email($blog_title,$email,$feed_url){

	add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));

	$options = get_option( 'pur_settings' );



	$message="<p>Dear User,.</p>"."<p>$options[email_text]</p>

	<p>Your posted details are:</p>
	<table>
	<tr>
	<td>Blog Title : </td><td>$blog_title</td>
	</tr>
	<tr>
	<td>Email : </td><td>$email</td>
	</tr>
	<tr>
	<td>Feed URL : </td><td>$feed_url</td>
	</tr>
	</table>
	";


	wp_mail($email, get_option("blogname")."Admin Accepted Your Feed", $message );


}
function get_images($html){

	$post_dom = str_get_html($html);

	$img_tags = $post_dom->find('img');

	$images = array();

	foreach($img_tags as $image) {

		$images[] = "<img src='$image->src' alt='$image->alt'/>";
	}

	return $images;
}


function get_images_url($url){

	$post_dom=file_get_html($url);

	$img_tags = $post_dom->find('img');

	$images = array();

	foreach($img_tags as $image) {
			
		$images[] = $image->src;
	}

	return $images;

}



