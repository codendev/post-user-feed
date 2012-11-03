<?php  

add_action( 'admin_menu', 'pur_register_menu_pages' );

function pur_register_menu_pages() {

	
	add_submenu_page( 'edit.php?post_type=pur_user_feed', __( 'Post User Feed Settings', 'pur' ), __( 'Settings', 'pur' ), 'manage_options', 'pur-aggregator-settings', 'pur_settings_page' );
	add_submenu_page( 'edit.php?post_type=pur_user_feed', __( 'Post User Feed Help', 'pur' ), __( 'Help', 'pur' ), 'manage_options', 'pur-help', 'pur_help_page' );
	
}

add_action( 'admin_init', 'pur_admin_init' );

function pur_admin_init() {

	register_setting( 'pur_options', 'pur_options' );

	add_settings_section( 'pur_main', '', 'pur_section_text', 'pur' );

	register_setting( 'pur_settings', 'pur_settings' );


	add_settings_section( 'pur-settings-main', '', 'pur_settings_section_text', 'pur-aggregator-settings' );

	add_settings_field( 'pur-settings-feed-limit', __( 'Feed limit', 'pur' ),
			'pur_setting_feed_limit', 'pur-aggregator-settings', 'pur-settings-main');
	
	add_settings_field( 'pur-settings-excerpt-text', __( 'Excerpt limit (words)', 'pur' ),
			'pur_setting_excerpt_limit', 'pur-aggregator-settings', 'pur-settings-main');
	
	add_settings_field( 'pur-settings-content-text', __( 'Content limit (words)', 'pur' ),
			'pur_setting_content_limit', 'pur-aggregator-settings', 'pur-settings-main');
	
	add_settings_field( 'pur-settings-page-thumb', __( 'Enable Page Thumb', 'pur' ),
			'pur_setting_page_thumb', 'pur-aggregator-settings', 'pur-settings-main');

	add_settings_field( 'pur-settings-email-text', __( 'Email text', 'pur' ),
			'pur_setting_email_text', 'pur-aggregator-settings', 'pur-settings-main');

	
	
}

function pur_help_page() {

	load_plugin_template("help.tpl.php");
}

function pur_settings_page() {
	?>
<div class="wrap">

	<h2>
		<?php _e( 'Post User Feed Settings' ); ?>
	</h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'pur_settings' ) ?>
		<?php do_settings_sections( 'pur-aggregator-settings' ); ?>
		<p class="submit">
			<input type="submit" value="<?php _e( 'Save Settings', 'pur' ); ?>"
				name="submit" class="button-primary">
		</p>
	</form>
</div>
<?php
}


// Draw the section header
function pur_settings_section_text() {
	//     echo '<p>Enter your settings here.</p>';
}

function pur_setting_email_text() {

	$options = get_option( 'pur_settings' );

	echo "<textarea name='pur_settings[email_text]' id='email_text' class='text' style='width:50%;'>$options[email_text]</textarea>";


}


function pur_setting_page_thumb() {
	
	$options = get_option( 'pur_settings' );
	
	$yes=$options[page_thumb]==true?"checked='checked'":"";
	
	$no=$options[page_thumb]==false?"checked='checked'":"";
	
	echo "<label for='page-thumb-yes'>Yes</label> <input id='page-thumb-yes' name='pur_settings[page_thumb]' type='radio' value='1' $yes />";
	
	echo " <label for='page-thumb-yes'>No</label> <input id='page-thumb-no' name='pur_settings[page_thumb]' type='radio' value='0' $no />";
	
	
	
}


function pur_setting_excerpt_limit() {
	$options = get_option( 'pur_settings' );
	
	echo "<input id='excerpt-limit' name='pur_settings[excerpt_limit]' type='text' value='$options[excerpt_limit]' />";
}


function pur_setting_content_limit() {
	$options = get_option( 'pur_settings' );

	echo "<input id='excerpt-limit' name='pur_settings[content_limit]' type='text' value='$options[content_limit]' />";
}



function pur_setting_feed_limit() {
	$options = get_option( 'pur_settings' );
	// echo the field

	echo "<input id='feed-limit' name='pur_settings[feed_limit]' type='text' value='$options[feed_limit]' />";
}




function pur_base_admin_body_class( $classes )
{
	if ( is_admin() && isset($_GET['action']) ) {
		$classes .= 'action-'.$_GET['action'];
	}
	if ( is_admin() && isset($_GET['post']) ) {
		$classes .= ' ';
		$classes .= 'post-'.$_GET['post'];
	}
	if ( isset($_GET['post_type']) ) $post_type = $_GET['post_type'];
	if ( isset($post_type) ) {
		$classes .= ' ';
		$classes .= 'post-type-'.$post_type;
	}
	if ( isset( $_GET['post'] ) ) {
		$post_query = $_GET['post'];
	}
	if ( isset($post_query) ) {
		$current_post_edit = get_post($post_query);
		$current_post_type = $current_post_edit->post_type;
		if ( !empty($current_post_type) ) {
			$classes .= ' ';
			$classes .= 'post-type-'.$current_post_type;
		}
	}
	return $classes;
}
add_filter('admin_body_class', 'pur_base_admin_body_class');
