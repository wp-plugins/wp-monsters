<?php
/**
 * @package WP Monsters
 * @version 1.3.4
 */
/* 
This plugins uses trademarks and/or copyrights owned by Paizo Inc., which are used under Paizo's Community Use Policy. We are expressly prohibited from charging you to use or access this content. This plugins is not published, endorsed, or specifically approved by Paizo Inc. For more information about Paizo's Community Use Policy, please visit paizo.com/communityuse. For more information about Paizo Inc. and Paizo products, please visit paizo.com. 
*/
register_activation_hook( __FILE__, 'wp_weapons_activate' );
function wp_weapons_activate() {
    if ( ! get_option( 'wp_weapons_flush_rewrite_rules_flag' ) ) {
        add_option( 'wp_weapons_flush_rewrite_rules_flag', true );
    }
}

add_action( 'init', 'wp_weapons_create_post_type' );
function wp_weapons_create_post_type() {

	$labels = array(
		'name'               => __( 'Weapons', 'wp_monsters' ),
		'singular_name'      => __( 'Weapon', 'wp_monsters' ),
		'add_new'            => __( 'Add new', 'wp_monsters' ),
		'add_new_item'       => __( 'Add new weapon', 'wp_monsters' ),
		'edit_item'          => __( 'Edit weapon', 'wp_monsters' ),
		'new_item'           => __( 'New weapon', 'wp_monsters' ),
		'all_items'          => __( 'All weapons', 'wp_monsters' ),
		'view_item'          => __( 'View weapon', 'wp_monsters' ),
		'search_items'       => __( 'Search weapon', 'wp_monsters' ),
		'not_found'          => __( 'Weapon not found', 'wp_monsters' ),
		'not_found_in_trash' => __( 'Weapon not found in trash', 'wp_monsters' ),
		'parent_item_colon'  => '',
		'menu_name'          =>  __( 'Weapons', 'wp_monsters' ),
	);
	$args = array(
		'labels'        => $labels,
		'description'   => __( 'Add new weapon', 'wp_monsters' ),
		'public'        => true,
		'menu_position' => 6,
		'taxonomies' 	=> array( 'weapons'),
		'supports'      => array( 'title', 'editor', 'thumbnail'/*, 'page-attributes', 'excerpt'*/ ),
		'rewrite'	=> array('slug' => 'weapons/%weapons%','with_front' => false),
		'query_var'	=> true,
		'has_archive'   => true,
		'hierarchical'	=> true,
		'show_in_menu'  => 'admin.php?page=wp-monsters/wp-monsters.php',
		'show_in_nav_menus'   => true,
		'menu_icon'	=> plugin_dir_url( __FILE__ ).'img/weapon.png'
	);
	register_post_type( 'weapon', $args );
}

add_action( 'init', 'wp_weapons_create_category' );

function wp_weapons_create_category() {
	$labels = array(
		'name'              => __( 'Type of weapons', 'wp_monsters' ),
		'singular_name'     => __( 'Type of weapons', 'wp_monsters' ),
		'search_items'      => __( 'Search type of weapons', 'wp_monsters' ),
		'all_items'         => __( 'All type of weapons', 'wp_monsters' ),
		'parent_item'       => __( 'Parent type of weapons', 'wp_monsters' ),
		'parent_item_colon' => __( 'Parent type of weapons:', 'wp_monsters' ),
		'edit_item'         => __( 'Edit type of weapons', 'wp_monsters' ),
		'update_item'       => __( 'Update type of weapons', 'wp_monsters' ),
		'add_new_item'      => __( 'Add new type of weapons', 'wp_monsters' ),
		'new_item_name'     => __( 'New type of weapons', 'wp_monsters' ),
		'menu_name'         => __( 'Type of weapons', 'wp_monsters' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' 	=> true,
		//'public'		=> true,
		'query_var'		=> true,
		'show_in_nav_menus'   => true,
		//slug prodotto deve coincidere con il primo parametro dello slug del Custom Post Type correlato
		'rewrite'		=>  array('slug' => 'weapons' ),
		//'_builtin'		=> false,
	);
	register_taxonomy( 'weapons', 'weapon', $args );
}

add_filter('post_link', 'wp_weapons_permalink', 1, 3);
add_filter('post_type_link', 'wp_weapons_permalink', 1, 3);

function wp_weapons_permalink($permalink, $post_id, $leavename) {
    if (strpos($permalink, '%weapons%') === FALSE) return $permalink;
        // Get post
        $post = get_post($post_id);
        if (!$post) return $permalink;

        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'weapons');
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0]))
        	$taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'general';

    return str_replace('%weapons%', $taxonomy_slug, $permalink);
}

