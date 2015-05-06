<?php
/**
 * @package WP Monsters
 * @version 1.3.4
 */
/* 
This plugins uses trademarks and/or copyrights owned by Paizo Inc., which are used under Paizo's Community Use Policy. We are expressly prohibited from charging you to use or access this content. This plugins is not published, endorsed, or specifically approved by Paizo Inc. For more information about Paizo's Community Use Policy, please visit paizo.com/communityuse. For more information about Paizo Inc. and Paizo products, please visit paizo.com. 
*/
register_activation_hook( __FILE__, 'wp_cities_activate' );
function wp_cities_activate() {
    if ( ! get_option( 'wp_cities_flush_rewrite_rules_flag' ) ) {
        add_option( 'wp_cities_flush_rewrite_rules_flag', true );
    }
}

add_action( 'init', 'wp_cities_create_post_type' );
function wp_cities_create_post_type() {

	$labels = array(
		'name'               => __( 'Cities', 'wp_monsters' ),
		'singular_name'      => __( 'City', 'wp_monsters' ),
		'add_new'            => __( 'Add new', 'wp_monsters' ),
		'add_new_item'       => __( 'Add new city', 'wp_monsters' ),
		'edit_item'          => __( 'Edit city', 'wp_monsters' ),
		'new_item'           => __( 'New city', 'wp_monsters' ),
		'all_items'          => __( 'All cities', 'wp_monsters' ),
		'view_item'          => __( 'View city', 'wp_monsters' ),
		'search_items'       => __( 'Search city', 'wp_monsters' ),
		'not_found'          => __( 'City not found', 'wp_monsters' ),
		'not_found_in_trash' => __( 'City not found in trash', 'wp_monsters' ),
		'parent_item_colon'  => '',
		'menu_name'          =>  __( 'Cities', 'wp_monsters' ),
	);
	$args = array(
		'labels'        => $labels,
		'description'   => __( 'Add new city', 'wp_monsters' ),
		'public'        => true,
		'menu_position' => 6,
		'taxonomies' 	=> array( 'cities'),
		'supports'      => array( 'title', 'editor'/*, 'thumbnail', 'page-attributes', 'excerpt'*/ ),
		'rewrite'	=> array('slug' => 'cities/%cities%','with_front' => false),
		'query_var'	=> true,
		'has_archive'   => true,
		'hierarchical'	=> true,
		'show_in_menu'  => 'admin.php?page=wp-monsters/wp-monsters.php',
		'show_in_nav_menus'   => true,
		'menu_icon'	=> plugin_dir_url( __FILE__ ).'img/city.png'
	);
	register_post_type( 'city', $args );
}

add_action( 'init', 'wp_cities_create_category' );

function wp_cities_create_category() {
	$labels = array(
		'name'              => __( 'Type of cities', 'wp_monsters' ),
		'singular_name'     => __( 'Type of cities', 'wp_monsters' ),
		'search_items'      => __( 'Search type of cities', 'wp_monsters' ),
		'all_items'         => __( 'All type of cities', 'wp_monsters' ),
		'parent_item'       => __( 'Parent type of cities', 'wp_monsters' ),
		'parent_item_colon' => __( 'Parent type of cities:', 'wp_monsters' ),
		'edit_item'         => __( 'Edit type of cities', 'wp_monsters' ),
		'update_item'       => __( 'Update type of cities', 'wp_monsters' ),
		'add_new_item'      => __( 'Add new type of cities', 'wp_monsters' ),
		'new_item_name'     => __( 'New type of cities', 'wp_monsters' ),
		'menu_name'         => __( 'Type of cities', 'wp_monsters' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' 	=> true,
		//'public'		=> true,
		'query_var'		=> true,
		'show_in_nav_menus'   => true,
		//slug prodotto deve coincidere con il primo parametro dello slug del Custom Post Type correlato
		'rewrite'		=>  array('slug' => 'cities' ),
		//'_builtin'		=> false,
	);
	register_taxonomy( 'cities', 'city', $args );
}

add_filter('post_link', 'wp_cities_permalink', 1, 3);
add_filter('post_type_link', 'wp_cities_permalink', 1, 3);

function wp_cities_permalink($permalink, $post_id, $leavename) {
    if (strpos($permalink, '%cities%') === FALSE) return $permalink;
        // Get post
        $post = get_post($post_id);
        if (!$post) return $permalink;

        // Get taxonomy terms
        $terms = wp_get_object_terms($post->ID, 'cities');
        if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0]))
        	$taxonomy_slug = $terms[0]->slug;
        else $taxonomy_slug = 'general';

    return str_replace('%cities%', $taxonomy_slug, $permalink);
}

