<?php
/**
 * @package WP Monsters
 * @version 1.3.3
 */

/* 
This plugins uses trademarks and/or copyrights owned by Paizo Inc., which are used under Paizo's Community Use Policy. We are expressly prohibited from charging you to use or access this content. This plugins is not published, endorsed, or specifically approved by Paizo Inc. For more information about Paizo's Community Use Policy, please visit paizo.com/communityuse. For more information about Paizo Inc. and Paizo products, please visit paizo.com. 
*/
register_activation_hook( __FILE__, 'wp_traps_activate' );
function wp_traps_activate() {
    if ( ! get_option( 'wp_traps_flush_rewrite_rules_flag' ) ) {
        add_option( 'wp_traps_flush_rewrite_rules_flag', true );
    }
}

add_action( 'init', 'wp_traps_create_post_type' );
function wp_traps_create_post_type() {

	$labels = array(
		'name'               => __( 'Traps', 'wp_monsters' ),
		'singular_name'      => __( 'Trap', 'wp_monsters' ),
		'add_new'            => __( 'Add new', 'wp_monsters' ),
		'add_new_item'       => __( 'Add new trap', 'wp_monsters' ),
		'edit_item'          => __( 'Edit trap', 'wp_monsters' ),
		'new_item'           => __( 'New trap', 'wp_monsters' ),
		'all_items'          => __( 'All traps', 'wp_monsters' ),
		'view_item'          => __( 'View trap', 'wp_monsters' ),
		'search_items'       => __( 'Search trap', 'wp_monsters' ),
		'not_found'          => __( 'Trap not found', 'wp_monsters' ),
		'not_found_in_trash' => __( 'Trap not found in trash', 'wp_monsters' ),
		'parent_item_colon'  => '',
		'menu_name'          =>  __( 'Traps', 'wp_monsters' ),
	);
	$args = array(
		'labels'        => $labels,
		'description'   => __( 'Add new trap', 'wp_monsters' ),
		'public'        => true,
		'menu_position' => 6,
		'taxonomies' 	=> array( 'traps'),
		'supports'      => array( 'title', 'editor', 'thumbnail'/*, 'page-attributes', 'excerpt'*/ ),
		'rewrite'	=> array('slug' => 'traps/%traps%','with_front' => false),
		'query_var'	=> true,
		'has_archive'   => true,
		'hierarchical'	=> true,
		'show_in_menu'  => 'admin.php?page=wp-monsters/wp-monsters.php',
		'show_in_nav_menus'   => true,
		'menu_icon'	=> plugin_dir_url( __FILE__ ).'img/trap.png'
	);
	register_post_type( 'trap', $args );
}

add_action( 'init', 'wp_traps_create_category' );

function wp_traps_create_category() {
	$labels = array(
		'name'              => __( 'Type of traps', 'wp_monsters' ),
		'singular_name'     => __( 'Type of traps', 'wp_monsters' ),
		'search_items'      => __( 'Search type of traps', 'wp_monsters' ),
		'all_items'         => __( 'All type of traps', 'wp_monsters' ),
		'parent_item'       => __( 'Parent type of traps', 'wp_monsters' ),
		'parent_item_colon' => __( 'Parent type of traps:', 'wp_monsters' ),
		'edit_item'         => __( 'Edit type of traps', 'wp_monsters' ),
		'update_item'       => __( 'Update type of traps', 'wp_monsters' ),
		'add_new_item'      => __( 'Add new type of traps', 'wp_monsters' ),
		'new_item_name'     => __( 'New type of traps', 'wp_monsters' ),
		'menu_name'         => __( 'Type of traps', 'wp_monsters' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' 	=> true,
		//'public'		=> true,
		'query_var'		=> true,
		'show_in_nav_menus'   => true,
		//slug prodotto deve coincidere con il primo parametro dello slug del Custom Post Type correlato
		'rewrite'		=>  array('slug' => 'traps' ),
		'show_admin_column' => true,
		//'_builtin'		=> false,
	);
	register_taxonomy( 'traps', 'trap', $args );
}

add_filter('post_link', 'wp_traps_permalink', 1, 3);
add_filter('post_type_link', 'wp_traps_permalink', 1, 3);

function wp_traps_permalink($permalink, $post_id, $leavename) {
    if (strpos($permalink, '%traps%') === FALSE) return $permalink;
        // Get post
        $post = get_post($post_id);
        if (!$post) return $permalink;

        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'traps');
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0]))
        	$taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'general';

    return str_replace('%traps%', $taxonomy_slug, $permalink);
}

