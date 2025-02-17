<?php
/*
Plugin Name: Translate In Realtime Pro
Version: 1.0
Plugin URI: http://ifinityhub.com
Description: Translate Text in Realtime on frontend. Helpful if some of the words are not getting translated after changing the language. 
Author: Waqar Hassan
Author URI: http://ifinityhub.com
License: GPLv2 or later
Text Domain: translate-in-realtime
*/

/*
Copyright 2024 iFinity Hub IT Solutions

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function far_plugin_meta( $links, $file ) { // add some links to plugin meta row
	if ( strpos( $file, 'translate-in-realtime.php' ) !== false ) {
		$links = array_merge( $links, array( '<a href="' . esc_url( get_admin_url(null, 'tools.php?page=translate-in-realtime') ) . '">Settings</a>' ) );
	}
	return $links;
}

/*
* Add a submenu under Tools
*/
function far_add_pages() {
	$page = add_submenu_page( 'tools.php', 'Translate In Realtime', 'Translate In Realtime', 'activate_plugins', 'translate-in-realtime', 'far_options_page' );
	add_action( "admin_print_scripts-$page", "far_admin_scripts" );
}

function far_options_page() {    
	if ( isset( $_POST['setup-update'] ) ) {
        check_admin_referer( 'far_rules_form' );
		$_POST = stripslashes_deep( $_POST );
		
		// If atleast one find has been submitted
		if ( isset ( $_POST['farfind'] ) && is_array( $_POST['farfind'] ) ) { 
			foreach ( $_POST['farfind'] as $key => $find ){

				// If empty ones have been submitted we get rid of the extra data submitted if any.
				if ( empty($find) ){ 
					unset( $_POST['farfind'][$key] );
					unset( $_POST['farreplace'][$key] );
					unset( $_POST['farregex'][$key] );
					unset( $_POST['faradmin'][$key] );
					unset( $_POST['farreplace'][$key] );
					unset( $_POST['farposttype'][$key] );
					unset( $_POST['fardescription'][$key] );
				}
				
				// Convert line feeds on non-regex only
				if ( !isset( $_POST['farregex'][$key] ) ) {
					$_POST['farfind'][$key] = str_replace( "\r\n", "\n", $find );
				}
			}
		}
		unset( $_POST['setup-update'] );
		unset( $_POST['import-text'] );
		unset( $_POST['export-text'] );
		unset( $_POST['submit-import'] );
		
		// Delete the option if there are no settings. Keeps the database clean if they aren't using it and uninstalled.
		if( empty( $_POST['farfind'] ) ) {
			delete_option( 'far_plugin_settings' );
		} else {
			update_option( 'far_plugin_settings', $_POST );
		}
		echo '<div id="message" class="updated fade">';
			echo '<p><strong>Options Updated</strong></p>';
		echo '</div>';
	}
?>
<div class="wrap" style="padding-bottom:5em;">
	<h2>Real-Time Find and Replace</h2>
	<p>Click "Add" to begin. Then enter your find and replace cases below. Click and drag to change the order. </p>
	<div id="far-items">

		<form method="post" action="<?php echo esc_url( $_SERVER["REQUEST_URI"] ); ?>">
            <?php wp_nonce_field( 'far_rules_form' ); ?>
			<input type="button" class="button left" value="Add" onClick="addFormField(); return false;" />
			<input type="submit" class="button left" value="Update Settings" name="update" id="update" />
			<input disabled="disabled" type="button" class="button left" value="Export Settings" name="export" id="export" />
			<input disabled="disabled" type="button" class="button left" value="Import Settings" name="import" id="import" />
			<input type="hidden" name="setup-update" />
			<br style="clear: both;" />
			<?php $far_settings = get_option( 'far_plugin_settings' ); ?>
			<ul id="far_itemlist">
			<?php
				$i = 0;
				// If there are any finds already set
				if ( isset ( $far_settings['farfind'] ) && is_array( $far_settings['farfind'] ) ){
					$i = 1;
					foreach ( $far_settings['farfind'] as $key => $find ){
						if( isset( $far_settings['farregex'][$key] ) ) {
							$regex_checked = 'CHECKED';
						} else {
							$regex_checked = '';
						}

						if ( isset( $far_settings['farreplace'][$key] ) ) {
							$far_replace = $far_settings['farreplace'][$key];
						} else {
							$far_replace = '';
						}

						echo "<li id='row$i'>";

						echo "<div style='float: left'>";
							echo "<div style='float: left'>";
							echo "<label for='farfind$i'>Find:</label>";
							echo "<br />";
							echo "<textarea class='left' name='farfind[$i]' id='farfind$i'>" . esc_textarea( $find ) . "</textarea>";
							echo "</div>";

							echo "<div style='float: left'>";
							echo "<label for='farreplace$i'>Replace With:</label>";
							echo "<br />";
							echo "<textarea class='left' name='farreplace[$i]' id='farreplace$i'>" . esc_textarea( $far_replace ) . "</textarea>";
							echo "</div>";
						echo "</div>";

						echo "<div style='float: left'>";
							echo "<label class='side-label' for='farregex$i'>Use RegEx:</label>";
							echo "<input class='checkbox' type='checkbox' name='farregex[$i]' id='farregex$i' $regex_checked />";
							echo "&nbsp;&nbsp;";
							echo "<label class='side-label-long' for='faradmin$i'>Admin:</label>";
							echo "<input disabled='disabled' class='checkbox' type='checkbox' name='faradmin[$i]' id='faradmin$i' />";
							echo "&nbsp;&nbsp;";
							echo "<label class='side-label-long' for='faradmin$i'>Ignore Case:&nbsp;</label>";
							echo "<input disabled='disabled' class='checkbox' type='checkbox' name='farcaseinsensitive[$i]' id='farcaseinsensitive$i' />";
							echo "<br />";

							echo "<label class='side-label' for='farposttype$i'>Post Type:</label>";
							$post_types_dropdown = "<select disabled='disabled' name='farposttype[$i]' id='farposttype$i'>";
							$post_types_dropdown = $post_types_dropdown . "<option value='any'>any</option>";
							$post_types_dropdown = $post_types_dropdown . '</select>';
							echo $post_types_dropdown;
							echo "<br />";

							echo "<label class='side-label' for='farquerystring$i'>Querystring:</label>";
							echo "<input disabled='disabled' class='textbox' type='text' name='farquerystring[$i]' id='farquerystring$i' value='pro version only' />";
							echo "<br />";

							echo "<label class='side-label' for='farreferrer$i'>Referrer:</label>";
							echo "<input disabled='disabled' class='textbox' type='text' name='farreferrer[$i]' id='farreferrer$i' value='pro version only' />";
							echo "<br />";

							echo "<label class='side-label' for='faruseragent$i'>User Agent:</label>";
							echo "<input disabled='disabled' class='textbox' type='text' name='faruseragent[$i]' id='faruseragent$i' value='pro version only' />";
							echo "<br />";

						echo "</div>";

						echo "<div>";
							echo "<input disabled='disabled' style='width: 615px;' type='text' name='fardescription[$i]' id='fardescription$i' value='pro version only' />";
							echo "<input style='margin-right: 9px' type='button' class='button right remove' value='Remove' onClick='removeFormField(\"#row$i\"); return false;' />";
						echo "</div>";

						echo "</li>";
						unset($regex_checked);
						$i = $i + 1;
					}
				} else {
					// Do nothing
				}
				?>
			</ul>
			<div id="divTxt"></div>
		    <div class="clearpad"></div>
			<input type="button" class="button left" value="Add" onClick="addFormField(); return false;" />
			<input type="submit" class="button left" value="Update Settings" />
		 	<input type="hidden" id="id" value="<?php echo $i; /* used so javascript returns unique ids */ ?>" />
		</form>
	</div>

	<div id="far-sb">
		<div class="postbox" id="far-sbone">
			<h3 class="hndle"><span>Documentation</span></h3>
			<div class="inside">
				<strong>Instructions</strong>
				<p>This plugin will replace HTML code AFTER it is written by the WordPress engine, but before it is sent to a user's browser. None of these changes affect your files. To undo changes, just delete the find/replace pair.</p>
				<ol>
	            <li>Type in text/code to find on the left. This can be a plain text match or a regular expression (use / at the start and end).</li>
				<li>Type in the text/code you want to replace the find with on the right.</li>
				<li>If using a regular expression for the find, check the Use RegEx box.</li>
				</ol>
				<strong>Tips</strong>
				<ol>
	            <li>Want to remove text/code from a page? Leave the replace box blank.</li>
				<li>Want to disable a rule, but not delete it? Put something random in the match box that will not actually be matched.</li>
				<li>Not seeing your changes? Turn off your cache!</li>
				<li>Seeing a blank page on your site? Incorrect regex syntax is the most common cause.</li>
				</ol>
			</div>
		</div>

	</div>
</div>
<?php } ?>
<?php
/*
* Scripts needed for the admin side
*/
function far_admin_scripts() {
	wp_enqueue_script( 'far_dynamicfields', plugins_url() . '/translate-in-realtime/js/jquery.dynamicfields.js', array('jquery') );
	wp_enqueue_script( 'jquery-ui-1', plugins_url() . '/translate-in-realtime/js/jquery-ui-1.10.3.custom.min.js', array('jquery') );
	wp_enqueue_style( 'far_styles', plugins_url() . '/translate-in-realtime/css/far.css' );
}

/*
* Apply find and replace rules
*/
function far_ob_call( $buffer ) { // $buffer contains entire page

	$far_settings = get_option( 'far_plugin_settings' );
	if ( is_array( $far_settings['farfind'] ) ) {
		foreach ( $far_settings['farfind'] as $key => $find ) {
			if( isset( $far_settings['farregex'][$key] ) ) {
				$buffer = preg_replace( $find, $far_settings['farreplace'][$key], $buffer );
			} else {			
				$buffer = str_replace( $find, $far_settings['farreplace'][$key], $buffer );
			}
		}
	}
	return $buffer;
}

function far_template_redirect() {
	ob_start();
	ob_start( 'far_ob_call' );
}

//Add left menu item in admin
add_action( 'admin_menu', 'far_add_pages' );

//Add additional links below plugin description on plugin page
add_filter( 'plugin_row_meta', 'far_plugin_meta', 10, 2 );

//Handles find and replace for public pages
add_action( 'template_redirect', 'far_template_redirect' );
