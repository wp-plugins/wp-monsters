<?php
/**
 * @package WP Monsters
 * @version 1.0.7
 */
/*
Plugin Name: WP Monsters
Plugin URI: http://blog.gwannon.com/wp-monsters/
Description: This plugin allows to the bloggers to publish in a easy way their Pathfinder RPG home-brew monster in their Wordpress blogs.
Version: 1.0.7
Author: Gwannon
Author URI: http://blog.gwannon.com/
*/
/* 
This plugins uses trademarks and/or copyrights owned by Paizo Inc., which are used under Paizo's Community Use Policy. We are expressly prohibited from charging you to use or access this content. This plugins is not published, endorsed, or specifically approved by Paizo Inc. For more information about Paizo's Community Use Policy, please visit paizo.com/communityuse. For more information about Paizo Inc. and Paizo products, please visit paizo.com. 
*/

//Cargamos el idioma del plugin
function wp_monsters_init() {
	load_plugin_textdomain( 'wp_monsters', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action('init', 'wp_monsters_init', 10);

/* ----------------------- Monsters ---------------------------- */
/* ------------------------------------------------------------- */
/**
 * Add a flag that will allow to flush the rewrite rules when needed.
 */
register_activation_hook( __FILE__, 'wp_monsters_activate' );
function wp_monsters_activate() {
    if ( ! get_option( 'wp_monsters_flush_rewrite_rules_flag' ) ) {
        add_option( 'wp_monsters_flush_rewrite_rules_flag', true );
    }
}


add_action( 'init', 'wp_monsters_create_post_type' );
function wp_monsters_create_post_type() {

	$labels = array(
		'name'               => __( 'Monsters', 'wp_monsters' ),
		'singular_name'      => __( 'Monster', 'wp_monsters' ),
		'add_new'            => __( 'Add new', 'wp_monsters' ),
		'add_new_item'       => __( 'Add new monster', 'wp_monsters' ),
		'edit_item'          => __( 'Edit monster', 'wp_monsters' ),
		'new_item'           => __( 'New monster', 'wp_monsters' ),
		'all_items'          => __( 'All monsters', 'wp_monsters' ),
		'view_item'          => __( 'View monster', 'wp_monsters' ),
		'search_items'       => __( 'Search monster', 'wp_monsters' ),
		'not_found'          => __( 'Monster not found', 'wp_monsters' ),
		'not_found_in_trash' => __( 'Monster not found in trash', 'wp_monsters' ),
		'parent_item_colon'  => '',
		'menu_name'          =>  __( 'Monsters', 'wp_monsters' ),
	);
	$args = array(
		'labels'        => $labels,
		'description'   => __( 'Add new monster', 'wp_monsters' ),
		'public'        => true,
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', /*'page-attributes', 'excerpt'*/ ),
		'rewrite'	=> array('slug' => 'monsters/%monsters%','with_front' => false),
		'query_var'	=> true,
		'has_archive'   => true,
		'hierarchical'	=> true,
		'menu_icon'	=> '/wp-content/plugins/wp-monsters/img/monster.png'
	);
	register_post_type( 'monster', $args );
}

add_action( 'init', 'wp_monsters_create_category' );

function wp_monsters_create_category() {
	$labels = array(
		'name'              => __( 'Type of monsters', 'wp_monsters' ),
		'singular_name'     => __( 'Type of monsters', 'wp_monsters' ),
		'search_items'      => __( 'Search type of monsters', 'wp_monsters' ),
		'all_items'         => __( 'All type of monsters', 'wp_monsters' ),
		'parent_item'       => __( 'Parent type of monsters', 'wp_monsters' ),
		'parent_item_colon' => __( 'Parent type of monsters:', 'wp_monsters' ),
		'edit_item'         => __( 'Edit type of monsters', 'wp_monsters' ),
		'update_item'       => __( 'Update type of monsters', 'wp_monsters' ),
		'add_new_item'      => __( 'Add new type of monsters', 'wp_monsters' ),
		'new_item_name'     => __( 'New type of monsters', 'wp_monsters' ),
		'menu_name'         => __( 'Type of monsters', 'wp_monsters' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' 	=> true,
		//'public'		=> true,
		'query_var'		=> true,
		//slug prodotto deve coincidere con il primo parametro dello slug del Custom Post Type correlato
		'rewrite'		=>  array('slug' => 'monsters' ),
		//'_builtin'		=> false,
	);
	register_taxonomy( 'monsters', 'monster', $args );
}

add_filter('post_link', 'wp_monsters_permalink', 1, 3);
add_filter('post_type_link', 'wp_monsters_permalink', 1, 3);

function wp_monsters_permalink($permalink, $post_id, $leavename) {
    if (strpos($permalink, '%monsters%') === FALSE) return $permalink;
        // Get post
        $post = get_post($post_id);
        if (!$post) return $permalink;

        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'monsters');
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0]))
        	$taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'general';

    return str_replace('%monsters%', $taxonomy_slug, $permalink);
}

