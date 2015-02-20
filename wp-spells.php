<?php
/**
 * @package WP Monsters
 * @version 1.3.2
 */
/* 
This plugins uses trademarks and/or copyrights owned by Paizo Inc., which are used under Paizo's Community Use Policy. We are expressly prohibited from charging you to use or access this content. This plugins is not published, endorsed, or specifically approved by Paizo Inc. For more information about Paizo's Community Use Policy, please visit paizo.com/communityuse. For more information about Paizo Inc. and Paizo products, please visit paizo.com. 
*/
register_activation_hook( __FILE__, 'wp_spells_activate' );
function wp_spells_activate() {
    if ( ! get_option( 'wp_spells_flush_rewrite_rules_flag' ) ) {
        add_option( 'wp_spells_flush_rewrite_rules_flag', true );
    }
}

add_action( 'init', 'wp_spells_create_post_type' );
function wp_spells_create_post_type() {

	$labels = array(
		'name'               => __( 'Spells', 'wp_monsters' ),
		'singular_name'      => __( 'Spell', 'wp_monsters' ),
		'add_new'            => __( 'Add new', 'wp_monsters' ),
		'add_new_item'       => __( 'Add new spell', 'wp_monsters' ),
		'edit_item'          => __( 'Edit spell', 'wp_monsters' ),
		'new_item'           => __( 'New spell', 'wp_monsters' ),
		'all_items'          => __( 'All spells', 'wp_monsters' ),
		'view_item'          => __( 'View spell', 'wp_monsters' ),
		'search_items'       => __( 'Search spell', 'wp_monsters' ),
		'not_found'          => __( 'Spell not found', 'wp_monsters' ),
		'not_found_in_trash' => __( 'Spell not found in trash', 'wp_monsters' ),
		'parent_item_colon'  => '',
		'menu_name'          =>  __( 'Spells', 'wp_monsters' ),
	);
	$args = array(
		'labels'        => $labels,
		'description'   => __( 'Add new spell', 'wp_monsters' ),
		'public'        => true,
		'menu_position' => 6,
		'supports'      => array( 'title', 'editor'/*, 'thumbnail', 'page-attributes', 'excerpt'*/ ),
		'rewrite'	=> array('slug' => 'spells/%spells%','with_front' => false),
		'has_archive'   => true,
		'hierarchical'	=> true,
		'show_in_menu'  => 'admin.php?page=wp-monsters/wp-monsters.php',
		'show_in_nav_menus'   => true,
		'menu_icon'	=> plugin_dir_url( __FILE__ ).'img/spell.png'
	);
	register_post_type( 'spell', $args );
}

add_action( 'init', 'wp_spells_create_category' );

function wp_spells_create_category() {
	$labels = array(
		'name'              => __( 'Type of spells', 'wp_monsters' ),
		'singular_name'     => __( 'Type of spells', 'wp_monsters' ),
		'search_items'      => __( 'Search type of spells', 'wp_monsters' ),
		'all_items'         => __( 'All type of spells', 'wp_monsters' ),
		'parent_item'       => __( 'Parent type of spells', 'wp_monsters' ),
		'parent_item_colon' => __( 'Parent type of spells:', 'wp_monsters' ),
		'edit_item'         => __( 'Edit type of spells', 'wp_monsters' ),
		'update_item'       => __( 'Update type of spells', 'wp_monsters' ),
		'add_new_item'      => __( 'Add new type of spells', 'wp_monsters' ),
		'new_item_name'     => __( 'New type of spells', 'wp_monsters' ),
		'menu_name'         => __( 'Type of spells', 'wp_monsters' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' 	=> true,
		//'public'		=> true,
		'query_var'		=> true,
		'show_in_nav_menus'   => true,
		//slug prodotto deve coincidere con il primo parametro dello slug del Custom Post Type correlato
		'rewrite'		=>  array('slug' => 'spells' ),
		//'_builtin'		=> false,
	);
	register_taxonomy( 'spells', 'spell', $args );
}

add_filter('post_link', 'wp_spells_permalink', 1, 3);
add_filter('post_type_link', 'wp_spells_permalink', 1, 3);

function wp_spells_permalink($permalink, $post_id, $leavename) {
    if (strpos($permalink, '%spells%') === FALSE) return $permalink;
        // Get post
        $post = get_post($post_id);
        if (!$post) return $permalink;

        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'spells');
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0]))
        	$taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'general';

    return str_replace('%spells%', $taxonomy_slug, $permalink);
}

