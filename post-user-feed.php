<?php
/*
 Plugin Name: Post User Feed
Plugin URI: http://wordpress.org/extend/plugins/post-user-feed/
Description: Imports User submited feeds and merges multiple RSS Feeds using SimplePie
Version: 1.0
Author: CodenDev
Author URI: http://www.codendev.com
License: GPLv3
*/

/*
 Copyright 2012 CodenDev
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

/*
 @version 1.0
@author Codendev
@copyright Copyright (c) 2012, Codendev
@copyright for some parts Copyright (c) 2012, Jean Galea
@link http://www.codendev.com/
@license http://www.gnu.org/licenses/gpl.html
*/


define ("PUR_PATH",plugin_dir_path( __FILE__ ),true);

define ("PUR_URL",plugin_dir_url( __FILE__ ),true);

define("PUR_FUNC",PUR_PATH. trailingslashit( 'func' ) ,true);

define("PUR_EXTLIB",PUR_PATH. trailingslashit( 'extlib' ) ,true);

require_once(PUR_PATH. trailingslashit( 'func' )."template.php");


require_once(PUR_FUNC. 'util.php');

require_once(PUR_FUNC. 'feed_custom_type.php');

require_once (PUR_FUNC.'admin-options.php' );

require_once(PUR_EXTLIB.'simple_html_dom/simple_html_dom.php');



add_action( 'init', 'pur_init' );


function pur_init() {
	register_activation_hook(PUR_FUNC. 'util.php', 'pur_activate' );
	register_deactivation_hook(PUR_FUNC. 'util.php', 'pur_deactivate' );
	if(!session_id()){
		session_start();
	}
}

add_action('init','pur_install');


function pur_install(){

	$settings = get_option( 'pur_settings' );


	if(empty($settings)){


		$settings['feed_limit'] = 10;

		$settings['excerpt_limit'] = 100;

		$settings['content_limit'] = 200;

		$settings['page_thumb'] = 0;

		$settings['email_text'] = "Admin has approved your post.";

		update_option( 'pur_settings', $settings );

	}

}

add_action( 'plugins_loaded', 'pur_load_textdomain' );

function pur_load_textdomain() {
	load_plugin_textdomain( 'pur', false, plugin_basename( __FILE__ ) . '/lang/' );
}



add_action( 'wp_head', 'pur_head_scripts_styles' );

function pur_head_scripts_styles() {
	wp_enqueue_style( 'form-style', PUR_URL . 'css/form-style.css' );

}

add_action( 'admin_enqueue_scripts', 'pur_admin_scripts_styles' );

function pur_admin_scripts_styles() {


	$screen = get_current_screen();


	if ( ( 'post' === $screen->base || 'edit' === $screen->base ) && ( 'pur_user_feed' === $screen->post_type) ) {

		wp_enqueue_style( 'styles', PUR_URL . 'css/styles.css' );
		wp_enqueue_style( 'colorbox_css', PUR_URL . 'js/css/colorbox/colorbox.css' );
		wp_enqueue_script( 'custom_button', PUR_URL . 'js/custom_button.js' );
		wp_enqueue_script( 'colorbox', PUR_URL . 'js/jquery.colorbox-min.js' );
		wp_enqueue_script( 'custom_colorbox', PUR_URL . 'js/custom.js' );

		if ( 'post' === $screen->base && 'pur_user_feed' === $screen->post_type ) {

		}
	}
}

add_action('wp_insert_post', 'pur_fetch_feed_items');

function pur_fetch_feed_items( $post_id ) {


	$post = get_post( $post_id );

	$options = get_option( 'pur_settings' );


	if( ( $post->post_type == 'pur_user_feed' ) && ( $post->post_status == 'publish' ) ) {


		$feed_sources = new WP_Query( array(
				'post_type'      => 'pur_user_feed',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
		) );

		$feed_tags_array=array();

		foreach(wp_get_post_tags($post->ID) as $item){

			$feed_tags_array[]=$item->slug;
		}

		$feed_tags=implode(",", $feed_tags_array);
		$categories=wp_get_post_categories($post->ID);

		if( $feed_sources->have_posts() ) {

			while ( $feed_sources->have_posts() ) {
				$feed_sources->the_post();

				$feed_ID = get_the_ID();

				$feed_url = get_post_meta( get_the_ID(), 'url', true );



				if( ! empty( $feed_url ) ) {

					add_filter( 'wp_feed_cache_transient_lifetime' , 'pur_return_7200' );

					$feed = fetch_feed( $feed_url );

					remove_filter( 'wp_feed_cache_transient_lifetime' , 'pur_return_7200' );

					if ( ! is_wp_error( $feed ) ) {

						$maxitems = $feed->get_item_quantity($options["feed_limit"]);

						$items = $feed->get_items(0, $maxitems);
							
					}
				}

				if ( ! empty( $items ) ) {
						
					global $wpdb;

					$existing_permalinks = $wpdb->get_col(
							"SELECT meta_value
							FROM $wpdb->postmeta
							WHERE meta_key = 'pur_item_permalink'
							AND post_id IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_value = $feed_ID)
							");

					if(count($existing_permalinks)>0){

						$existing_posts=$wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_value = $feed_ID");

						foreach($existing_posts as $item){

							wp_set_post_categories($item, $categories);

							wp_set_post_tags($item, $feed_tags);

						}

					}

					foreach ( $items as $item ) {

						if (  ! ( in_array( $item->get_permalink(), $existing_permalinks ) )  ) {

							$firstImage="";

							if($options["page_thumb"]==0){

								$images=get_images($item->get_description());

								if(count($images)>0){

									$firstImage=$images[0];
								}
							}
							else{
								$images=unserialize(get_post_meta( $post->ID, "thumbs", true ));
									
								if(array_key_exists(md5($item->get_permalink()), $images))
								{

									$firstImage="<img src='".$images[md5($item->get_permalink())]."' alt='".$item->get_title()."' />";

								}
							}


							$continue_reading="<br/><a target='new' href='".$item->get_permalink()."' >".__("continue reading")." &raquo;</a>";


							$excerpt=$firstImage."<br/>".limit_words(strip_tags($item->get_description()),$options["excerpt_limit"]).$continue_reading;

							$content=$firstImage."<br/>".limit_words(strip_tags($item->get_description()),$options["content_limit"]).$continue_reading;

							$feed_item = array(
									'post_title' => $item->get_title(),
									'post_content' => $content,
									'post_excerpt'=>$excerpt,
									'post_status' => 'publish'
							);
							$new_post_id = wp_insert_post( $feed_item );

							wp_set_post_categories($new_post_id, $categories);

							wp_set_post_tags($new_post_id, $feed_tags);

							update_post_meta( $new_post_id, 'pur_item_permalink', $item->get_permalink() );

							update_post_meta( $new_post_id, 'pur_feed_id', $feed_ID);

						} 
					} 
				}
			}

			$email = get_post_meta( $post_id, 'email', true);
			$url = get_post_meta( $post_id, 'url', true);

			if($email!=get_option("admin_email")){

				pur_send_approve_email($post->post_title,$email,$url);
					
			}

			wp_reset_postdata();
		} 
	} 
} 