add_action( 'init', 'wp_traps_flush_rewrite_rules_maybe', 20 );
function wp_traps_flush_rewrite_rules_maybe() {
	    if ( get_option( 'wp_traps_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'wp_traps_flush_rewrite_rules_flag' );
	    }
}

//SHORTCODE --------------------------------------
function wp_traps_add_trap_shortcode() {
    add_meta_box(
        'shortcode', // $id
        __('Shortcode', 'wp_monsters'), // $title 
        'wp_traps_show_trap_shortcode', // $callback
        'trap', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_traps_add_trap_shortcode');

function wp_traps_show_trap_shortcode() { //Show box
	global $post;
	echo "[trap id=\"".$post->ID."\" name=\"".$post->post_name."\" title=\"yes\" description=\"yes\" image=\"yes\"]";
}

//TYPE --------------------------------------
function wp_traps_add_trap_type() {
    add_meta_box(
        'type', // $id
        __('GENERAL', 'wp_monsters'), // $title 
        'wp_traps_show_trap_type', // $callback
        'trap', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_traps_add_trap_type');

function wp_traps_show_trap_type() { //Show box
	global $post;

	$types = array (__("Mechanical", 'wp_monsters'), __("Magical", 'wp_monsters'), __("Mechanical and magical", 'wp_monsters'));
	$bypasses = array (__("No", 'wp_monsters'), __("Lock (DC 30 Disable Device)", 'wp_monsters'), __("Hidden Switch (DC 25 Perception)", 'wp_monsters'), __("Hidden Lock (DC 25 Perception / DC 30 Disable Device)", 'wp_monsters'));

	?><table width="100%">
		<tr><td><?php _e('Type', 'wp_monsters'); ?></td><td>
			<select name='type'>
			<?php 
				foreach ($types as $key => $type) { ?> 
					<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'type', true ) == $key) echo " selected='selected'"; ?>><?php echo $type; ?></option>
				<?php }
			?>
			</select>
		</td></tr>
		<tr><td><?php _e('CR', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 20, 1, 'cr', get_post_meta( $post->ID, 'cr', true )); ?></td></tr>
		<tr><td><?php _e('XP', 'wp_monsters'); ?></td><td><input type="text" name="xp" value="<?php echo get_post_meta( $post->ID, 'xp', true ); ?>" /></td></tr>
		<tr><td><?php _e('Bypass', 'wp_monsters'); ?></td><td>
			<select name='bypass'>
			<?php 
				foreach ($bypasses as $key => $bypass) { ?> 
					<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'bypass', true ) == $key) echo " selected='selected'"; ?>><?php echo $bypass; ?></option>
				<?php }
			?>
			</select>
		</td></tr>
		<tr><td><?php _e('Perception DC', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (1, 100, 1, 'perception', get_post_meta( $post->ID, 'perception', true )); ?></td></tr>
		<tr><td><?php _e('Disable Device DC', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (1, 100, 1, 'disable', get_post_meta( $post->ID, 'disable', true )); ?></td></tr>
	</table><?php
}

function wp_traps_save_trap( $post_id ) { //Save changes
	if (isset($_POST['cr'])) update_post_meta( $post_id, 'cr', sanitize_text_field( $_POST['cr'] ) );
	if (isset($_POST['xp'])) update_post_meta( $post_id, 'xp', sanitize_text_field( $_POST['xp'] ) );
	if (isset($_POST['bypass'])) update_post_meta( $post_id, 'bypass', sanitize_text_field( $_POST['bypass'] ) );
	if (isset($_POST['perception'])) update_post_meta( $post_id, 'perception', sanitize_text_field( $_POST['perception'] ) );
	if (isset($_POST['disable'])) update_post_meta( $post_id, 'disable', sanitize_text_field( $_POST['disable'] ) );
}
add_action( 'save_post', 'wp_traps_save_trap' );

//EFFECTS --------------------------------------
function wp_traps_add_trap_effect() {
    add_meta_box(
        'effect', // $id
        __('EFFECTS', 'wp_monsters'), // $title 
        'wp_traps_show_trap_effect', // $callback
        'trap', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_traps_add_trap_effect');

function wp_traps_show_trap_effect() { //Show box
	global $post;

	$triggers = array (__("Location", 'wp_monsters'), __("Sound", 'wp_monsters'), __("Visual", 'wp_monsters'), __("Touch", 'wp_monsters'), __("Timed", 'wp_monsters'), __("Spell", 'wp_monsters'));

	$resets = array (__("No Reset", 'wp_monsters'), __("Repair", 'wp_monsters'), __("Manual", 'wp_monsters'), __("Automatic", 'wp_monsters'));

	?><table width="100%">
		<tr><td><?php _e('Trigger', 'wp_monsters'); ?></td><td>
			<select name='trigger'>
			<?php 
				foreach ($triggers as $key => $trigger) { ?> 
					<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'trigger', true ) == $key) echo " selected='selected'"; ?>><?php echo $trigger; ?></option>
				<?php }
			?>
			</select>
		</td></tr>
		<tr><td><?php _e('Duration', 'wp_monsters'); ?></td><td><input type="text" name="duration" value="<?php echo get_post_meta( $post->ID, 'duration', true ); ?>" /></td></tr>
		<tr><td><?php _e('Reset', 'wp_monsters'); ?></td><td>
			<select name='reset'>
			<?php 
				foreach ($resets as $key => $reset) { ?> 
					<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'reset', true ) == $key) echo " selected='selected'"; ?>><?php echo $reset; ?></option>
				<?php }
			?>
			</select>
		</td></tr>
		<tr><td><?php _e('Reset automatic time', 'wp_monsters'); ?></td><td><input type="text" name="resetautomatictime" value="<?php echo get_post_meta( $post->ID, 'resetautomatictime', true ); ?>" /></td></tr>
		<tr><td><?php _e('Effect', 'wp_monsters'); ?></td><td><textarea name="effect" style="width: 100%;"><?php echo get_post_meta( $post->ID, 'effect', true ); ?></textarea></td></tr>
	</table><?php
}

function wp_traps_save_trap_effect( $post_id ) { //Save changes
	if (isset($_POST['trigger'])) update_post_meta( $post_id, 'trigger', sanitize_text_field( $_POST['trigger'] ) );
	if (isset($_POST['effect'])) update_post_meta( $post_id, 'effect', sanitize_text_field( $_POST['effect'] ) );
	if (isset($_POST['duration'])) update_post_meta( $post_id, 'duration', sanitize_text_field( $_POST['duration'] ) );
	if (isset($_POST['reset'])) update_post_meta( $post_id, 'reset', sanitize_text_field( $_POST['reset'] ) );
	if (isset($_POST['resetautomatictime'])) update_post_meta( $post_id, 'resetautomatictime', sanitize_text_field( $_POST['resetautomatictime'] ) );
}
add_action( 'save_post', 'wp_traps_save_trap_effect' );

//ADD SHORTCODE AND CATEGORY TO COLUMNS --------------------------------------
function wp_traps_set_columns($columns) {
	$columns['shortcode'] = __( 'Shortcode', 'wp_monsters');
	$columns['traps'] = __( 'Type of traps', 'wp_monsters');
      	unset( $columns['date'] );
      	return $columns;
}
add_filter( 'manage_edit-trap_columns', 'wp_traps_set_columns' );

function wp_traps_set_columns_info( $column ) {
	if ($column == 'shortcode') {
		global $post; 
		echo "[trap id=\"".$post->ID."\" name=\"".$post->post_name."\" title=\"yes\" description=\"yes\" image=\"yes\"]";
	} else 	if ($column == 'traps') {
		global $post; 
		$terms = get_the_terms( $post->ID, 'traps' );
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
add_action( 'manage_trap_posts_custom_column' , 'wp_traps_set_columns_info');

//Metemos el filtrado por categoria
add_action('restrict_manage_posts','wp_traps_restrict');
function wp_traps_restrict() {
	global $typenow;
	global $wp_query;
	if ($typenow=='trap') {
		$taxonomy = 'traps';
		wp_monsters_taxonomy_dropdown($taxonomy);
	}
}
add_filter('parse_query','wp_traps_term_in_query');
function wp_traps_term_in_query($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	if ($pagenow=='edit.php') {
		if(isset($qv['taxonomy']) && $qv['taxonomy']=='traps' && isset($qv['term']) && is_numeric($qv['term']) &&$qv['term']>0) { 
			$term = get_term_by('id',$qv['term'],'traps');
			$qv['term'] = $term->slug;
		}
	}
}

//SHORTCODE ---------------------------------------------
function trap_shortcode( $atts ) {
	$post = get_post( $atts['id'] );

	$types = array (__("Mechanical", 'wp_monsters'), __("Magical", 'wp_monsters'), __("Mechanical and magical", 'wp_monsters'));
	$triggers = array (__("Location", 'wp_monsters'), __("Sound", 'wp_monsters'), __("Visual", 'wp_monsters'), __("Touch", 'wp_monsters'), __("Timed", 'wp_monsters'), __("Spell", 'wp_monsters'));
	$resets = array (__("No Reset", 'wp_monsters'), __("Repair", 'wp_monsters'), __("Manual", 'wp_monsters'), __("Automatic", 'wp_monsters'));
	$bypasses = array (__("No", 'wp_monsters'), __("Lock (DC 30 Disable Device)", 'wp_monsters'), __("Hidden Switch (DC 25 Perception)", 'wp_monsters'), __("Hidden Lock (DC 25 Perception / DC 30 Disable Device)", 'wp_monsters'));	

	if ($atts['title'] != 'no') $html = "<h3>".apply_filters('the_title', $post->post_title)."</h3>";
	if ($atts['image'] != 'no' && has_post_thumbnail($post->ID) ) $html .= get_the_post_thumbnail($post->ID, 'medium', array('class' => "alignleft") );
	if ($atts['description'] != 'no') $html .= "<div>".apply_filters('the_content', $post->post_content)."</div>";
	$template = "<table class='wp-traps'>
		<tr>
			<th>".apply_filters('the_title', $post->post_title)."</th>
			<th><b>".__('CR', 'wp_monsters')."</b> [cr]</th>
		</tr>
		<tr>
			<td><b>".__('Type', 'wp_monsters')."</b></td><td>[type]</td>
		</tr>
		<tr>
			<td><b>".__('XP', 'wp_monsters')."</b></td><td>[xp]</td>
		</tr>
		<tr>
			<td><b>".__('Bypass', 'wp_monsters')."</b></td><td>[bypass]</td>
		</tr>
		<tr>
			<td><b>".__('Perception DC', 'wp_monsters')."</b></td><td>[perception]</td>
		</tr>
		<tr>
			<td><b>".__('Disable Device DC', 'wp_monsters')."</b></td><td>[disable]</td>
		</tr>
		<tr>
			<th colspan='2'>".__('EFFECTS', 'wp_monsters')."</th>
		</tr>
		<tr>
			<td><b>".__('Trigger', 'wp_monsters')."</b></td><td colspan='4'>[trigger]</td>
		</tr>
		<tr>
			<td><b>".__('Reset', 'wp_monsters')."</b></td><td colspan='4'>[reset] [resetautomatictime]</td>
		</tr>
		<tr>
			<td><b>".__('Effect', 'wp_monsters')."</b></td><td colspan='4'>[effect]</td>
		</tr>

	</table>";

	$codes = array('type', 'cr', 'xp', 'bypass', 'perception', 'disable', 'trigger', 'effect', 'reset', 'resetautomatictime', 'duration');
	foreach($codes as $code) {
		$data = get_post_meta( $post->ID, $code, true );
		if ($code == 'type') $data = $types[$data];
		if ($code == 'bypass') $data = $bypasses[$data];
		if ($code == 'trigger') $data = $triggers[$data];
		if ($code == 'reset') $data = $resets[$data];
		if ($code == 'resetautomatictime' && $data == '') $data = "";
		else if ($data == '')  $data = "--";
		$template = str_replace("[".$code."]", $data, $template);
	} 

	$html .= $template;
	$html .= "";
	return $html;
}
add_shortcode( 'trap', 'trap_shortcode' );

?>
