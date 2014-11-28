<?php
/**
 * @package WP Monsters
 * @version 1.0.2
 */
/* 
This plugins uses trademarks and/or copyrights owned by Paizo Inc., which are used under Paizo's Community Use Policy. We are expressly prohibited from charging you to use or access this content. This plugins is not published, endorsed, or specifically approved by Paizo Inc. For more information about Paizo's Community Use Policy, please visit paizo.com/communityuse. For more information about Paizo Inc. and Paizo products, please visit paizo.com. 
*/
register_activation_hook( __FILE__, 'wp_feats_activate' );
function wp_feats_activate() {
    if ( ! get_option( 'wp_feats_flush_rewrite_rules_flag' ) ) {
        add_option( 'wp_feats_flush_rewrite_rules_flag', true );
    }
}

add_action( 'init', 'wp_feats_create_post_type' );
function wp_feats_create_post_type() {

	$labels = array(
		'name'               => __( 'Feats', 'wp_monsters' ),
		'singular_name'      => __( 'Feat', 'wp_monsters' ),
		'add_new'            => __( 'Add new', 'wp_monsters' ),
		'add_new_item'       => __( 'Add new feat', 'wp_monsters' ),
		'edit_item'          => __( 'Edit feat', 'wp_monsters' ),
		'new_item'           => __( 'New feat', 'wp_monsters' ),
		'all_items'          => __( 'All feats', 'wp_monsters' ),
		'view_item'          => __( 'View feat', 'wp_monsters' ),
		'search_items'       => __( 'Search feat', 'wp_monsters' ),
		'not_found'          => __( 'Feat not found', 'wp_monsters' ),
		'not_found_in_trash' => __( 'Feat not found in trash', 'wp_monsters' ),
		'parent_item_colon'  => '',
		'menu_name'          =>  __( 'Feats', 'wp_monsters' ),
	);
	$args = array(
		'labels'        => $labels,
		'description'   => __( 'Add new feat', 'wp_monsters' ),
		'public'        => true,
		'menu_position' => 6,
		'supports'      => array( 'title', 'editor'/*, 'thumbnail', 'page-attributes', 'excerpt'*/ ),
		'has_archive'   => true,
		'hierarchical'	=> true,
	);
	register_post_type( 'feat', $args );
}

add_action( 'init', 'wp_feats_flush_rewrite_rules_maybe', 20 );
function wp_feats_flush_rewrite_rules_maybe() {
	    if ( get_option( 'wp_feats_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'wp_feats_flush_rewrite_rules_flag' );
	    }
}

//SHORTCODE --------------------------------------
function wp_feats_add_feat_shortcode() {
    add_meta_box(
        'shortcode', // $id
        __('Shortcode', 'wp_monsters'), // $title 
        'wp_feats_show_feat_shortcode', // $callback
        'feat', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_feats_add_feat_shortcode');

function wp_feats_show_feat_shortcode() { //Show box
	global $post;
	echo "[feat id=\"".$post->ID."\" name=\"".$post->post_name."\"]";
}

//TYPE --------------------------------------
function wp_feats_add_feat_type() {
    add_meta_box(
        'type', // $id
        __('FEATS', 'wp_monsters'), // $title 
        'wp_feats_show_feat_type', // $callback
        'feat', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_feats_add_feat_type');

function wp_feats_show_feat_type() { //Show box
	global $post;
	?><table width="100%">
		<tr><td><?php _e('Prerequisites', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="prerequisites" value="<?php echo get_post_meta( $post->ID, 'prerequisites', true ); ?>" /></td></tr>
		<tr><td><?php _e('Benefit', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="normal" value="<?php echo get_post_meta( $post->ID, 'normal', true ); ?>" /></td></tr>
	</td></tr>
	</table><?php
}

function wp_feats_save_feat_type( $post_id ) { //Save changes
	if (isset($_POST['prerequisites'])) update_post_meta( $post_id, 'prerequisites', sanitize_text_field( $_POST['prerequisites'] ) );
	if (isset($_POST['normal'])) update_post_meta( $post_id, 'normal', sanitize_text_field( $_POST['normal'] ) );
}
add_action( 'save_post', 'wp_feats_save_feat_type' );

//ADD SHORTCODE TO COLUMNS --------------------------------------
function wp_feats_set_columns($columns) {
	$columns['shortcode'] = __( 'Shortcode', 'wp_monsters');
	$columns['type'] = __( 'Type', 'wp_monsters');
      	unset( $columns['date'] );
      	return $columns;
}
add_filter( 'manage_edit-feat_columns', 'wp_feats_set_columns' );

function wp_feats_set_columns_info( $column ) {
	if ($column == 'shortcode') {
		global $post; 
		echo "[feat id=\"".$post->ID."\" name=\"".$post->post_name."\"]";
	} else 	if ($column == 'type') {
		global $post; 
		echo get_post_meta( $post->ID, 'type', true );
	}
}
add_action( 'manage_feat_posts_custom_column' , 'wp_feats_set_columns_info');


//SHORTCODE ---------------------------------------------
function feat_shortcode( $atts ) {
	$post = get_post( $atts['id'] );

		$srs = array (__("Yes", 'wp_monsters'), __("No", 'wp_monsters'));

	if ($atts['title'] != 'no') $html = "<h3>".apply_filters('the_title', $post->post_title)."</h3>";
	$template = "<div><b>".__('Prerequisites', 'wp_monsters').":</b> [prerequisites]</div><div><b>".__('Normal', 'wp_monsters').":</b> [normal]</div>";
	if ($atts['description'] != 'no') $template .= "<div><b>".__('Benefit', 'wp_monsters').":</b> ".apply_filters('the_content', $post->post_content)."</div>";
	$codes = array("prerequisites", "normal" );
	foreach($codes as $code) {
		$data = get_post_meta( $post->ID, $code, true );
		if ($code == 'sr') $data = $srs[$data];
		if ($data == '')  $data = "--";
		$template = str_replace("[".$code."]", $data, $template);
	} 

	$html .= $template;
	$html .= "";
	return $html;
}
add_shortcode( 'feat', 'feat_shortcode' );

?>