add_action( 'trash_pur_user_feed', 'pur_delete_feed_items' );

function pur_delete_feed_items() {
	global $post;

	$args = array(
			'post_type'      => 'post',
			'meta_key'       => 'pur_feed_id',
			'meta_value_num' => $post->ID,
			'posts_per_page' => -1
	);

	$feed_items = new WP_Query( $args );

	if ( $feed_items->have_posts() ) {
		while ( $feed_items->have_posts() ){

			$feed_items->the_post();
			$postid = get_the_ID();
			$purge = wp_delete_post( $postid, true );

		}
	}

	wp_reset_postdata();
} // end pur_delete_feed_items

add_action('parse_request', 'pur_public_submission');

function pur_save_user_post($title,$url,$category,$email,$tags,$captcha,$error=array()) {

	$postData = array();


	foreach(explode(",",trim($category)) as $item){

		if(!empty($item))
			$categories[]=pur_get_category_id(trim($item));

	}

	$tags=trim($tags);

	$tagArray=array();

	foreach(explode(",",$tags) as $item){

		$tagArray[]=trim($item);
	}

	$tags=implode(",", $tagArray);

	$postData['post_title'] = $title;
	$postData['post_status'] = 'pending';
	$postData[ 'post_type']="pur_user_feed";

	if(!pur_validate_title($title)){
			
		$error[]="Please provide title.";
	}

	if($captcha!=$_SESSION["security_code"]){

		$error[]="Please provide captcha again.";

	}

	if(empty($email)||!pur_validate_email($email)){

		$error[]="Please provide valid email.";

	}

	if(!empty($url))
		$feed=fetch_feed( $url );


	if(empty($url)||is_wp_error($feed)){

		$error[]="Please provide valid feed.";

	}

	if(count($error)>0){

		return false;
	}

	// 	$maxitems = $feed->get_item_quantity(10);


	// 	$items=$feed->get_items(0, $maxitems);

	// 	$top_feeds=array();

	// 	foreach($items as $item){

	// 		$top_feeds[]=$item->get_permalink();

	// 	}



	$newPost = wp_insert_post($postData);

	if ($newPost) {

		if(count($categories)>0)
			wp_set_post_categories($newPost, $categories);

		if(count($tagArray)>0){

			wp_set_post_tags($newPost,$tags);
		}

		update_post_meta( $newPost, "email", $email );
		update_post_meta( $newPost, "url", $url );
		//update_post_meta( $newPost, "top_feeds", implode(",",$top_feeds) );

		return true;
	}

	return false;

}


function pur_public_submission() {

	if (isset($_POST['pur_post_hidden']) && ! empty($_POST['pur_post_hidden'])) {

		$title = stripslashes($_POST['pur_post_title']);
		$url = stripslashes($_POST['pur_post_url']);
		$category = $_POST['pur_post_category'];
		$email = $_POST['pur_post_email'];
		$captcha =$_POST["pur_post_captcha"];
		$tags=$_POST["pur_post_tag"];

		$error=array();

		$publicSubmission = pur_save_user_post($title,$url,$category,$email,$tags,$captcha,&$error);

		$settings['error-message']=implode("<br/>",$error)."<br/><a href='javascript:back();'>go back</a>";

		if (false == ($publicSubmission)) {
			$errorMessage = empty($settings['error-message']) ? __('An error occurred.  Please go back and try again.') : $settings['error-message'];
			if( !empty( $_POST[ 'redirect-override' ] ) ) {
				$redirect = stripslashes( $_POST[ 'redirect-override' ] );
				$redirect = add_query_arg( array( 'submission-error' => '1' ), $redirect );
				wp_redirect( $redirect );
				exit();
			}
			wp_die($errorMessage);
		} else {
			$redirect = empty($settings['redirect-url']) ? $_SERVER['REQUEST_URI'] : $settings['redirect-url'];
			if (! empty($_POST['redirect-override'])) {
				$redirect = stripslashes($_POST['redirect-override']);
			}
			$redirect = add_query_arg(array('success'=>1), $redirect);


			pur_send_admin_email($title,$email,$url);

			wp_redirect($redirect);
			exit();
		}
	}
}