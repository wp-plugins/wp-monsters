<?php
/**
 * @package WP Monsters
 * @version 1.3.2
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
		'taxonomies' 	=> array( 'feats'),
		'supports'      => array( 'title', 'editor'/*, 'thumbnail', 'page-attributes', 'excerpt'*/ ),
		'rewrite'	=> array('slug' => 'feats/%feats%','with_front' => false),
		'query_var'	=> true,
		'has_archive'   => true,
		'hierarchical'	=> true,
		'show_in_menu'  => 'admin.php?page=wp-monsters/wp-monsters.php',
		'show_in_nav_menus'   => true,
		'menu_icon'	=> plugin_dir_url( __FILE__ ).'img/feat.png'
	);
	register_post_type( 'feat', $args );
}

add_action( 'init', 'wp_feats_create_category' );

function wp_feats_create_category() {
	$labels = array(
		'name'              => __( 'Type of feats', 'wp_monsters' ),
		'singular_name'     => __( 'Type of feats', 'wp_monsters' ),
		'search_items'      => __( 'Search type of feats', 'wp_monsters' ),
		'all_items'         => __( 'All type of feats', 'wp_monsters' ),
		'parent_item'       => __( 'Parent type of feats', 'wp_monsters' ),
		'parent_item_colon' => __( 'Parent type of feats:', 'wp_monsters' ),
		'edit_item'         => __( 'Edit type of feats', 'wp_monsters' ),
		'update_item'       => __( 'Update type of feats', 'wp_monsters' ),
		'add_new_item'      => __( 'Add new type of feats', 'wp_monsters' ),
		'new_item_name'     => __( 'New type of feats', 'wp_monsters' ),
		'menu_name'         => __( 'Type of feats', 'wp_monsters' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' 	=> true,
		//'public'		=> true,
		'query_var'		=> true,
		'show_in_nav_menus'   => true,
		//slug prodotto deve coincidere con il primo parametro dello slug del Custom Post Type correlato
		'rewrite'		=>  array('slug' => 'feats' ),
		//'_builtin'		=> false,
	);
	register_taxonomy( 'feats', 'feat', $args );
}

add_filter('post_link', 'wp_feats_permalink', 1, 3);
add_filter('post_type_link', 'wp_feats_permalink', 1, 3);

function wp_feats_permalink($permalink, $post_id, $leavename) {
    if (strpos($permalink, '%feats%') === FALSE) return $permalink;
        // Get post
        $post = get_post($post_id);
        if (!$post) return $permalink;

        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'feats');
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0]))
        	$taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'general';

    return str_replace('%feats%', $taxonomy_slug, $permalink);
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

//ADD SHORTCODE AND CATEGORY TO COLUMNS --------------------------------------
function wp_feats_set_columns($columns) {
	$columns['shortcode'] = __( 'Shortcode', 'wp_monsters');
	$columns['feats'] = __( 'Type of feats', 'wp_monsters');
      	unset( $columns['date'] );
      	return $columns;
}
add_filter( 'manage_edit-feat_columns', 'wp_feats_set_columns' );

function wp_feats_set_columns_info( $column ) {
	if ($column == 'shortcode') {
		global $post; 
		echo "[feat id=\"".$post->ID."\" name=\"".$post->post_name."\"]";
	} else 	if ($column == 'feats') {
		global $post; 
		$terms = get_the_terms( $post->ID, 'feats' );
			if ($terms && ! is_wp_error($terms)) :
				$term_slugs_arr = array();
				foreach ($terms as $term) {
				    $term_slugs_arr[] = $term->name;
				}
				$terms_slug_str = join( ", ", $term_slugs_arr);
			endif;
			echo $terms_slug_str;
	}
}
add_action( 'manage_feat_posts_custom_column' , 'wp_feats_set_columns_info');

//Metemos el filtrado por categoria
add_action('restrict_manage_posts','wp_feats_restrict');
function wp_feats_restrict() {
	global $typenow;
	global $wp_query;
	if ($typenow=='feat') {
		$taxonomy = 'feats';
		wp_monsters_taxonomy_dropdown($taxonomy);
	}
}
add_filter('parse_query','wp_feats_term_in_query');
function wp_feats_term_in_query($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	if ($pagenow=='edit.php') {
		if(isset($qv['taxonomy']) && $qv['taxonomy']=='feats' && isset($qv['term']) && is_numeric($qv['term']) &&$qv['term']>0) { 
			$term = get_term_by('id',$qv['term'],'feats');
			$qv['term'] = $term->slug;
		}
	}
}

//SHORTCODE ---------------------------------------------
function feat_shortcode( $atts ) {
	$post = get_post( $atts['id'] );

	if ($atts['title'] != 'no') $html = "<h3>".apply_filters('the_title', $post->post_title)."</h3>";
	$template = "<div><b>".__('Prerequisites', 'wp_monsters').":</b> [prerequisites]</div><div><b>".__('Normal', 'wp_monsters').":</b> [normal]</div>";
	if ($atts['description'] != 'no') $template .= "<div><b>".__('Benefit', 'wp_monsters').":</b> ".apply_filters('the_content', $post->post_content)."</div>";
	$codes = array("prerequisites", "normal" );
	foreach($codes as $code) {
		$data = get_post_meta( $post->ID, $code, true );
		if ($data == '')  $data = "--";
		$template = str_replace("[".$code."]", $data, $template);
	} 

	$html .= $template;
	$html .= "";
	return $html;
}
add_shortcode( 'feat', 'feat_shortcode' );

?>
