<?php
/*
Plugin Name: Currently Listening
Plugin URI: http://www.patrickgarman.com/personal-projects/wordpress-plugins/currently-listening/
Description: Adds a short line at the bottom of posts/pages using custom fields to tell the world what your listening to.
Version: 1.0.1
Author: Patrick Garman
Author URI: http://www.patrickgarman.com/

/*  Copyright 2010 Patrick Garman (email: patrick@garmanonline.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// All plugins need a fancy admin page!!!
add_action('admin_menu', 'clt_plugin_menu');
function clt_plugin_menu() {
	add_options_page('Currently Listening Plugin Settings', 'Currently Listening', 'manage_options', 'currently-listening', 'clt_plugin_options');
}
function clt_plugin_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	if (isset($_POST['update_clt']))
	{
		update_option('clt_bold', stripslashes($_POST['clt_bold']));
		update_option('clt_divstyle', stripslashes($_POST['clt_divstyle']));
		update_option('clt_pstyle', stripslashes($_POST['clt_pstyle']));
		update_option('clt_text', stripslashes($_POST['clt_text']));
		update_option('clt_separator', stripslashes($_POST['clt_separator']));
	}
	echo ('<div class="wrap">');
	echo ('<h2>Currently Listening To</h2>');
	echo ('<small><em>Note: You must enter any spaces needed before AND after your separator.</em></small>');
	echo ('
		<form action="'.$_SERVER["REQUEST_URI"].'" method="post">
			<table>
				<tr>
					<td style="text-align:right;">Default Text: </td>
					<td><input style="width:300px;" type="text" name="clt_text" value="'.get_option('clt_text').'" /></td>
				</tr>
				<tr>
					<td style="text-align:right;">Separator: </td>
					<td>
						<input style="width:85px;" type="text" name="clt_separator" value="'.get_option('clt_separator').'" />
						<strong>BOLD</strong> Song and Artist Names: 
						<input type="hidden" name="clt_bold" value="0" />
						<input type="checkbox" name="clt_bold" value="1" 
	');
	if (get_option('clt_bold')==1) echo 'checked ';
	echo ('
						/>
					</td>
				</tr>
				<tr>
					<td style="text-align:right;">Custom DIV Style: </td>
					<td><textarea style="width:300px; height:100px;" name="clt_divstyle">'.get_option('clt_divstyle').'</textarea></td>
				</tr>
				<tr>
					<td style="text-align:right;">Custom P Style: </td>
					<td><textarea style="width:300px; height:100px;" name="clt_pstyle">'.get_option('clt_pstyle').'</textarea></td>
				</tr>
			</table>
			<input type="submit" value="Save Changes" name="update_clt" />
		</form>
		<p>I\'ve put a lot of hard work into this plugin and don\'t require any links or credit for you to use it free. If you like it, please <a href="http://www.patrickgarman.com/personal-projects/" target="_blank">donate now</a> so I can make more awesome plugins and keep them updated!</p></p>
	');
	echo ('</div>');
}


// The hard work in action.
add_action('the_content', 'currently_listening_to_plugin'); 
function currently_listening_to_plugin($content) {
	if (is_single() || is_page())
	{
		// Gather the custom field variables
		$clt_isgood=0;
		if($clt_plugin_song = get_post_meta(get_the_ID(), 'CLT_song', true))
		{$clt_isgood=2;}
		if($clt_plugin_artist = get_post_meta(get_the_ID(), 'CLT_artist', true))
		{$clt_isgood++;}
		// Set plugin to blank
		$clt_plugin = '';
		// If we are good to go (have at least a song or artist)....
		if ($clt_isgood>0)
		{
			$clt_plugin = '<div id="clt_plugin_div" style="'.get_option('clt_divstyle').'"><p style="'.get_option('clt_pstyle').'">';
			// Get the text/separator variables
			$clt_plugin_text = get_option('clt_text');
			$clt_plugin_separator = get_option('clt_separator');
			// If custom text set, lets use it instead
			if(get_post_meta(get_the_ID(), 'CLT_text', true))
			{$clt_plugin_text = get_post_meta(get_the_ID(), 'CLT_text', true);}
			// Add text and separator into the final variable
			$clt_plugin .= $clt_plugin_text.$clt_plugin_separator;
			// Are we bolding names?
			if (get_option('clt_bold')==1) $clt_plugin_spanstyles='font-weight:bold;';
			// Do we have song, artist, or both?!clt_bold			
			if ($clt_isgood==2) // song only
				$clt_plugin .= '<span style="'.$clt_plugin_spanstyles.'">'.$clt_plugin_song.'</span>';
			elseif ($clt_isgood==1) // artist only
				$clt_plugin .= '<span style="'.$clt_plugin_spanstyles.'">'.$clt_plugin_artist.'</span>';
			elseif ($clt_isgood==3) // YAY BOTH
				$clt_plugin .= '<span style="'.$clt_plugin_spanstyles.'">'.$clt_plugin_song.'</span> by <span style="'.$clt_plugin_spanstyles.'">'.$clt_plugin_artist.'</span>';
			$clt_plugin .= '</p></div>';
		}
		
		// All done! Show the world the masterpiece.
		return $content . $clt_plugin;
	}
	else { return $content; }
}

// We need an options variable! INSTALL!!
register_activation_hook( __FILE__, 'clt_activate' );
function clt_activate() {
    add_option('clt_bold', '1', 'Bold song and artist', 'no');
    add_option('clt_divstyle', '', 'Div style', 'no');
    add_option('clt_pstyle', '', 'P style', 'no');
    add_option('clt_text', 'Currently listening to', 'Default text', 'no');
    add_option('clt_separator', ': ', 'Separator', 'no');
}

// Let's be nice and clean up our mess

register_deactivation_hook( __FILE__, 'clt_deactivate' );
function clt_deactivate() {
    delete_option('clt_bold');
    delete_option('clt_divstyle');
    delete_option('clt_pstyle');
    delete_option('clt_text');
    delete_option('clt_separator');
}

// All done! Let's throw a party!!

?>