add_action( 'init', 'wp_weapons_flush_rewrite_rules_maybe', 20 );
function wp_weapons_flush_rewrite_rules_maybe() {
	    if ( get_option( 'wp_weapons_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'wp_weapons_flush_rewrite_rules_flag' );
	    }
}

//SHORTCODE --------------------------------------
function wp_weapons_add_weapon_shortcode() {
    add_meta_box(
        'shortcode', // $id
        __('Shortcode', 'wp_monsters'), // $title 
        'wp_weapons_show_weapon_shortcode', // $callback
        'weapon', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_weapons_add_weapon_shortcode');

function wp_weapons_show_weapon_shortcode() { //Show box
	global $post;
	echo "[weapon id=\"".$post->ID."\" name=\"".$post->post_name."\" title=\"yes\" description=\"yes\" image=\"yes\"]";
}

//TYPE --------------------------------------
function wp_weapons_add_weapon_type() {
    add_meta_box(
        'type', // $id
        __('WEAPONS', 'wp_monsters'), // $title 
        'wp_weapons_show_weapon_type', // $callback
        'weapon', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_weapons_add_weapon_type');

function wp_weapons_show_weapon_type() { //Show box
	global $post;

	//(Simple) Light Melee Weapons	Cost	Dmg (S)	Dmg (M)	Critical	Range	Weight1	Type2	Special	Source
	//B for bludgeoning, P for piercing, or S for slashing
	$generaltypes = array(__("Simple", 'wp_monsters'), __("Martial", 'wp_monsters'), __("Exotic", 'wp_monsters'));
	$subtypes = array(__("Unarmed", 'wp_monsters'), __("Light Melee", 'wp_monsters'), __("One-handed Melee", 'wp_monsters'), __("Two-handed Melee", 'wp_monsters'), __("Ranged", 'wp_monsters'), __("Ammunition", 'wp_monsters'));
	
	$criticals = array("×2", "×3", "×3/×4", "×4", "19–20/×2", "18–20/×2");
	?><table width="100%">
		<tr><td><?php _e('General type', 'wp_monsters'); ?></td><td>
			<select name='generaltype'>
			<?php 
				foreach ($generaltypes as $key => $generaltype) { ?> 
					<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'generaltype', true ) == $key) echo " selected='selected'"; ?>><?php echo $generaltype; ?></option>
				<?php }
			?>
			</select>
		</td></tr>
		<tr><td><?php _e('Subtype', 'wp_monsters'); ?></td><td>
			<select name='subtype'>
			<?php 
				foreach ($subtypes as $key => $subtype) { ?> 
					<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'subtype', true ) == $key) echo " selected='selected'"; ?>><?php echo $subtype; ?></option>
				<?php }
			?>
			</select>
		</td></tr>
		<tr><td><?php _e('Cost', 'wp_monsters'); ?></td><td><input type="text" name="cost" value="<?php echo get_post_meta( $post->ID, 'cost', true ); ?>" /></td></tr>
		<tr><td><?php _e('Dmg (S)', 'wp_monsters'); ?></td><td><input type="text" name="dmgs" value="<?php echo get_post_meta( $post->ID, 'dmgs', true ); ?>" /></td></tr>
		<tr><td><?php _e('Dmg (M)', 'wp_monsters'); ?></td><td><input type="text" name="dmgm" value="<?php echo get_post_meta( $post->ID, 'dmgm', true ); ?>" /></td></tr>
		<tr><td><?php _e('Critical', 'wp_monsters'); ?></td><td>
			<select name='critical'>
			<?php 
				foreach ($criticals as $key => $critical) { ?> 
					<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'critical', true ) == $key) echo " selected='selected'"; ?>><?php echo $critical; ?></option>
				<?php }
			?>
			</select>
		</td></tr>
		<tr><td><?php _e('Range', 'wp_monsters'); ?></td><td><input type="text" name="range" value="<?php echo get_post_meta( $post->ID, 'range', true ); ?>" /></td></tr>
		<tr><td><?php _e('Weight', 'wp_monsters'); ?></td><td><input type="text" name="weight" value="<?php echo get_post_meta( $post->ID, 'weight', true ); ?>" /></td></tr>
		<tr><td><?php _e('Type of damage', 'wp_monsters'); ?></td><td>
			<?php 
				$typedamageb = get_post_meta( $post->ID, 'typedamageb', true );
				$typedamagep = get_post_meta( $post->ID, 'typedamagep', true );
				$typedamages = get_post_meta( $post->ID, 'typedamages', true );
			?>
			<select name="typedamageb">
				<option value="0"<?php if($typedamageb == 0) echo " selected='selected'"; ?>><?php _e("No", "wp_monsters"); ?></option>
				<option value="1"<?php if($typedamageb == 1) echo " selected='selected'"; ?>><?php _e("Si", "wp_monsters"); ?></option>
			</select>  <?php _e("Bludgeoning", "wp_monsters"); ?><br/>
			<select name="typedamagep">
				<option value="0"<?php if($typedamagep == 0) echo " selected='selected'"; ?>><?php _e("No", "wp_monsters"); ?></option>
				<option value="1"<?php if($typedamagep == 1) echo " selected='selected'"; ?>><?php _e("Si", "wp_monsters"); ?></option>
			</select>  <?php _e("Piercing", "wp_monsters"); ?><br/>
			<select name="typedamages">
			<option value="0"<?php if($typedamages == 0) echo " selected='selected'"; ?>><?php _e("No", "wp_monsters"); ?></option>
				<option value="1"<?php if($typedamages == 1) echo " selected='selected'"; ?>><?php _e("Si", "wp_monsters"); ?></option>
			</select> <?php _e("Slashing", "wp_monsters"); ?>
		</td></tr>
		<tr><td colspan="2"><?php _e('Special', 'wp_monsters'); ?></td></tr>
		<tr><td colspan="2"><textarea name="special" style="width: 100%;"><?php echo get_post_meta( $post->ID, 'special', true ); ?></textarea></td></tr>
	</table><?php
}