add_action( 'init', 'wp_spells_flush_rewrite_rules_maybe', 20 );
function wp_spells_flush_rewrite_rules_maybe() {
	    if ( get_option( 'wp_spells_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'wp_spells_flush_rewrite_rules_flag' );
	    }
}


//SHORTCODE --------------------------------------
function wp_spells_add_spell_shortcode() {
    add_meta_box(
        'shortcode', // $id
        __('Shortcode', 'wp_monsters'), // $title 
        'wp_spells_show_spell_shortcode', // $callback
        'spell', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_spells_add_spell_shortcode');

function wp_spells_show_spell_shortcode() { //Show box
	global $post;
	echo "[spell id=\"".$post->ID."\" name=\"".$post->post_name."\"]";
}

//TYPE --------------------------------------
function wp_spells_add_spell_type() {
    add_meta_box(
        'type', // $id
        __('SPELL', 'wp_monsters'), // $title 
        'wp_spells_show_spell_type', // $callback
        'spell', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_spells_add_spell_type');

function wp_spells_show_spell_type() { //Show box
	global $post;

	$srs = array (__("Yes", 'wp_monsters'), __("No", 'wp_monsters'));

	$times = array (__("Swift Action", 'wp_monsters'), __("Inmediate Action", 'wp_monsters'), __("Standard Action", 'wp_monsters'), __("View Description", 'wp_monsters'));	

	?><table width="100%">
		<tr><td><?php _e('School', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="school" value="<?php echo get_post_meta( $post->ID, 'school', true ); ?>" /></td></tr>
		<tr><td><?php _e('Level', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="level" value="<?php echo get_post_meta( $post->ID, 'level', true ); ?>" /></td></tr>
		<tr><td><?php _e('Casting time', 'wp_monsters'); ?></td><td>
		<select name='castingtime'>
		<?php 
			foreach ($times as $key => $time) { ?> 
				<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'castingtime', true ) == $key) echo " selected='selected'"; ?>><?php echo $time; ?></option>
			<?php }
		?>
		</select>
		</td></tr>
		<tr><td><?php _e('Components', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="components" value="<?php echo get_post_meta( $post->ID, 'components', true ); ?>" /></td></tr>
		<tr><td><?php _e('Range', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="range" value="<?php echo get_post_meta( $post->ID, 'range', true ); ?>" /></td></tr>
		<tr><td><?php _e('Target', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="target" value="<?php echo get_post_meta( $post->ID, 'target', true ); ?>" /></td></tr>
		<tr><td><?php _e('Duration', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="duration" value="<?php echo get_post_meta( $post->ID, 'duration', true ); ?>" /></td></tr>
		<tr><td><?php _e('Saving Throw', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="savingthrow" value="<?php echo get_post_meta( $post->ID, 'savingthrow', true ); ?>" /></td></tr>
		<tr><td><?php _e('Spell Resistance', 'wp_monsters'); ?></td><td>
		<select name='sr'>
		<?php 
			foreach ($srs as $key => $sr) { ?> 
				<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'sr', true ) == $key) echo " selected='selected'"; ?>><?php echo $sr; ?></option>
			<?php }
		?>
		</select>
	</td></tr>
	</table><?php
}

function wp_spells_save_spell_type( $post_id ) { //Save changes
	if (isset($_POST['school'])) update_post_meta( $post_id, 'school', sanitize_text_field( $_POST['school'] ) );
	if (isset($_POST['level'])) update_post_meta( $post_id, 'level', sanitize_text_field( $_POST['level'] ) );
	if (isset($_POST['castingtime'])) update_post_meta( $post_id, 'castingtime', sanitize_text_field( $_POST['castingtime'] ) );
	if (isset($_POST['components'])) update_post_meta( $post_id, 'components', sanitize_text_field( $_POST['components'] ) );
	if (isset($_POST['range'])) update_post_meta( $post_id, 'range', sanitize_text_field( $_POST['range'] ) );
	if (isset($_POST['target'])) update_post_meta( $post_id, 'target', sanitize_text_field( $_POST['target'] ) );
	if (isset($_POST['duration'])) update_post_meta( $post_id, 'duration', sanitize_text_field( $_POST['duration'] ) );
	if (isset($_POST['savingthrow'])) update_post_meta( $post_id, 'savingthrow', sanitize_text_field( $_POST['savingthrow'] ) );
	if (isset($_POST['sr'])) update_post_meta( $post_id, 'sr', sanitize_text_field( $_POST['sr'] ) );
}
add_action( 'save_post', 'wp_spells_save_spell_type' );

//ADD SHORTCODE TO COLUMNS --------------------------------------
function wp_spells_set_columns($columns) {
	$columns['shortcode'] = __( 'Shortcode', 'wp_monsters');
	$columns['type'] = __( 'Type', 'wp_monsters');
      	unset( $columns['date'] );
      	return $columns;
}
add_filter( 'manage_edit-spell_columns', 'wp_spells_set_columns' );

function wp_spells_set_columns_info( $column ) {
	if ($column == 'shortcode') {
		global $post; 
		echo "[spell id=\"".$post->ID."\" name=\"".$post->post_name."\"]";
	} else 	if ($column == 'type') {
		global $post; 
		$terms = get_the_terms( $post->ID, 'spells' );
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
add_action( 'manage_spell_posts_custom_column' , 'wp_spells_set_columns_info');

//Metemos el filtrado por categoria
add_action('restrict_manage_posts','wp_spells_restrict');
function wp_spells_restrict() {
	global $typenow;
	global $wp_query;
	if ($typenow=='spell') {
		$taxonomy = 'spells';
		wp_monsters_taxonomy_dropdown($taxonomy);
	}
}
add_filter('parse_query','wp_spells_term_in_query');
function wp_spells_term_in_query($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	if ($pagenow=='edit.php') {
		if(isset($qv['taxonomy']) && $qv['taxonomy']=='spells' && isset($qv['term']) && is_numeric($qv['term']) &&$qv['term']>0) { 
			$term = get_term_by('id',$qv['term'],'spells');
			$qv['term'] = $term->slug;
		}
	}
}


//SHORTCODE ---------------------------------------------
function spell_shortcode( $atts ) {
	$post = get_post( $atts['id'] );

	$srs = array (__("Yes", 'wp_monsters'), __("No", 'wp_monsters'));
	$times = array (__("Swift Action", 'wp_monsters'), __("Inmediate Action", 'wp_monsters'), __("Standard Action", 'wp_monsters'), __("View Description", 'wp_monsters'));	

	if ($atts['title'] != 'no') $html = "<h3>".apply_filters('the_title', $post->post_title)."</h3>";
	$template = "<table class='wp-spells'>
			<tbody>
				<tr>
					<td><b>".__('School', 'wp_monsters')."</b></td>
					<td>[school]</td>
				</tr>
				<tr>
					<td><b>".__('Level', 'wp_monsters')."</b></td>
					<td>[level]</td>
				</tr>
				<tr>
					<td><b>".__('Casting time', 'wp_monsters')."</b></td>
					<td>[castingtime]</td>
				</tr>
				<tr>
					<td><b>".__('Components', 'wp_monsters')."</b></td>
					<td>[components]</td>
				</tr>
				<tr>
					<td><b>".__('Range', 'wp_monsters')."</b></td>
					<td>[range]</td>
				</tr>
				<tr>
					<td><b>".__('Target', 'wp_monsters')."</b></td>
					<td>[target]</td>
				</tr>
				<tr>
					<td><b>".__('Duration', 'wp_monsters')."</b></td>
					<td>[duration]</td>
				</tr>
				<tr>
					<td><b>".__('Saving Throw', 'wp_monsters')."</b></td>
					<td>[savingthrow]</td>
				</tr>
				<tr>
					<td><b>".__('Spell Resistance', 'wp_monsters')."</b></td>
					<td>[sr]</td>
				</tr>
			</tbody>
		</table>";
	$codes = array("school", "level", "castingtime", "components", "range", "target", "duration", "savingthrow", "sr" );
	foreach($codes as $code) {
		$data = get_post_meta( $post->ID, $code, true );
		if ($code == 'sr') $data = $srs[$data];	
		if ($code == 'castingtime') $data = $times[$data];
		if ($data == '')  $data = "--";
		$template = str_replace("[".$code."]", $data, $template);
	} 

	$html .= $template;
	if ($atts['description'] != 'no') $html .= "<p>".apply_filters('the_title', $post->post_content)."</p>";
	return $html;
}
add_shortcode( 'spell', 'spell_shortcode' );

?>
