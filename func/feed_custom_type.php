<?php

add_action( 'init', 'pur_register_post_types' );

function pur_register_post_types() {

	$labels = apply_filters( 'pur_feed_post_type_labels', array(
			'name'                  => __( 'Post User Feed', 'pur' ),
			'singular_name'         => __( 'User Feed', 'pur' ),
			'add_new'               => __( 'Post User Feed', 'pur' ),
			'all_items'             => __( 'All User Post Feed', 'pur' ),
			'add_new_item'          => __( 'Add User Feed', 'pur' ),
			'edit_item'             => __( 'Edit User Feed', 'pur' ),
			'new_item'              => __( 'New User Feed', 'pur' ),
			'view_item'             => __( 'View User Feed', 'pur' ),
			'search_items'          => __( 'Search Feeds', 'pur' ),
			'not_found'             => __( 'No User Feed Found', 'pur' ),
			'not_found_in_trash'    => __( 'No User Feed Found In Trash', 'pur' ),
			'menu_name'             => __( 'Post User Feed', 'pur' )
	)
	);

	$feed_args = apply_filters( 'pur_feed_post_type_args', array(
			'public'        => true,
			'query_var'     => 'post_user_feed',
			'menu_position' => 100,
			'menu_icon'     => PUR_URL . 'img/feed_icon_14x14.png',
			'show_in_menu'  => true,
			'supports'      => array( 'title' ),
			'rewrite'       => array(
					'slug'       => 'postfeeds',
					'with_front' => false
			),
			'labels'        => $labels,
			'taxonomies' => array('category','post_tag')

	)
	);


	register_post_type( 'pur_user_feed', $feed_args );

	register_taxonomy_for_object_type('category', 'pur_user_feed');
	register_taxonomy_for_object_type('post_tag', 'pur_user_feed');

}

add_filter( 'manage_edit-pur_user_feed_columns', 'pur_add_columns');

function pur_add_columns( $columns ) {


	$myColumn = array (
			'cb'          => '<input type="checkbox" />',
			'title'       => __( 'Blog Name', 'pur' ),
			'email'       =>__( 'Email', 'pur' ),
			'url'         => __( 'URL', 'pur' ),

	);
	;
	return array_merge($myColumn,$columns);
}


add_action( "manage_pur_user_feed_posts_custom_column", "pur_post_custom_columns", 10, 2 );

function pur_post_custom_columns( $column, $post_id ) {


	switch ( $column ) {
		case 'url':
			$url = get_post_meta( $post_id, 'url', true);
			echo '<a href="' . esc_url($url) . '">' . esc_url($url) . '</a>';
			break;

		case 'email':
			$email = get_post_meta( $post_id, 'email', true);
			echo esc_html( $email );
			break;
	}
}


add_action( 'add_meta_boxes', 'pur_add_meta_boxes');

function pur_add_meta_boxes() {


	//remove_meta_box( 'submitdiv', 'pur_user_feed', 'side' );

	add_meta_box(
	'submitdiv',
	__( 'Save Feed Source', 'pur' ),
	'post_submit_meta_box',
	'pur_user_feed', // $page
	'side',
	'high');




	add_meta_box(
	'custom_meta_box', // $id
	__( 'User Feed Post Details', 'pur' ), // $title
	'pur_user_custom_field', // $callback
	'pur_user_feed', // $page
	'normal', // $context
	'high'); // $priority


	add_meta_box(
	'custom_feed_box', // $id
	__( 'Latest Feeds', 'pur' ), // $title
	'pur_show_feed_box', // $callback
	'pur_user_feed', // $page
	'normal', // $context
	'high'); // $priority


}

function pur_show_feed_box($post){


	$top_feeds = get_post_meta( $post->ID, "top_feeds", true );

	$top_feeds=pur_get_latest_feed(get_post_meta( $post->ID, "url", true ),get_post_meta( $post->ID, "thumbs", true ));

	if(!empty($top_feeds)){
		update_post_meta( $post->ID, "top_feeds", implode(",",$top_feeds) );
	}

	$data["top_feeds"]=$top_feeds;

	$data["updated"]= date("F j, Y, g:i a");

	load_plugin_template("feed_list.tpl.php",$data);


}


function pur_user_custom_field($post){

	$data["post"]=$post;

	load_plugin_template("admin_form.tpl.php",$data);

}

add_action( 'save_post', 'pur_save_custom_fields' );

function pur_save_custom_fields( $post_id ) {


	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
		return $post_id;

	// check permissions
	if ( 'page' == $_POST[ 'post_type' ] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return $post_id;
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	$old = get_post_meta( $post_id, "email", true );
	$new = $_POST[ "email" ];

	if ( $new && $new != $old ) {
		update_post_meta( $post_id, "email", $new );
	} elseif ( '' == $new && $old ) {
		delete_post_meta( $post_id, "email", $old );
	}
	$old = get_post_meta( $post_id, "url", true );
	$new = $_POST[ "url" ];

	if ( $new && $new != $old ) {
		update_post_meta( $post_id, "url", $new );
	} elseif ( '' == $new && $old ) {
		delete_post_meta( $post_id, "url", $old );
	}

	$old = get_post_meta( $post_id, "thumbs", true );


	$new=serialize($_POST['images']);

	if ( $new && $new != $old ) {
		update_post_meta( $post_id, "thumbs", $new );
	} elseif ( '' == $new && $old ) {
		delete_post_meta( $post_id, "thumbs", $old );
	}

}

add_filter( 'gettext', 'pur_change_publish_button', 10, 2 );

function pur_change_publish_button( $translation, $text ) {
	if ( 'pur_user_feed' == get_post_type()){
		if ( $text == 'Publish' )
			return __( 'Approve', 'pur' );
		if ( $text == 'Update' )
			return __( 'Update Blog Feeds', 'pur' );
	}

	return $translation;
}


