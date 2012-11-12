<?php
// If uninstall not called from WordPress exit
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
exit ();

$args = array(
			'post_type'      => 'pur_user_feed',
			
	);

$feed_items = new WP_Query( $args );

if ( $feed_items->have_posts() ) {
		while ( $feed_items->have_posts() ){

			$feed_items->the_post();
			$postid = get_the_ID();

			deletePosts($postid);

			$purge = wp_delete_post( $postid, true );

		}
	}



function  deletePosts($customId){
	
	$args = array(
			'post_type'      => 'post',
			'meta_key'       => 'pur_feed_id',
			'meta_value_num' => $customId,
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
}

delete_option( 'pur_options' );
delete_option( 'pur_settings' );
delete_option( 'pur_db_version' );