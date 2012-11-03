<input type="hidden" name="pur_meta_box_nonce" value="<?php  wp_create_nonce( basename( __FILE__ ) ) ?>" />
<table class="form-table">

	<tr>
		<th><label for="email">Email</label>
		</th>
		<td><input type="text" name="email" id="email" 	value="<?php echo get_post_meta( $post->ID, "email", true ); ?>" size="55" />
		</td>
	</tr>
	<tr>
		<th><label for="URL">URL</label>
		</th>
		<td>
		<input type="text" name="url" id="url" value="<?php echo get_post_meta( $post->ID, "url", true ); ?>" size="55" />
		</td>
	</tr>

</table>