add_action( 'init', 'wp_monsters_flush_rewrite_rules_maybe', 20 );
function wp_monsters_flush_rewrite_rules_maybe() {
	    if ( get_option( 'wp_monsters_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'wp_monsters_flush_rewrite_rules_flag' );
	    }
}

//SHORTCODE --------------------------------------
function wp_monsters_add_monster_shortcode() {
    add_meta_box(
        'shortcode', // $id
        __('Shortcode', 'wp_monsters'), // $title 
        'wp_monsters_show_monster_shortcode', // $callback
        'monster', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_monsters_add_monster_shortcode');

function wp_monsters_show_monster_shortcode() { //Show box
	global $post;
	echo "[monster id=\"".$post->ID."\" name=\"".$post->post_name."\" title=\"yes\" description=\"yes\" image=\"yes\"]";
}

//TYPE --------------------------------------
function wp_monsters_add_monster_type() {
    add_meta_box(
        'type', // $id
        __('GENERAL', 'wp_monsters'), // $title 
        'wp_monsters_show_monster_type', // $callback
        'monster', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_monsters_add_monster_type');

function wp_monsters_show_monster_type() { //Show box
	global $post;
	$sizes = array (__("fine", 'wp_monsters'), __("diminutive", 'wp_monsters'), __("tiny", 'wp_monsters'), __("small", 'wp_monsters'), __("medium", 'wp_monsters'), __("large", 'wp_monsters'), __("huge", 'wp_monsters'), __("gargantuan", 'wp_monsters'), __("colossal", 'wp_monsters'));

	$alignment = array (__("lawful good", 'wp_monsters'), __("neutral good", 'wp_monsters'), __("chaotic good", 'wp_monsters'), __("lawful neutral", 'wp_monsters'), __("neutral", 'wp_monsters'), __("chaotic neutral", 'wp_monsters'), __("lawful evil", 'wp_monsters'), __("neutral evil", 'wp_monsters'), __("chaotic evil", 'wp_monsters'));

	?><table width="100%">
		<tr><td><?php _e('Type', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="type" value="<?php echo get_post_meta( $post->ID, 'type', true ); ?>" /></td></tr>
		<tr><td><?php _e('Alignment', 'wp_monsters'); ?></td><td>
		<select name='alignment'>
		<?php 
			foreach ($alignment as $key => $alignment) { ?> 
				<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'alignment', true ) == $key) echo " selected='selected'"; ?>><?php echo $alignment; ?></option>
			<?php }
		?>
		</select>
		</td></tr>
		<tr><td><?php _e('Size', 'wp_monsters'); ?></td><td>
		<select name='size'>
		<?php 
			foreach ($sizes as $key => $size) { ?> 
				<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'size', true ) == $key) echo " selected='selected'"; ?>><?php echo $size; ?></option>
			<?php }
		?>
		</select>
		</td></tr>
		<tr><td><?php _e('CR', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 20, 1, 'cr', get_post_meta( $post->ID, 'cr', true )); ?></td></tr>
		<tr><td><?php _e('XP', 'wp_monsters'); ?></td><td><input type="text" name="xp" value="<?php echo get_post_meta( $post->ID, 'xp', true ); ?>" /></td></tr>
		<tr><td><?php _e('Init', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 15, 1, 'init', get_post_meta( $post->ID, 'init', true ), true); ?></td></tr>
		<tr><td><?php _e('Senses', 'wp_monsters'); ?></td><td><input type="text" style="width: 100%;" name="senses" value="<?php echo get_post_meta( $post->ID, 'senses', true ); ?>" /></td></tr>
	</table><?php
}

function wp_monsters_save_monster_type( $post_id ) { //Save changes
	if (isset($_POST['type'])) update_post_meta( $post_id, 'type', sanitize_text_field( $_POST['type'] ) );
	if (isset($_POST['alignment'])) update_post_meta( $post_id, 'alignment', sanitize_text_field( $_POST['alignment'] ) );
	if (isset($_POST['size'])) update_post_meta( $post_id, 'size', sanitize_text_field( $_POST['size'] ) );
	if (isset($_POST['cr'])) update_post_meta( $post_id, 'cr', sanitize_text_field( $_POST['cr'] ) );
	if (isset($_POST['xp'])) update_post_meta( $post_id, 'xp', sanitize_text_field( $_POST['xp'] ) );
	if (isset($_POST['init'])) update_post_meta( $post_id, 'init', sanitize_text_field( $_POST['init'] ) );
	if (isset($_POST['senses'])) update_post_meta( $post_id, 'senses', sanitize_text_field( $_POST['senses'] ) );
}
add_action( 'save_post', 'wp_monsters_save_monster_type' );

//DEFENSE --------------------------------------
function wp_monsters_add_monster_defense() {
    add_meta_box(
        'defense', // $id
        __('DEFENSE', 'wp_monsters'), // $title 
        'wp_monsters_show_monster_defense', // $callback
        'monster', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_monsters_add_monster_defense');

function wp_monsters_show_monster_defense() { //Show box
	global $post;
	?><table width="100%">
	<tr><td><?php _e('Fort', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 20, 1, 'fort', get_post_meta( $post->ID, 'fort', true ), true); ?> </td>
	<td><?php _e('Ref', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 20, 1, 'ref', get_post_meta( $post->ID, 'ref', true ), true); ?></td>
	<td><?php _e('Will', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 20, 1, 'will', get_post_meta( $post->ID, 'will', true ), true); ?></td>
	<td><?php _e('SR', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 50, 1, 'sr', get_post_meta( $post->ID, 'sr', true ), false); ?></td></tr>
	</table>
	<table width="100%">
	<tr><td><?php _e('CA', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (1, 100, 1, 'ca', get_post_meta( $post->ID, 'ca', true ), false); ?></td></tr>
<tr><td><?php _e('Touched', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (1, 100, 1, 'touched', get_post_meta( $post->ID, 'touched', true ), false); ?></td></tr>
<tr><td><?php _e('Flat-footed', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (1, 100, 1, 'flat-footed', get_post_meta( $post->ID, 'flat-footed', true ), false); ?></td></tr>
<tr><td><?php _e('Info CA', 'wp_monsters'); ?></td><td><input type="text" name="infoca" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'infoca', true ); ?>" /></td></tr>
	<tr><td><?php _e('HP', 'wp_monsters'); ?></td><td><input type="text" name="hp" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'hp', true ); ?>" /></td></tr>
	<tr><td><?php _e('DR', 'wp_monsters'); ?></td><td><input type="text" name="dr" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'dr', true ); ?>" /></td></tr>
	<tr><td><?php _e('Immune', 'wp_monsters'); ?></td><td><input type="text" name="inmmune" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'inmmune', true ); ?>" /></td></tr>
	<tr><td><?php _e('Resist', 'wp_monsters'); ?></td><td><input type="text" name="resist" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'resist', true ); ?>" /></td></tr>
	<tr><td><?php _e('Weaknesses', 'wp_monsters'); ?></td><td><input type="text" name="weaknesses" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'weaknesses', true ); ?>" /></td></tr>
	</table>
	<?php 
	/*
	*/
}

function wp_monsters_save_monster_defense( $post_id ) { //Save changes
	if (isset($_POST['fort'])) update_post_meta( $post_id, 'fort', sanitize_text_field( $_POST['fort'] ) );
	if (isset($_POST['ref'])) update_post_meta( $post_id, 'ref', sanitize_text_field( $_POST['ref'] ) );
	if (isset($_POST['will'])) update_post_meta( $post_id, 'will', sanitize_text_field( $_POST['will'] ) );
	if (isset($_POST['sr'])) update_post_meta( $post_id, 'sr', sanitize_text_field( $_POST['sr'] ) );
	if (isset($_POST['ca'])) update_post_meta( $post_id, 'ca', sanitize_text_field( $_POST['ca'] ) );
	if (isset($_POST['touched'])) update_post_meta( $post_id, 'touched', sanitize_text_field( $_POST['touched'] ) );
	if (isset($_POST['flat-footed'])) update_post_meta( $post_id, 'flat-footed', sanitize_text_field( $_POST['flat-footed'] ) );
	if (isset($_POST['infoca'])) update_post_meta( $post_id, 'infoca', sanitize_text_field( $_POST['infoca'] ) );
	if (isset($_POST['hp'])) update_post_meta( $post_id, 'hp', sanitize_text_field( $_POST['hp'] ) );
	if (isset($_POST['dr'])) update_post_meta( $post_id, 'dr', sanitize_text_field( $_POST['dr'] ) );
	if (isset($_POST['inmmune'])) update_post_meta( $post_id, 'inmmune', sanitize_text_field( $_POST['inmmune'] ) );
	if (isset($_POST['resist'])) update_post_meta( $post_id, 'resist', sanitize_text_field( $_POST['resist'] ) );
	if (isset($_POST['weaknesses'])) update_post_meta( $post_id, 'weaknesses', sanitize_text_field( $_POST['weaknesses'] ) );
}
add_action( 'save_post', 'wp_monsters_save_monster_defense' );

//OFFENSE --------------------------------------
function wp_monsters_add_monster_offense() {
    add_meta_box(
        'offense', // $id
        __('OFFENSE', 'wp_monsters'), // $title 
        'wp_monsters_show_monster_offense', // $callback
        'monster', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_monsters_add_monster_offense');

function wp_monsters_show_monster_offense() { //Show box
	global $post;

	$flytypes = array("--", __("clumsy", 'wp_monsters'), __("poor", 'wp_monsters'), __("average", 'wp_monsters'), __("good", 'wp_monsters'), __("perfect", 'wp_monsters')); 

	?><table width="100%">
	<tr><td><?php _e('Speed', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 120, 5, 'speed', get_post_meta( $post->ID, 'speed', true ), false); ?> <?php _e('ft.', 'wp_monsters'); ?></td></tr>
	<tr><td><?php _e('Fly', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 120, 5, 'fly', get_post_meta( $post->ID, 'fly', true ), false); ?> <?php _e('ft.', 'wp_monsters'); ?></td></tr>
	<tr><td><?php _e('Fly maneuverability', 'wp_monsters'); ?></td><td>
	<select name='flytype'>
	<?php 
		foreach ($flytypes as $key => $flytype) { ?> 
			<option value='<?php echo $key; ?>'<?php if(get_post_meta( $post->ID, 'flytype', true ) == $key) echo " selected='selected'"; ?>><?php echo $flytype; ?></option>
		<?php }
	?>
	</select>
	</td></tr>
	<tr><td><?php _e('Melee', 'wp_monsters'); ?></td><td><input type="text" name="melee" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'melee', true ); ?>" /></td></tr>

	<tr><td><?php _e('Space', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (5, 50, 5, 'space', get_post_meta( $post->ID, 'space', true ), false); ?> <?php _e('ft.', 'wp_monsters'); ?></td></tr>
	<tr><td><?php _e('Reach', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (5, 50, 5, 'reach', get_post_meta( $post->ID, 'reach', true ), false); ?> <?php _e('ft.', 'wp_monsters'); ?></td></tr>

	<tr><td><?php _e('Ranged', 'wp_monsters'); ?></td><td><input type="text" name="ranged" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'ranged', true ); ?>" /></td></tr>
	<tr><td colspan="2"><?php _e('Special attacks', 'wp_monsters'); ?></td></tr>
	<tr><td colspan="2"><textarea name="special-attacks" style="width: 100%;"><?php echo get_post_meta( $post->ID, 'special-attacks', true ); ?></textarea></td></tr>
	<tr><td colspan="2"><?php _e('Spell-like abilities', 'wp_monsters'); ?></td></tr>
	<tr><td colspan="2"><textarea name="spell-like-abilities" style="width: 100%;"><?php echo get_post_meta( $post->ID, 'spell-like-abilities', true ); ?></textarea></td></tr>
	</table>
	<?php 
}

function wp_monsters_save_monster_offense( $post_id ) { //Save changes
	if (isset($_POST['speed'])) update_post_meta( $post_id, 'speed', sanitize_text_field( $_POST['speed'] ) );
	if (isset($_POST['fly'])) update_post_meta( $post_id, 'fly', sanitize_text_field( $_POST['fly'] ) );
	if (isset($_POST['flytype'])) update_post_meta( $post_id, 'flytype', sanitize_text_field( $_POST['flytype'] ) );
	if (isset($_POST['space'])) update_post_meta( $post_id, 'space', sanitize_text_field( $_POST['space'] ) );
	if (isset($_POST['melee'])) update_post_meta( $post_id, 'melee', sanitize_text_field( $_POST['melee'] ) );
	if (isset($_POST['reach'])) update_post_meta( $post_id, 'reach', sanitize_text_field( $_POST['reach'] ) );
	if (isset($_POST['ranged'])) update_post_meta( $post_id, 'ranged', sanitize_text_field( $_POST['ranged'] ) );
	if (isset($_POST['spell-like-abilities'])) update_post_meta( $post_id, 'spell-like-abilities', $_POST['spell-like-abilities'] );
	if (isset($_POST['special-attacks'])) update_post_meta( $post_id, 'special-attacks', $_POST['special-attacks'] );
}
add_action( 'save_post', 'wp_monsters_save_monster_offense' );

//STATS --------------------------------------
function wp_monsters_add_monster_stats() {
    add_meta_box(
        'statistics', // $id
        __('STATISTICS', 'wp_monsters'), // $title 
        'wp_monsters_show_monster_stats', // $callback
        'monster', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_monsters_add_monster_stats');

function wp_monsters_show_monster_stats() { //Show box
	global $post;
	?><table width="100%">
	<tr><td><?php _e('Str', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 100, 1, 'str', get_post_meta( $post->ID, 'str', true ), false); ?></td>
	<td><?php _e('Dex', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 100, 1, 'dex', get_post_meta( $post->ID, 'dex', true ), false); ?> </td>
	<td><?php _e('Con', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 100, 1, 'con', get_post_meta( $post->ID, 'con', true ), false); ?></td>
	<td><?php _e('Int', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 100, 1, 'int', get_post_meta( $post->ID, 'int', true ), false); ?> </td>
	<td><?php _e('Wis', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 100, 1, 'wis', get_post_meta( $post->ID, 'wis', true ), false); ?></td>
	<td><?php _e('Cha', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 100, 1, 'cha', get_post_meta( $post->ID, 'cha', true ), false); ?></td></tr>
	</table>
	<table width="60%">
	<tr><td><?php _e('Base Atk', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 20, 1, 'ba', get_post_meta( $post->ID, 'ba', true ), true); ?></td>
	<td><?php _e('CMB', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 20, 1, 'cmb', get_post_meta( $post->ID, 'cmb', true ), true); ?></td>
	<td><?php _e('CMD', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 50, 1, 'cmd', get_post_meta( $post->ID, 'cmd', true ), false); ?></td></tr>
	</table>
	<table width="100%">
	<tr><td><?php _e('Feats', 'wp_monsters'); ?></td><td><input type="text" name="feats" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'feats', true ); ?>" /></td></tr>
	<tr><td><?php _e('Skills', 'wp_monsters'); ?></td><td><input type="text" name="skills" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'skills', true ); ?>" /></td></tr>
	<tr><td><?php _e('Languages', 'wp_monsters'); ?></td><td><input type="text" name="languages" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'languages', true ); ?>" /></td></tr>
	</table>
	
	<?php 
}

function wp_monsters_save_monster_stats( $post_id ) { //Save changes
	if (isset($_POST['str'])) update_post_meta( $post_id, 'str', sanitize_text_field( $_POST['str'] ) );
	if (isset($_POST['dex'])) update_post_meta( $post_id, 'dex', sanitize_text_field( $_POST['dex'] ) );
	if (isset($_POST['con'])) update_post_meta( $post_id, 'con', sanitize_text_field( $_POST['con'] ) );
	if (isset($_POST['int'])) update_post_meta( $post_id, 'int', sanitize_text_field( $_POST['int'] ) );
	if (isset($_POST['wis'])) update_post_meta( $post_id, 'wis', sanitize_text_field( $_POST['wis'] ) );
	if (isset($_POST['cha'])) update_post_meta( $post_id, 'cha', sanitize_text_field( $_POST['cha'] ) );
	if (isset($_POST['ba'])) update_post_meta( $post_id, 'ba', sanitize_text_field( $_POST['ba'] ) );
	if (isset($_POST['cmb'])) update_post_meta( $post_id, 'cmb', sanitize_text_field( $_POST['cmb'] ) );
	if (isset($_POST['cmd'])) update_post_meta( $post_id, 'cmd', sanitize_text_field( $_POST['cmd'] ) );
	if (isset($_POST['feats'])) update_post_meta( $post_id, 'feats', sanitize_text_field( $_POST['feats'] ) );
	if (isset($_POST['skills'])) update_post_meta( $post_id, 'skills', sanitize_text_field( $_POST['skills'] ) );
	if (isset($_POST['languages'])) update_post_meta( $post_id, 'languages', sanitize_text_field( $_POST['languages'] ) );
}
add_action( 'save_post', 'wp_monsters_save_monster_stats' );

//ECOLOGY --------------------------------------
function wp_monsters_add_monster_ecology() {
    add_meta_box(
        'ecology', // $id
        __('ECOLOGY', 'wp_monsters'), // $title 
        'wp_monsters_show_monster_ecology', // $callback
        'monster', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_monsters_add_monster_ecology');

function wp_monsters_show_monster_ecology() { //Show box
	global $post;
	?>
	<table width="100%">
	<tr><td><?php _e('Environment', 'wp_monsters'); ?></td><td><input type="text" name="environment" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'environment', true ); ?>" /></td></tr>
	<tr><td><?php _e('Organization', 'wp_monsters'); ?></td><td><input type="text" name="organization" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'organization', true ); ?>" /></td></tr>
	<tr><td><?php _e('Treasure', 'wp_monsters'); ?></td><td><input type="text" name="treasure" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'treasure', true ); ?>" /></td></tr>
	</table>
	<?php 
}

function wp_monsters_save_monster_ecology( $post_id ) { //Save changes
	if (isset($_POST['environment'])) update_post_meta( $post_id, 'environment', sanitize_text_field( $_POST['environment'] ) );
	if (isset($_POST['organization'])) update_post_meta( $post_id, 'organization', sanitize_text_field( $_POST['organization'] ) );
	if (isset($_POST['treasure'])) update_post_meta( $post_id, 'treasure', sanitize_text_field( $_POST['treasure'] ) );
}
add_action( 'save_post', 'wp_monsters_save_monster_ecology' );

//SPECIAL ABILITIES --------------------------------------
function wp_monsters_add_monster_special_abilities() {
    add_meta_box(
        'special-abilities', // $id
        __('SPECIAL ABILITIES', 'wp_monsters'), // $title 
        'wp_monsters_show_monster_special_abilities', // $callback
        'monster', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_monsters_add_monster_special_abilities');

function wp_monsters_show_monster_special_abilities() { //Show box
	global $post;
	?>
	<?php _e('Special abilities', 'wp_monsters'); ?><br/>
	<textarea name="special-abilities" style="width: 100%;"><?php echo get_post_meta( $post->ID, 'special-abilities', true ); ?></textarea>
	<?php 
}

function wp_monsters_save_monster_special_abilities( $post_id ) { //Save changes
	if (isset($_POST['special-abilities'])) update_post_meta( $post_id, 'special-abilities', $_POST['special-abilities'] );
}
add_action( 'save_post', 'wp_monsters_save_monster_special_abilities' );

//ADD SHORTCODE TO COLUMNS --------------------------------------
function wp_monsters_set_columns($columns) {
	$columns['shortcode'] = __( 'Shortcode', 'wp_monsters');
	$columns['type'] = __( 'Type', 'wp_monsters');
	$columns['monsters'] = __( 'Category', 'wp_monsters');
      	unset( $columns['date'] );
      	return $columns;
}
add_filter( 'manage_edit-monster_columns', 'wp_monsters_set_columns' );

function wp_monsters_set_columns_info( $column ) {
	if ($column == 'shortcode') {
		global $post; 
		echo "[monster id=\"".$post->ID."\" name=\"".$post->post_name."\" title=\"yes\" description=\"yes\" image=\"yes\"]";
	} else 	if ($column == 'type') {
		global $post; 
		echo get_post_meta( $post->ID, 'type', true );
	} else 	if ($column == 'monsters') {
		global $post; 
		$terms = get_the_terms( $post->ID, 'monsters' );
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
add_action( 'manage_monster_posts_custom_column' , 'wp_monsters_set_columns_info');

//Metemos el filtrado por categoria
add_action('restrict_manage_posts','wp_monsters_restrict');
function wp_monsters_restrict() {
	global $typenow;
	global $wp_query;
	if ($typenow=='monster') {
		$taxonomy = 'monsters';
		wp_monsters_taxonomy_dropdown($taxonomy);
	}
}
add_filter('parse_query','wp_monsters_term_in_query');
function wp_monsters_term_in_query($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	if ($pagenow=='edit.php') {
		if(isset($qv['taxonomy']) && $qv['taxonomy']=='monsters' && isset($qv['term']) && is_numeric($qv['term']) &&$qv['term']>0) { 
			$term = get_term_by('id',$qv['term'],'monsters');
			$qv['term'] = $term->slug;
		}
	}
}


//SHORTCODE ---------------------------------------------
function monster_shortcode( $atts ) {
	$sizes = array (__("fine", 'wp_monsters'), __("diminutive", 'wp_monsters'), __("tiny", 'wp_monsters'), __("small", 'wp_monsters'), __("medium", 'wp_monsters'), __("large", 'wp_monsters'), __("huge", 'wp_monsters'), __("gargantuan", 'wp_monsters'), __("colossal", 'wp_monsters'));
	$alignment = array (__("lawful good", 'wp_monsters'), __("neutral good", 'wp_monsters'), __("chaotic good", 'wp_monsters'), __("lawful neutral", 'wp_monsters'), __("neutral", 'wp_monsters'), __("chaotic neutral", 'wp_monsters'), __("lawful evil", 'wp_monsters'), __("neutral evil", 'wp_monsters'), __("chaotic evil", 'wp_monsters'));
	$flytypes = array("--", __("clumsy", 'wp_monsters'), __("poor", 'wp_monsters'), __("average", 'wp_monsters'), __("good", 'wp_monsters'), __("perfect", 'wp_monsters')); 
	
	$post = get_post( $atts['id'] );
	$html = "";
	if ($atts['title'] != 'no') $html = "<h3>".apply_filters('the_title', $post->post_title)."</h3>";
	if ($atts['image'] != 'no' && has_post_thumbnail($post->ID) ) $html .= get_the_post_thumbnail($post->ID, 'medium', array('class' => "alignleft") );
	if ($atts['description'] != 'no') $html .= apply_filters('the_content', $post->post_content);

	$template = "<table class='wp-monsters'>
			<thead>
				<tr>
					<td><b>".apply_filters('the_title', $post->post_title)."</b></td>
					<td><b>[type] [size] [alignment]</b></td>
				</tr>
			<thead>
			<tbody>
				<tr>
					<td><b>".__('GENERAL', 'wp_monsters')."</b></td>
					<td><b>".__('DEFENSE', 'wp_monsters')."</b></td>
				</tr>
				<tr>
					<td>
						<b>".__('CR', 'wp_monsters').":</b> [cr]<br/>
						<b>".__('XP', 'wp_monsters').":</b> [xp]<br/>
						<b>".__('Init', 'wp_monsters').":</b> [init]<br/>
						<b>".__('Senses', 'wp_monsters').":</b> [senses]<br/>
					</td>
					<td>
						<b>".__('CA', 'wp_monsters').":</b> [ca] ".__('Flat-footed', 'wp_monsters')." [flat-footed] ".__('Touched', 'wp_monsters')." [touched] ([infoca])<br/>
						<b>".__('HP', 'wp_monsters').":</b> [hp]<br/>
						".__('Fort', 'wp_monsters')." [fort], ".__('Ref', 'wp_monsters')." [ref], ".__('Will', 'wp_monsters')." [will]<br/>
						<b>".__('DR', 'wp_monsters').":</b> [dr]<br/>
						<b>".__('Immune', 'wp_monsters').":</b> [inmmune]<br/>
						<b>".__('Resist', 'wp_monsters').":</b> [resist]<br/>		
						<b>".__('Weaknesses', 'wp_monsters').":</b> [weaknesses]<br/>
						<b>".__('SR', 'wp_monsters').":</b> [sr]<br/>
					</td>
				</tr>
				<tr>
					<td><b>".__('OFFENSE', 'wp_monsters')."</b></td>
					<td><b>".__('STATISTICS', 'wp_monsters')."</b></td>
				</tr>
				<tr>
					<td>
						<b>".__('Speed', 'wp_monsters').":</b> [speed] ".__('ft.', 'wp_monsters')." ".__('Fly', 'wp_monsters')." [fly] ".__('ft.', 'wp_monsters')." ([flytype])<br/>
						<b>".__('Melee', 'wp_monsters').":</b> [melee]<br/>
						<b>".__('Space', 'wp_monsters')."</b> [space] ".__('ft.', 'wp_monsters')." <b>".__('Reach', 'wp_monsters')."</b> [reach] ".__('ft.', 'wp_monsters')."</br>
						<b>".__('Ranged', 'wp_monsters').":</b> [ranged]<br/>
						<b>".__('Special attacks', 'wp_monsters').":</b> [special-attacks]<br/>
						<b>".__('Spell-like abilities', 'wp_monsters').":</b><br/>[spell-like-abilities]
					</td>
					<td>
						".__('Str', 'wp_monsters')." [str], ".__('Dex', 'wp_monsters')." [dex], ".__('Con', 'wp_monsters')." [con], ".__('Int', 'wp_monsters')." [int], ".__('Wis', 'wp_monsters')." [wis], ".__('Cha', 'wp_monsters')." [cha]<br/>
						<b>".__('Base Atk', 'wp_monsters')."</b> [ba], <b>".__('CMB', 'wp_monsters')."</b> [cmb], <b>".__('CMD', 'wp_monsters')."</b> [cmd]<br/>
						<b>".__('Feats', 'wp_monsters').":</b> [feats]<br/>
						<b>".__('Skills', 'wp_monsters').":</b> [skills]<br/>
						<b>".__('Languages', 'wp_monsters').":</b> [languages]
					</td>
				</tr>
				<tr>
					<td><b>".__('ECOLOGY', 'wp_monsters')."</b></td>
					<td><b>".__('SPECIAL ABILITIES', 'wp_monsters')."</b></td>
				</tr>
				<tr>
					<td>
						<b>".__('Environment', 'wp_monsters').":</b> [environment]<br/>
						<b>".__('Organization', 'wp_monsters').":</b> [organization]<br/>
						<b>".__('Treasure', 'wp_monsters').":</b> [treasure]
					</td>
					<td>[special-abilities]</td>
				</tr>
			</tbody>
		</table>";
	$codes = array("type", "size", "cr", "xp", "init", "senses", "str", "dex", "con", "int", "wis", "cha", "ba", "cmb", "cmd", "feats", "skills", "speed", "fly", "flytype", "space", "reach", "fort", "ref", "will", "environment", "organization", "treasure", "special-abilities", "sr", "melee", "ranged","special-attacks", "spell-like-abilities", "ca", "flat-footed", "touched", "infoca", "hp", "dr", "inmmune", "resist", "weaknesses", "languages", "alignment");
	foreach($codes as $code) {
		$data = get_post_meta( $post->ID, $code, true );
		if ($code == 'special-abilities' || $code == 'spell-like-abilities') $data = wpautop($data, true);
		else if ($code == 'size') $data = $sizes[$data];
		else if ($code == 'flytype') $data = $flytypes[$data];
		else if ($code == 'alignment') $data = $alignment[$data];
		else if (($code == "str" || $code == "dex" || $code == "con" || $code == "int" || $code == "wis" || $code == "cha") && $data == 0)  $data = "--";
		if ($data == '')  $data = "--";
		$template = str_replace("[".$code."]", $data, $template);
	} 

	$html .= $template;

	return $html;
}
add_shortcode( 'monster', 'monster_shortcode' );

//Modificamos el contenido the_content(); 
add_filter( 'the_content', 'wp_monsters_show_content',17 );
function wp_monsters_show_content ($content) {

	if (get_post_type($wp_query->post->ID) == 'monster') {
		if (has_post_thumbnail($wp_query->post->ID) ) $content .= get_the_post_thumbnail($wp_query->post->ID, 'medium', array('class' => "alignleft") ).$content;
		$content .= monster_shortcode(array("id" => $wp_query->post->ID, "title" => 'no', "description" => 'no'));
		//$content .= "Prueba"; 
	} else if (get_post_type($wp_query->post->ID) == 'spell') {

		$content = spell_shortcode(array("id" => $wp_query->post->ID, "title" => 'no', "description" => 'no')).$content;
		//$content .= "Prueba"; 
	} else if (get_post_type($wp_query->post->ID) == 'feat') {

		$content = feat_shortcode(array("id" => $wp_query->post->ID, "title" => 'no', "description" => 'no'))."<div><b>".__('Benefit', 'wp_monsters').":</b> ".$content."</div>";
		//$content .= "Prueba"; 
	} else if (get_post_type($wp_query->post->ID) == 'weapon') {

		$content = $content.weapon_shortcode(array("id" => $wp_query->post->ID, "title" => 'no', "description" => 'no'));
		//$content .= "Prueba"; 
	} else if (get_post_type($wp_query->post->ID) == 'city') {

		$content = $content.city_shortcode(array("id" => $wp_query->post->ID, "title" => 'no', "description" => 'no'));
		//$content .= "Prueba"; 
	}

	return $content;
}

// FUNCIONES VARIAS ----------------------------
function wp_monsters_generate_select ($ini= 0, $end = 10, $step = 1, $name, $value, $showsymbol = false) {

	$html = "";
	$html = "<select name='".$name."'>\n";
	for ($i = $ini; $i <= $end; $i = $i + $step) {  
		if ($showsymbol && $i >= 0) $text = "+".$i;
		else $text = $i;
		if ($text == $value) $selected = " selected='selected'";
		else  $selected = ""; 

		$html .= "<option value='".$text."'". $selected.">".$text."</option>\n";
	}
	$html .= "</select>";
	return $html;
}

function wp_monsters_taxonomy_dropdown($taxonomy) { ?>
	<select name="<?php echo $taxonomy; ?>" id="<?php echo $taxonomy; ?>" class="postform">
		<option value="0"><?php _e("Show All", "wp-monsters"); ?></option>
		<?php
		$terms = get_terms($taxonomy);
		foreach ($terms as $term) {
			//print_r ($term);
			if($_REQUEST[$taxonomy] == $term->slug) printf( '<option class="level-0" selected="selected" value="%s">%s</option>', $term->slug, $term->name." (".$term->count.")" ); 
			else printf( '<option class="level-0" value="%s">%s</option>', $term->slug, $term->name." (".$term->count.")" );
		} ?>
	</select>
<?php }

require_once ('wp-spells.php');
require_once ('wp-feats.php');
require_once ('wp-weapons.php');
require_once ('wp-cities.php');
?>