function wp_weapons_save_weapon( $post_id ) { //Save changes
	if (isset($_POST['generaltype'])) update_post_meta( $post_id, 'generaltype', sanitize_text_field( $_POST['generaltype'] ) );
	if (isset($_POST['subtype'])) update_post_meta( $post_id, 'subtype', sanitize_text_field( $_POST['subtype'] ) );
	if (isset($_POST['cost'])) update_post_meta( $post_id, 'cost', sanitize_text_field( $_POST['cost'] ) );
	if (isset($_POST['dmgs'])) update_post_meta( $post_id, 'dmgs', sanitize_text_field( $_POST['dmgs'] ) );
	if (isset($_POST['dmgm'])) update_post_meta( $post_id, 'dmgm', sanitize_text_field( $_POST['dmgm'] ) );
	if (isset($_POST['critical'])) update_post_meta( $post_id, 'critical', sanitize_text_field( $_POST['critical'] ) );
	if (isset($_POST['range'])) update_post_meta( $post_id, 'range', sanitize_text_field( $_POST['range'] ) );
	if (isset($_POST['weight'])) update_post_meta( $post_id, 'weight', sanitize_text_field( $_POST['weight'] ) );
	if (isset($_POST['typedamageb'])) update_post_meta( $post_id, 'typedamageb', $_POST['typedamageb'] );
	if (isset($_POST['typedamagep'])) update_post_meta( $post_id, 'typedamagep', $_POST['typedamagep'] );
	if (isset($_POST['typedamages'])) update_post_meta( $post_id, 'typedamages', $_POST['typedamages'] );
	if (isset($_POST['special'])) update_post_meta( $post_id, 'special', sanitize_text_field( $_POST['special'] ) );
}
add_action( 'save_post', 'wp_weapons_save_weapon' );

//ADD SHORTCODE AND CATEGORY TO COLUMNS --------------------------------------
function wp_weapons_set_columns($columns) {
	$columns['shortcode'] = __( 'Shortcode', 'wp_monsters');
	$columns['weapons'] = __( 'Type of weapons', 'wp_monsters');
      	unset( $columns['date'] );
      	return $columns;
}
add_filter( 'manage_edit-weapon_columns', 'wp_weapons_set_columns' );

