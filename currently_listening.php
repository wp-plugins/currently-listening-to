<?php
/*
Plugin Name: Currently Listening
Plugin URI: http://www.patrickgarman.com/tag/currently-listening/
Description: Adds a short line at the bottom of posts/pages using custom fields to tell the world what your listening to.
Version: 1.1.1
Author: Patrick Garman
Author URI: http://www.patrickgarman.com/
License: GPLv2
*/

/*  Copyright 2011  Patrick Garman  (email : patrickmgarman@gmail.com)

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

$currently_listening = new CurrentlyListening();
class CurrentlyListening{
	
	function constructor() {
		add_action('the_content', array(&$this,'show_clt')); 
	}
	
	function show_clt($content) {
		if (is_single() || is_page()) {
			// Gather the custom field variables
			$clt_isgood=0;
			if($clt_plugin_song = get_post_meta(get_the_ID(), 'CLT_song', true))
			{$clt_isgood=2;}
			if($clt_plugin_artist = get_post_meta(get_the_ID(), 'CLT_artist', true))
			{$clt_isgood++;}
			// Set plugin to blank
			$clt_plugin = '';
			// If we are good to go (have at least a song or artist)....
			if ($clt_isgood>0) {
				$clt_plugin = '<div id="clt_plugin_div" style="'.get_option('clt_divstyle').'"><p style="'.get_option('clt_pstyle').'">';
				// Get the text/separator variables
				$clt_plugin_text = get_option('clt_text');
				$clt_plugin_separator = get_option('clt_separator');
				// If custom text set, lets use it instead
				if(get_post_meta(get_the_ID(), 'CLT_text', true))
				{$clt_plugin_text = get_post_meta(get_the_ID(), 'CLT_text', true);}
				// Add text and separator into the final variable
				$clt_plugin .= $clt_plugin_text.$clt_plugin_separator.' ';
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
	
	function options_page(){
		echo '<div class="wrap">';
			//echo '<h2>'.$this->plugin_data('Name').' Options</h2>';
			$this->settings_form($this->settings_list());
		echo '</div>';
	}
	
	function settings_list() {
		$settings = array(
			array(
				'display' => 'Default Text',
				'name' => 'clt_text',
				'value' => '',
				'type' => 'textbox',
				'hint' => 'the text that comes before what you were listening to'
			),
			array(
				'display' => 'Separator',
				'name' => 'clt_separator',
				'value' => '',
				'type' => 'textbox',
				'hint' => 'what separates the names from the values'
			),
			array(
				'display' => 'Bold Artist/Song Names',
				'name' => 'clt_bold',
				'value' => '1',
				'yes' => 'Yes',
				'no' => 'No',
				'type' => 'radio',
				'hint' => 'do you want the artist and band names bold?'
			),
			array(
				'display' => 'DIV Custom Style',
				'name' => 'clt_divstyle',
				'value' => '',
				'type' => 'textbox',
				'hint' => 'custom styles you want to add to the DIV element'
			),
			array(
				'display' => 'P Custom Style',
				'name' => 'clt_pstyle',
				'value' => '',
				'type' => 'textbox',
				'hint' => 'custom styles you want to add to the P element'
			),
		);
		return $settings;
	}
	
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////// DO NOT EDIT BELOW THIS LINE //////// DO NOT EDIT BELOW THIS LINE //////// DO NOT EDIT BELOW THIS LINE //////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////// DO NOT EDIT BELOW THIS LINE //////// DO NOT EDIT BELOW THIS LINE //////// DO NOT EDIT BELOW THIS LINE //////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////// DO NOT EDIT BELOW THIS LINE //////// DO NOT EDIT BELOW THIS LINE //////// DO NOT EDIT BELOW THIS LINE //////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function __construct(){	
		register_activation_hook(__FILE__, array(&$this, 'on_activation'));
		register_deactivation_hook(__FILE__, array(&$this, 'on_deactivation'));
		add_action('init', array(&$this, 'init'));
		add_action('admin_init', array(&$this, 'register_settings'));
		$this->constructor();
	}

	function init(){
		if (is_admin()) {
			add_action('admin_menu', array(&$this, 'admin_menu'));
			add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(&$this,'settings_link'));
		}
	}
	
	function admin_menu(){
		add_options_page('Currently Listening Admin', 'Currently Listening', 'manage_options', plugin_basename(__FILE__), array(&$this, 'options_page'));
	}
	
	function on_activation(){
		$settings = $this->settings_list();
		foreach ($settings as $setting) {
			add_option($setting['name'], $setting['value']);
		}
	}
	
	function on_deactivation(){
		$settings = $this->settings_list();
		foreach ($settings as $setting) {
			delete_option($setting['name']);
		}
	}
	
	function register_settings() {
		$settings = $this->settings_list();
		foreach ($settings as $setting) {
			register_setting($setting['name'], $setting['value']);
		}
	}
	
	function plugin_data($key){
		//[Name],[PluginURI],[Version],[Description] ,[Author],[AuthorURI],[TextDomain],[DomainPath],[Network],[Title],[AuthorName]
		$data = get_plugin_data(__FILE__);
		return $data[$key];
	}
	
	function settings_link($links) {
		$support_link = '<a href="https://patrickgarman.zendesk.com/" target="_blank">Support</a>';
		array_unshift($links, $support_link);
		$settings_link = '<a href="options-general.php?page='.plugin_basename(__FILE__).'">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	
	function settings_form($settings) {
		$row_count=count($settings)+2;
		echo '<form method="post" action="options.php"><table class="widefat">';
		echo '<thead><tr><th colspan=2>'.$this->plugin_data('Name').' Options</th><th>Say Thanks to Developers</th></tr></thead><tr>';
			echo '<td colspan=2 style="text-align:center; border-bottom:none;"><a href="http://www.garmanonline.com" target="_blank"><img src="http://www.garmanonline.com/ads/wp-leaderboard.gif" alt="GarmanOnline Hosting" /></a></td>';
			echo '<td style="vertical-align:top; border-left:1px solid #DFDFDF; padding:10px 15px;" rowspan='.$row_count.'>';
				echo '<p>While you may not always be able to donate to developers for the time and effort put into the plugins we create. When you find a plugin that does exactly what you need and works perfectly be sure to at least send over an email to show your appreciation.</p>';
				echo '<p><a href="https://patrickgarman.zendesk.com/forums/349122-currently-listening/" target="_blank">Support Forum</a></p>';
				echo '<p><a href="https://patrickgarman.zendesk.com/anonymous_requests/new" target="_blank">Submit a Support Ticket</a></p>';
				echo '<p><a href="http://profiles.wordpress.org/users/patrickgarman/" target="_blank">More Plugins by Patrick Garman</a></p>';
				echo '<p><a href="#" target="_blank">GarmanOnline - 30% Off All Hosting Services</a></p>';
			echo '</td>';
			//<a href="" target="_blank">View Patrick\'s Other Plugins</a>
		echo '</tr>';
			foreach ($settings as $setting) {
				echo '<tr><th scope="row">'.$setting['display'].'</th><td>';
				if ($setting['type']=='radio') {
					echo $setting['yes'].' <input type="'.$setting['type'].'" name="'.$setting['name'].'" value="1" ';
					if (get_option($setting['name'])==1) { echo 'checked="checked" />'; } else { echo ' />'; }
					echo $setting['no'].' <input type="'.$setting['type'].'" name="'.$setting['name'].'" value="0" ';
					if (get_option($setting['name'])==0) { echo 'checked="checked" />'; } else { echo ' />'; }
				} elseif ($setting['type']=='select') {
					$values=$setting['values'];
					echo '<select name="'.$setting['name'].'">';
					foreach ($values as $value=>$name) {
						echo '<option value="'.$value.'" ';
						if (get_option($setting['name'])==$value) { echo ' selected="selected" ';}
						echo '>'.$name.'</option>';
					}
					echo '</select>';
				} else { echo '<input type="'.$setting['type'].'" name="'.$setting['name'].'" value="'.get_option($setting['name']).'" />'; }
				echo ' <em>('.$setting['hint'].')</em></td></tr>';
			}		
			echo '<tr><td style="text-align:center; width:200px;"></td><td style="width:520px;">';
				echo '<input type="submit" value="Save Changes" />';
				echo '<input type="hidden" name="action" value="update" />';
				wp_nonce_field('update-options');
				echo '<input type="hidden" name="page_options" value="'; foreach ($settings as $setting) { echo $setting['name'].','; } echo '" />';
			echo '</td></tr>';
		echo '</table></form>';
	}
}

?>