add_action( 'init', 'wp_cities_flush_rewrite_rules_maybe', 20 );
function wp_cities_flush_rewrite_rules_maybe() {
	    if ( get_option( 'wp_cities_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'wp_cities_flush_rewrite_rules_flag' );
	    }
}

//SHORTCODE --------------------------------------
function wp_cities_add_city_shortcode() {
    add_meta_box(
        'shortcode', // $id
        __('Shortcode', 'wp_monsters'), // $title 
        'wp_cities_show_city_shortcode', // $callback
        'city', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_cities_add_city_shortcode');

function wp_cities_show_city_shortcode() { //Show box
	global $post;
	echo "[city id=\"".$post->ID."\" name=\"".$post->post_name."\" title=\"yes\" description=\"yes\" image=\"yes\"]";
}

//TYPE --------------------------------------
function wp_cities_add_city_type() {
    add_meta_box(
        'type', // $id
        __('STATS', 'wp_monsters'), // $title 
        'wp_cities_show_city_type', // $callback
        'city', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_cities_add_city_type');

function wp_cities_show_city_type() { //Show box
	global $post;

	$sizes = array (__("Thorp (Fewer than 20)", 'wp_monsters'), __("Hamlet (21-60)", 'wp_monsters'), __("Village (61-200)", 'wp_monsters'), __("Small town (201-2,000)", 'wp_monsters'), __("Large town (2,001-5,000)", 'wp_monsters'), __("Small city (5,001-10,000)", 'wp_monsters'), __("Large city (10,001-25,000)", 'wp_monsters'), __("Metropolis (More than 25,000)", 'wp_monsters'));

	$alignment = array (__("lawful good", 'wp_monsters'), __("neutral good", 'wp_monsters'), __("chaotic good", 'wp_monsters'), __("lawful neutral", 'wp_monsters'), __("neutral", 'wp_monsters'), __("chaotic neutral", 'wp_monsters'), __("lawful evil", 'wp_monsters'), __("neutral evil", 'wp_monsters'), __("chaotic evil", 'wp_monsters'));

	?><table width="100%">
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
	</table>
	<table width="100%">
		<tr><td><?php _e('Corruption', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 15, 1, 'corruption', get_post_meta( $post->ID, 'corruption', true ), false); ?></td>
		<td><?php _e('Crime', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 15, 1, 'crime', get_post_meta( $post->ID, 'crime', true ), false); ?> </td>
		<td><?php _e('Economy', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 15, 1, 'economy', get_post_meta( $post->ID, 'economy', true ), false); ?></td>
		<td><?php _e('Law', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 15, 1, 'law', get_post_meta( $post->ID, 'law', true ), false); ?> </td>
		<td><?php _e('Lore', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 15, 1, 'lore', get_post_meta( $post->ID, 'lore', true ), false); ?></td>
		<td><?php _e('Society', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 15, 1, 'society', get_post_meta( $post->ID, 'society', true ), false); ?></td></tr>
	</table>
	<table width="100%">
		<tr><td><?php _e('Qualities', 'wp_monsters'); ?></td><td><input type="text" name="qualities" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'qualities', true ); ?>" /></td></tr>
		<tr><td><?php _e('Danger', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (-5, 15, 1, 'danger', get_post_meta( $post->ID, 'danger', true ), false); ?></td></tr>
	</table>
<?php
}

function wp_cities_save_city_type( $post_id ) { //Save changes
	if (isset($_POST['alignment'])) update_post_meta( $post_id, 'alignment', sanitize_text_field( $_POST['alignment'] ) );
	if (isset($_POST['size'])) update_post_meta( $post_id, 'size', sanitize_text_field( $_POST['size'] ) );

	if (isset($_POST['corruption'])) update_post_meta( $post_id, 'corruption', sanitize_text_field( $_POST['corruption'] ) );
	if (isset($_POST['crime'])) update_post_meta( $post_id, 'crime', sanitize_text_field( $_POST['crime'] ) );
	if (isset($_POST['economy'])) update_post_meta( $post_id, 'economy', sanitize_text_field( $_POST['economy'] ) );
	if (isset($_POST['law'])) update_post_meta( $post_id, 'law', sanitize_text_field( $_POST['law'] ) );
	if (isset($_POST['lore'])) update_post_meta( $post_id, 'lore', sanitize_text_field( $_POST['lore'] ) );
	if (isset($_POST['society'])) update_post_meta( $post_id, 'society', sanitize_text_field( $_POST['society'] ) );

	if (isset($_POST['qualities'])) update_post_meta( $post_id, 'qualities', sanitize_text_field( $_POST['qualities'] ) );
	if (isset($_POST['danger'])) update_post_meta( $post_id, 'danger', sanitize_text_field( $_POST['danger'] ) );
}
add_action( 'save_post', 'wp_cities_save_city_type' );
//DEMOGRAPHICS --------------------------------------
function wp_cities_add_city_demographic() {
    add_meta_box(
        'demographics', // $id
        __('DEMOGRAPHICS', 'wp_monsters'), // $title 
        'wp_cities_show_city_demographic', // $callback
        'city', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_cities_add_city_demographic');

function wp_cities_show_city_demographic() { //Show box
	global $post;
	global $post; ?>
	<table width="100%">
		<tr><td><?php _e('Goverment', 'wp_monsters'); ?></td><td><input type="text" name="goverment" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'goverment', true ); ?>" /></td></tr>
		<tr><td><?php _e('Population', 'wp_monsters'); ?></td><td><input type="text" name="population" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'population', true ); ?>" /></td></tr>
	</table>
	<?php
}

function wp_cities_save_city_demographic( $post_id ) { //Save changes
	if (isset($_POST['goverment'])) update_post_meta( $post_id, 'goverment', sanitize_text_field( $_POST['goverment'] ) );
	if (isset($_POST['population'])) update_post_meta( $post_id, 'population', sanitize_text_field( $_POST['population'] ) );
}
add_action( 'save_post', 'wp_cities_save_city_demographic' );

//MARKETPLACE --------------------------------------
function wp_cities_add_city_marketplace() {
    add_meta_box(
        'marketplaces', // $id
        __('MARKETPLACE', 'wp_monsters'), // $title 
        'wp_cities_show_city_marketplace', // $callback
        'city', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'wp_cities_add_city_marketplace');

function wp_cities_show_city_marketplace() { //Show box
	global $post; ?>
	<table width="100%">
		<tr><td><?php _e('Base Value', 'wp_monsters'); ?></td><td><input type="text" name="basevalue" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'basevalue', true ); ?>" /></td></tr>
		<tr><td><?php _e('Purchase Limit', 'wp_monsters'); ?></td><td><input type="text" name="purchaselimit" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'purchaselimit', true ); ?>" /></td></tr>
		<tr><td><?php _e('Spellcasting', 'wp_monsters'); ?></td><td><?php echo wp_monsters_generate_select (0, 10, 1, 'spellcasting', get_post_meta( $post->ID, 'spellcasting', true ), false); ?></td></tr>
		<tr>
			<td><?php _e('Minor items', 'wp_monsters'); ?></td>
			<td><?php _e('Medium items', 'wp_monsters'); ?></td>
			<td><?php _e('Mayor items', 'wp_monsters'); ?></td>
		</tr>
		<tr>
			<td><input type="text" name="minoritems" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'minoritems', true ); ?>" /></td>
			<td><input type="text" name="mediumitems" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'mediumitems', true ); ?>" /></td>
			<td><input type="text" name="mayoritems" style="width: 100%;" value="<?php echo get_post_meta( $post->ID, 'mayoritems', true ); ?>" /></td>
		</tr>
	</table>
	<?php
}

function wp_cities_save_city_marketplace( $post_id ) { //Save changes
	if (isset($_POST['basevalue'])) update_post_meta( $post_id, 'basevalue', sanitize_text_field( $_POST['basevalue'] ) );
	if (isset($_POST['purchaselimit'])) update_post_meta( $post_id, 'purchaselimit', sanitize_text_field( $_POST['purchaselimit'] ) );
	if (isset($_POST['spellcasting'])) update_post_meta( $post_id, 'spellcasting', sanitize_text_field( $_POST['spellcasting'] ) );
	if (isset($_POST['minoritems'])) update_post_meta( $post_id, 'minoritems', sanitize_text_field( $_POST['minoritems'] ) );
	if (isset($_POST['mediumitems'])) update_post_meta( $post_id, 'mediumitems', sanitize_text_field( $_POST['mediumitems'] ) );
	if (isset($_POST['mayoritems'])) update_post_meta( $post_id, 'mayoritems', sanitize_text_field( $_POST['mayoritems'] ) );
}
add_action( 'save_post', 'wp_cities_save_city_marketplace' );

//ADD SHORTCODE AND CATEGORY TO COLUMNS --------------------------------------
function wp_cities_set_columns($columns) {
	$columns['shortcode'] = __( 'Shortcode', 'wp_monsters');
	$columns['cities'] = __( 'Type of cities', 'wp_monsters');
      	unset( $columns['date'] );
      	return $columns;
}
add_filter( 'manage_edit-city_columns', 'wp_cities_set_columns' );

function wp_cities_set_columns_info( $column ) {
	if ($column == 'shortcode') {
		global $post; 
		echo "[city id=\"".$post->ID."\" name=\"".$post->post_name."\" title=\"yes\" description=\"yes\" image=\"yes\"]";
	} else 	if ($column == 'cities') {
		global $post; 
		$terms = get_the_terms( $post->ID, 'cities' );
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
add_action( 'manage_city_posts_custom_column' , 'wp_cities_set_columns_info');

//Metemos el filtrado por categoria
add_action('restrict_manage_posts','wp_cities_restrict');
function wp_cities_restrict() {
	global $typenow;
	global $wp_query;
	if ($typenow=='city') {
		$taxonomy = 'cities';
		wp_monsters_taxonomy_dropdown($taxonomy);
	}
}
add_filter('parse_query','wp_cities_term_in_query');
function wp_cities_term_in_query($query) {
	global $pagenow;
	$qv = &$query->query_vars;
	if ($pagenow=='edit.php') {
		if(isset($qv['taxonomy']) && $qv['taxonomy']=='cities' && isset($qv['term']) && is_numeric($qv['term']) &&$qv['term']>0) { 
			$term = get_term_by('id',$qv['term'],'cities');
			$qv['term'] = $term->slug;
		}
	}
}

//SHORTCODE ---------------------------------------------
function city_shortcode( $atts ) {

	$sizes = array (__("Thorp (Fewer than 20)", 'wp_monsters'), __("Hamlet (21-60)", 'wp_monsters'), __("Village (61-200)", 'wp_monsters'), __("Small town (201-2,000)", 'wp_monsters'), __("Large town (2,001-5,000)", 'wp_monsters'), __("Small city (5,001-10,000)", 'wp_monsters'), __("Large city (10,001-25,000)", 'wp_monsters'), __("Metropolis (More than 25,000)", 'wp_monsters'));

	$alignment = array (__("lawful good", 'wp_monsters'), __("neutral good", 'wp_monsters'), __("chaotic good", 'wp_monsters'), __("lawful neutral", 'wp_monsters'), __("neutral", 'wp_monsters'), __("chaotic neutral", 'wp_monsters'), __("lawful evil", 'wp_monsters'), __("neutral evil", 'wp_monsters'), __("chaotic evil", 'wp_monsters'));


	$post = get_post( $atts['id'] );
	$html = "";
	if ($atts['title'] != 'no') $html = "<h3>".apply_filters('the_title', $post->post_title)."</h3>";
	if ($atts['image'] != 'no' && has_post_thumbnail($post->ID) ) $html .= get_the_post_thumbnail($post->ID, 'medium', array('class' => "alignleft") );
	if ($atts['description'] != 'no') $html .= apply_filters('the_content', $post->post_content);

	$template = "<table class='wp-cities'>
			<thead>
				<tr>
					<td colspan='3'><b>".apply_filters('the_title', $post->post_title)."</b></td>
					<td colspan='3'><b>[size] [alignment]</b></td>
				</tr>
			<thead>
			<tbody>
				<tr>
					<td><b>".__('Corruption', 'wp_monsters')."</b></td>
					<td><b>".__('Crime', 'wp_monsters')."</b></td>
					<td><b>".__('Economy', 'wp_monsters')."</b></td>
					<td><b>".__('Law', 'wp_monsters')."</b></td>
					<td><b>".__('Lore', 'wp_monsters')."</b></td>
					<td><b>".__('Society', 'wp_monsters')."</b></td>
				</tr>
				<tr>
					<td>[corruption]</td>
					<td>[crime]</td>
					<td>[economy]</td>
					<td>[law]</td>
					<td>[lore]</td>
					<td>[society]</td>
				</tr>
				<tr>
					<td colspan='1'><b>".__('Qualities', 'wp_monsters')."</b></td>
					<td colspan='5'>[qualities]</td>
				</tr>
				<tr>
					<td colspan='1'><b>".__('Danger', 'wp_monsters')."</b></td>
					<td colspan='5'>[danger]</td>
				</tr>
				<tr>
					<td colspan='6'><b>".__('DEMOGRAPHICS', 'wp_monsters')."</b></td>
				</tr>
				<tr>
					<td colspan='1'><b>".__('Goverment', 'wp_monsters')."</b></td>
					<td colspan='5'>[goverment]</td>
				</tr>
				<tr>
					<td colspan='1'><b>".__('Population', 'wp_monsters')."</b></td>
					<td colspan='5'>[population]</td>
				</tr>
				<tr>
					<td colspan='6'><b>".__('MARKETPLACE', 'wp_monsters')."</b></td>
				</tr>
				<tr>
					<td colspan='1'><b>".__('Base value', 'wp_monsters')."</b></td>
					<td colspan='5'>[basevalue]</td>
				</tr>
				<tr>
					<td colspan='1'><b>".__('Purchase Limit', 'wp_monsters')."</b></td>
					<td colspan='5'>[purchaselimit]</td>
				</tr>
				<tr>
					<td colspan='1'><b>".__('Spellcasting', 'wp_monsters')."</b></td>
					<td colspan='5'>[spellcasting]</td>
				</tr>
				<tr>
					<td colspan='2'><b>".__('Minor items', 'wp_monsters')."</b></td>
					<td colspan='2'><b>".__('Medium items', 'wp_monsters')."</b></td>
					<td colspan='2'><b>".__('Mayor items', 'wp_monsters')."</b></td>
				</tr>
				<tr>
					<td colspan='2'>[minoritems]</td>
					<td colspan='2'>[mediumitems]</td>
					<td colspan='2'>[mayoritems]</td>
				</tr>
			</tbody>
		</table>";
	$codes = array("alignment", "size", "corruption", "crime", "economy", "law", "lore", "society", "danger", "qualities", "basevalue", "purchaselimit", "spellcasting", "goverment", "population", "minoritems", "mediumitems", "mayoritems" );
	foreach($codes as $code) {
		$data = get_post_meta( $post->ID, $code, true );
		if ($code == 'size') $data = $sizes[$data];
		else if ($code == 'alignment') $data = $alignment[$data];
		if ($data == '')  $data = "--";
		$template = str_replace("[".$code."]", $data, $template);
	} 

	$html .= $template;

	return $html;
}
add_shortcode( 'city', 'city_shortcode' );

?>
