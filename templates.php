<?php
/**
 * @package WP Monsters
 * @version 1.3.4
 */
/* 
This plugins uses trademarks and/or copyrights owned by Paizo Inc., which are used under Paizo's Community Use Policy. We are expressly prohibited from charging you to use or access this content. This plugins is not published, endorsed, or specifically approved by Paizo Inc. For more information about Paizo's Community Use Policy, please visit paizo.com/communityuse. For more information about Paizo Inc. and Paizo products, please visit paizo.com. 
*/
$codes_monster = array("title","type", "size", "cr", "xp", "init", "senses", "str", "dex", "con", "int", "wis", "cha", "ba", "cmb", "cmd", "feats", "skills", "speed", "fly", "flytype", "space", "reach", "fort", "ref", "will", "environment", "organization", "treasure", "special-abilities", "sr", "melee", "ranged","special-attacks", "spell-like-abilities", "ca", "flat-footed", "touched", "infoca", "hp", "dr", "inmmune", "resist", "weaknesses", "languages", "alignment", "feets", "sq");
$template_monster = "";
$default_template_monster = "";	

function wp_monsters_init_templates () {
	global $default_template_monster;
	$default_template_monster = "<table class='wp-monsters'>
		<thead>
			<tr>
				<td><b>[title]</b></td>
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
					<b>".__('Speed', 'wp_monsters').":</b> [speed] [feets] ".__('Fly', 'wp_monsters')." [fly] [feets] ([flytype])<br/>
					<b>".__('Melee', 'wp_monsters').":</b> [melee]<br/>
					<b>".__('Space', 'wp_monsters')."</b> [space] [feets] <b>".__('Reach', 'wp_monsters')."</b> [reach] [feets]</br>
					<b>".__('Ranged', 'wp_monsters').":</b> [ranged]<br/>
					<b>".__('Special attacks', 'wp_monsters').":</b> [special-attacks]<br/>
					<b>".__('Spell-like abilities', 'wp_monsters').":</b><br/>[spell-like-abilities]
				</td>
				<td>
					".__('Str', 'wp_monsters')." [str], ".__('Dex', 'wp_monsters')." [dex], ".__('Con', 'wp_monsters')." [con], ".__('Int', 'wp_monsters')." [int], ".__('Wis', 'wp_monsters')." [wis], ".__('Cha', 'wp_monsters')." [cha]<br/>
					<b>".__('Base Atk', 'wp_monsters')."</b> [ba], <b>".__('CMB', 'wp_monsters')."</b> [cmb], <b>".__('CMD', 'wp_monsters')."</b> [cmd]<br/>
					<b>".__('Feats', 'wp_monsters').":</b> [feats]<br/>
					<b>".__('Skills', 'wp_monsters').":</b> [skills]<br/>
					<b>".__('Languages', 'wp_monsters').":</b> [languages]<br/>
					<b>".__('Special Features', 'wp_monsters').":</b> [sq]
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
	global $template_monster;

	$template_monster = stripslashes(get_option( 'wp_monsters_template_monster'));
	if ($template_monster == '') $template_monster = $default_template_monster;

}


add_action( 'init', 'wp_monsters_init_templates' );
?>