function wp_weapons_set_columns_info( $column ) {
	if ($column == 'shortcode') {
		global $post; 
		echo "[weapon id=\"".$post->ID."\" name=\"".$post->post_name."\"]";
	} else 	if ($column == 'weapons') {
		global $post; 
		$terms = get_the_terms( $post->ID, 'weapons' );
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
add_action( 'manage_weapon_posts_custom_column' , 'wp_weapons_set_columns_info');

//Metemos el filtrado por categoria
add_action('restrict_manage_posts','wp_weapons_restrict');
function wp_weapons_restrict() {
	global $typenow;
	global $wp_query;
	if ($typenow=='weapon') {
		$taxonomy = 'weapons';
		wp_monsters_taxonomy_dropdown($taxonomy);
	}
}
add_filter('parse_query','wp_weapons_term_in_query');
function wp_weapons_term_in_query($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	if ($pagenow=='edit.php') {
		if(isset($qv['taxonomy']) && $qv['taxonomy']=='weapons' && isset($qv['term']) && is_numeric($qv['term']) &&$qv['term']>0) { 
			$term = get_term_by('id',$qv['term'],'weapons');
			$qv['term'] = $term->slug;
		}
	}
}

//SHORTCODE ---------------------------------------------
function weapon_shortcode( $atts ) {
	$post = get_post( $atts['id'] );

	$generaltypes = array(__("Simple", 'wp_monsters'), __("Martial", 'wp_monsters'), __("Exotic", 'wp_monsters'));
	$subtypes = array(__("Unarmed", 'wp_monsters'), __("Light Melee", 'wp_monsters'), __("One-handed Melee", 'wp_monsters'), __("Two-handed Melee", 'wp_monsters'), __("Ranged", 'wp_monsters'), __("Ammunition", 'wp_monsters'));
	$criticals = array("×2", "×3", "×3/×4", "×4", "19–20/×2", "18–20/×2");
	
	if ($atts['title'] != 'no') $html = "<h3>".apply_filters('the_title', $post->post_title)."</h3>";
	if ($atts['image'] != 'no' && has_post_thumbnail($post->ID) ) $html .= get_the_post_thumbnail($post->ID, 'medium', array('class' => "alignleft") );
	if ($atts['description'] != 'no') $html .= "<div>".apply_filters('the_content', $post->post_content)."</div>";
	$template = "<table class='wp-weapons'>
		<tr>
			<th>".apply_filters('the_title', $post->post_title)."</th>
			<th>".__('Cost', 'wp_monsters')."</th>
			<th>".__('Dmg (S)', 'wp_monsters')."</th>
			<th>".__('Dmg (M)', 'wp_monsters')."</th>
			<th>".__('Critical', 'wp_monsters')."</th>
			<th>".__('Range', 'wp_monsters')."</th>
			<th>".__('Weight', 'wp_monsters')."</th>
			<th>".__('Type of damage', 'wp_monsters')."</th>
		</tr>
		<tr>
			<td>[generaltype] [subtype]</td>
			<td>[cost]</td>
			<td>[dmgs]</td>
			<td>[dmgm]</td>
			<td>[critical]</td>
			<td>[range]</td>
			<td>[weight]</td>
			<td>[typedamageb] [typedamagep] [typedamages]</td>
		</tr>
		<tr><td colspan='8'>[special]</td></tr>
	</table>";

	$codes = array("generaltype", "subtype", "cost", "dmgs", "dmgm", "critical", "range", "weight", "typedamageb", "typedamagep", "typedamages", "special");
	foreach($codes as $code) {
		$data = get_post_meta( $post->ID, $code, true );
		if ($code == 'generaltype') $data = $generaltypes[$data];
		if ($code == 'subtype') $data = $subtypes[$data];
		if ($code == 'critical') $data = $criticals[$data];
		if ($code == 'typedamageb' && $data == 1) $data = __("Bludgeoning", "wp_monsters");
		else if ($code == 'typedamageb') $data = " ";
		if ($code == 'typedamagep' && $data == 1) $data = __("Piercing", "wp_monsters");
		else if ($code == 'typedamagep') $data = " ";
		if ($code == 'typedamages' && $data == 1) $data = __("Slashing", "wp_monsters");
		else if ($code == 'typedamages') $data = " ";
		if ($data == '')  $data = "--";
		$template = str_replace("[".$code."]", $data, $template);
	} 

	$html .= $template;
	$html .= "";
	return $html;
}
add_shortcode( 'weapon', 'weapon_shortcode' );

?>
