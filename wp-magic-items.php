<?php
/**
 * @package WP Monsters
 * @version 1.1
 */
/* 
This plugins uses trademarks and/or copyrights owned by Paizo Inc., which are used under Paizo's Community Use Policy. We are expressly prohibited from charging you to use or access this content. This plugins is not published, endorsed, or specifically approved by Paizo Inc. For more information about Paizo's Community Use Policy, please visit paizo.com/communityuse. For more information about Paizo Inc. and Paizo products, please visit paizo.com. 
*/
/*

*/

register_activation_hook( __FILE__, 'wp_magic_items_activate' );
function wp_magic_items_activate() {
    if ( ! get_option( 'wp_magic_items_flush_rewrite_rules_flag' ) ) {
        add_option( 'wp_magic_items_flush_rewrite_rules_flag', true );
    }
}

add_action( 'init', 'wp_magic_items_create_post_type' );
function wp_magic_items_create_post_type() {

	$labels = array(
		'name'               => __( 'Magic items', 'wp_monsters' ),
		'singular_name'      => __( 'Magic item', 'wp_monsters' ),
		'add_new'            => __( 'Add new', 'wp_monsters' ),
		'add_new_item'       => __( 'Add new magic item', 'wp_monsters' ),
		'edit_item'          => __( 'Edit magic item', 'wp_monsters' ),
		'new_item'           => __( 'New magic item', 'wp_monsters' ),
		'all_items'          => __( 'All magic items', 'wp_monsters' ),
		'view_item'          => __( 'View magic item', 'wp_monsters' ),
		'search_items'       => __( 'Search magic item', 'wp_monsters' ),
		'not_found'          => __( 'Magic item not found', 'wp_monsters' ),
		'not_found_in_trash' => __( 'Magic item not found in trash', 'wp_monsters' ),
		'parent_item_colon'  => '',
		'menu_name'          =>  __( 'Magic items', 'wp_monsters' ),
	);
	$args = array(
		'labels'        => $labels,
		'description'   => __( 'Add new magic item', 'wp_monsters' ),
		'public'        => true,
		'menu_position' => 6,
		'taxonomies' 	=> array( 'magic_items'),
		'supports'      => array( 'title', 'editor', 'thumbnail'/*, 'page-attributes', 'excerpt'*/ ),
		'rewrite'	=> array('slug' => 'magic-items/%magic_items%','with_front' => false),
		'query_var'	=> true,
		'has_archive'   => true,
		'hierarchical'	=> true,
		'menu_icon'	=> plugin_dir_url( __FILE__ ).'img/magic_item.png'
	);
	register_post_type( 'magic_item', $args );
}

add_action( 'init', 'wp_magic_items_create_category' );

function wp_magic_items_create_category() {
	$labels = array(
		'name'              => __( 'Type of magic items', 'wp_monsters' ),
		'singular_name'     => __( 'Type of magic items', 'wp_monsters' ),
		'search_items'      => __( 'Search type of magic items', 'wp_monsters' ),
		'all_items'         => __( 'All type of magic items', 'wp_monsters' ),
		'parent_item'       => __( 'Parent type of magic items', 'wp_monsters' ),
		'parent_item_colon' => __( 'Parent type of magic items:', 'wp_monsters' ),
		'edit_item'         => __( 'Edit type of magic items', 'wp_monsters' ),
		'update_item'       => __( 'Update type of magic items', 'wp_monsters' ),
		'add_new_item'      => __( 'Add new type of magic items', 'wp_monsters' ),
		'new_item_name'     => __( 'New type of magic items', 'wp_monsters' ),
		'menu_name'         => __( 'Type of magic items', 'wp_monsters' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' 	=> true,
		//'public'		=> true,
		'query_var'		=> true,
		//slug prodotto deve coincidere con il primo parametro dello slug del Custom Post Type correlato
		'rewrite'		=>  array('slug' => 'magic-items' ),
		//'_builtin'		=> false,
	);
	register_taxonomy( 'magic_items', 'magic_item', $args );
}

add_filter('post_link', 'wp_magic_items_permalink', 1, 3);
add_filter('post_type_link', 'wp_magic_items_permalink', 1, 3);

function wp_magic_items_permalink($permalink, $post_id, $leavename) {
    if (strpos($permalink, '%magic_items%') === FALSE) return $permalink;
        // Get post
        $post = get_post($post_id);
        if (!$post) return $permalink;

        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'magic_items');
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0]))
        	$taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'general';

    return str_replace('%magic_items%', $taxonomy_slug, $permalink);
}

add_action( 'init', 'wp_magic_items_flush_rewrite_rules_maybe', 20 );
function wp_magic_items_flush_rewrite_rules_maybe() {
	    if ( get_option( 'wp_magic_items_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'wp_magic_items_flush_rewrite_rules_flag' );
	    }
}

//SHORTCODE --------------------------------------
function wp_magic_items_add_magic_item_shortcode() {
    add_meta_box(
        'shortcode', // $id
        __('Shortcode', 'wp_monsters'), // $title 
        'wp_magic_items_show_magic_item_shortcode', // $callback
        'magic_item', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_magic_items_add_magic_item_shortcode');

function wp_magic_items_show_magic_item_shortcode() { //Show box
	global $post;
	echo "[magic_item id=\"".$post->ID."\" name=\"".$post->post_name."\" title=\"yes\" description=\"yes\" image=\"yes\"]";
}

//TYPE --------------------------------------
function wp_magic_items_add_magic_item_type() {
    add_meta_box(
        'type', // $id
        __('MAGIC', 'wp_monsters'), // $title 
        'wp_magic_items_show_magic_item_type', // $callback
        'magic_item', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_magic_items_add_magic_item_type');

function wp_magic_items_show_magic_item_type() { //Show box
	global $post;

	$slots = array (__("Armor", 'wp_monsters'), __("Belts", 'wp_monsters'), __("Bod", 'wp_monsters'), __("Chest", 'wp_monsters'), __("Eyes", 'wp_monsters'), __("Feet", 'wp_monsters'), __("Hands", 'wp_monsters'), __("Head", 'wp_monsters'), __("Headband", 'wp_monsters'), __("Neck", 'wp_monsters'), __("Ring", 'wp_monsters'), __("Shield", 'wp_monsters'), __("Shoulders", 'wp_monsters'), __("Wrists", 'wp_monsters'), __("Slotless", 'wp_monsters'));

	?><table width="100%">
		<tr><td><?php _e('Cost', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="cost" value="<?php echo get_post_meta( $post->ID, 'cost', true ); ?>" /></td></tr>
		<tr><td><?php _e('Slot', 'wp_monsters'); ?></td><td>
		<select name='slot'>
		<?php 
			foreach ($slots as $key => $slot) { ?> 
				<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'slot', true ) == $key) echo " selected='selected'"; ?>><?php echo $slot; ?></option>
			<?php }
		?>
		</select>
		</td></tr>
		<tr><td><?php _e('Weight', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="weight" value="<?php echo get_post_meta( $post->ID, 'weight', true ); ?>" /></td></tr>
		<tr><td><?php _e('CL', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (1, 10, 1, 'cl', get_post_meta( $post->ID, 'cl', true ), false); ?></td></tr>
		<tr><td><?php _e('Aura', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="aura" value="<?php echo get_post_meta( $post->ID, 'aura', true ); ?>" /></td></tr>
		<tr><td><?php _e('Construction cost', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="constructioncost" value="<?php echo get_post_meta( $post->ID, 'constructioncost', true ); ?>" /></td></tr>
		<tr><td><?php _e('Construction requeriments', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="constructionrequirements" value="<?php echo get_post_meta( $post->ID, 'constructionrequirements', true ); ?>" /></td></tr>
	</td></tr>
	</table><?php
}

function wp_magic_items_save_magic_item_type( $post_id ) { //Save changes
	if (isset($_POST['cost'])) update_post_meta( $post_id, 'cost', sanitize_text_field( $_POST['cost'] ) );
	if (isset($_POST['weight'])) update_post_meta( $post_id, 'weight', sanitize_text_field( $_POST['weight'] ) );
	if (isset($_POST['slot'])) update_post_meta( $post_id, 'slot', sanitize_text_field( $_POST['slot'] ) );	
	if (isset($_POST['cl'])) update_post_meta( $post_id, 'cl', sanitize_text_field( $_POST['cl'] ) );
	if (isset($_POST['aura'])) update_post_meta( $post_id, 'aura', sanitize_text_field( $_POST['aura'] ) );
	if (isset($_POST['constructioncost'])) update_post_meta( $post_id, 'constructioncost', sanitize_text_field( $_POST['constructioncost'] ) );
	if (isset($_POST['constructionrequirements'])) update_post_meta( $post_id, 'constructionrequirements', sanitize_text_field( $_POST['constructionrequirements'] ) );
}
add_action( 'save_post', 'wp_magic_items_save_magic_item_type' );

//ADD SHORTCODE AND CATEGORY TO COLUMNS --------------------------------------
function wp_magic_items_set_columns($columns) {
	$columns['shortcode'] = __( 'Shortcode', 'wp_monsters');
	$columns['magic_items'] = __( 'Type of magic items', 'wp_monsters');
      	unset( $columns['date'] );
      	return $columns;
}
add_filter( 'manage_edit-magic_item_columns', 'wp_magic_items_set_columns' );

function wp_magic_items_set_columns_info( $column ) {
	if ($column == 'shortcode') {
		global $post; 
		echo "[magic_item id=\"".$post->ID."\" name=\"".$post->post_name."\" title=\"yes\" description=\"yes\" image=\"yes\"]";
	} else 	if ($column == 'magic_items') {
		global $post; 
		$terms = get_the_terms( $post->ID, 'magic_items' );
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
add_action( 'manage_magic_item_posts_custom_column' , 'wp_magic_items_set_columns_info');

//Metemos el filtrado por categoria
add_action('restrict_manage_posts','wp_magic_items_restrict');
function wp_magic_items_restrict() {
	global $typenow;
	global $wp_query;
	if ($typenow=='magic_item') {
		$taxonomy = 'magic_items';
		wp_monsters_taxonomy_dropdown($taxonomy);
	}
}
add_filter('parse_query','wp_magic_items_term_in_query');
function wp_magic_items_term_in_query($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	if ($pagenow=='edit.php') {
		if(isset($qv['taxonomy']) && $qv['taxonomy']=='magic_items' && isset($qv['term']) && is_numeric($qv['term']) &&$qv['term']>0) { 
			$term = get_term_by('id',$qv['term'],'magic_items');
			$qv['term'] = $term->slug;
		}
	}
}

//SHORTCODE ---------------------------------------------
function magic_item_shortcode( $atts ) {
	$post = get_post( $atts['id'] );

	$html = "";
	if ($atts['title'] != 'no') $html = "<h3>".apply_filters('the_title', $post->post_title)."</h3>";
	if ($atts['image'] != 'no' && has_post_thumbnail($post->ID) ) $html .= get_the_post_thumbnail($post->ID, 'medium', array('class' => "alignleft") );

	$template = "<table class='wp-monsters'>
			<thead>
				<tr>
					<td colspan='8'><b>".apply_filters('the_title', $post->post_title)."</b></td>
				</tr>
			<thead>
			<tbody>
				<tr>
					<td colspan='2'><b>".__('Weight', 'wp_monsters')."</b></td>
					<td colspan='2'><b>".__('Slot', 'wp_monsters')."</b></td>
					<td colspan='2'><b>".__('CL', 'wp_monsters')."</b></td>
					<td colspan='2'><b>".__('Cost', 'wp_monsters')."</b></td>
				</tr>
				<tr>
					<td colspan='2'>[weight]</td>
					<td colspan='2'>[slot]</td>
					<td colspan='2'>[cl]</td>
					<td colspan='2'>[cost]</td>
				</tr>
				<tr>
					<td colspan='2'><b>".__('Aura', 'wp_monsters')."</b></td>
					<td colspan='6'>[aura]</td>
				</tr>
				<tr>
					<td colspan='2'><b>".__('Construction requeriments', 'wp_monsters')."</b></td>
					<td colspan='6'><b>".__('Construction cost', 'wp_monsters')."</b></td>

				</tr>
				<tr>
					<td colspan='2'>[constructionrequirements]</td>
					<td colspan='6'>[constructioncost]</td>
				</tr>
			</tbody>
		</table>";
	$codes = array("cost", "weight", "slot", "cl", "aura", "constructionrequirements", "constructioncost"  );
	foreach($codes as $code) {
		$data = get_post_meta( $post->ID, $code, true );
		if ($data == '')  $data = "--";
		$template = str_replace("[".$code."]", $data, $template);
	} 

	$html .= $template;
	if ($atts['description'] != 'no') $html .= apply_filters('the_content', $post->post_content);
	return $html;
}
add_shortcode( 'magic_item', 'magic_item_shortcode' );

?>
