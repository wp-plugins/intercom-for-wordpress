<?php
/*
Plugin Name: Intercom for WordPress
Plugin URI: http://lumpylemon.co.uk/plugins/intercom-crm-for-wordpress
Description: Integrate the <a href="http://intercom.io">Intercom</a> CRM and messaging app into your WordPress website.
Author: Simon Blackbourn
Author URI: https://twitter.com/lumpysimon
Version: 0.4



	-----------
	description
	-----------

	Intercom is a customer relationship management (CRM) and messaging tool for web app owners. WordPress is being widely used as a web app nowadays, so Intercom is an ideal companion app to find out more about your users, contact them, get their instant feedback, and track your relationship with them over time so you can spot those who need attention.

	This plugin generates the Javascript install code to integrate all of this functionality into your WordPress-powered web app.

	It allows you to securely connect to Intercom using secure key authentication mode, and you can optionally send extra custom data about your users.



	-------
	license
	-------

	This is a plugin for WordPress (http://wordpress.org).

	Copyright Simon Blackbourn (simon@lumpylemon.co.uk)

	Released under the GPL license: http://www.opensource.org/licenses/gpl-license.php

	This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.



	--------
	about me
	--------

	I'm Simon Blackbourn, co-founder of Lumpy Lemon, a small & friendly UK-based WordPress design & development company specialising in custom-built WordPress CMS sites. I work mainly, but not exclusively, with not-for-profit organisations.

	Find me on Twitter, Skype & GitHub: lumpysimon



*/



define( 'LL_INTERCOM_VERSION', '0.4' );



class lumpyIntercom {



	public function __construct() {

		register_activation_hook(   __FILE__, array( $this, 'hello'   ) );
		register_deactivation_hook( __FILE__, array( $this, 'goodbye' ) );

		add_action( 'wp_footer',             array( $this, 'output_install_code' ) );
		add_action( 'admin_menu',            array( $this, 'create_options_page' ) );
		add_action( 'network_admin_menu',    array( $this, 'create_options_page' ) );
		add_action( 'admin_init',            array( $this, 'settings_init'       ) );
		add_action( 'admin_notices',         array( $this, 'notice'              ) );
		add_action( 'network_admin_notices', array( $this, 'notice'              ) );

	}



	/**
	 * various initiation stuff when the plugin is activated
	 * @return null
	 */
	function hello() {

		// add the 'hide from intercom' capability to the admin user

		$role = get_role( 'administrator' );
		$role->add_cap( 'hide_from_intercom' );

	}



	/**
	 * stuff to do when the plugin is de-activated
	 * @return null
	 */
	function goodbye() {

		// remove the 'hide from intercom' capability from the admin user

		$role = get_role( 'administrator' );
		$role->remove_cap( 'hide_from_intercom' );

	}



	/**
	 * check if this plugin is activated network-wide
	 * @return boolean
	 */
	function is_network_active() {

		if ( ! function_exists( 'is_plugin_active_for_network' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
			return true;

		return false;

	}



	/**
	 * retrieve the intercom options
	 * @return array 'll-intercom' options
	 */
	function get_settings() {

		if ( self::is_network_active() )
			return get_site_option( 'll-intercom' );

		return get_option( 'll-intercom' );

	}



	/**
	 * update the intercom options in the database
	 * @param  array $opts new options settings to save
	 * @return null
	 */
	function update_settings( $opts ) {

		if ( is_network_admin() ) {
			update_site_option( 'll-intercom', $opts );
		} else {
			update_option( 'll-intercom', $opts );
		}

	}



	/**
	 * output the intercom javascript install code to the footer
	 * @return null
	 */
	function output_install_code() {

		// don't do anything if the current user is hidden from intercom
		// or is not logged in

		if ( current_user_can( 'hide_from_intercom' ) or !is_user_logged_in() )
			return;

		global $current_user;

		get_currentuserinfo();

		$opts = self::get_settings();

		// if we're sending the user role as custom data then
		// figure out the current user's role

		if ( $opts['send-user-role'] ) {
			$user = new WP_User( $current_user->ID );
			$role = false;
			if ( !empty( $user->roles ) and is_array( $user->roles ) ) {
				foreach ( $user->roles as $user_role ) {
					$role = $user_role;
				}
			}
		}

		// if a security key has been entered
		// then calculate the hash

		$secure = false;
		if ( isset( $opts['secure'] ) and !empty( $opts['secure'] ) ) {
			$secure = hash_hmac( 'sha256', $current_user->user_email, $opts['secure'] );
		}

		// set the required username format

		switch ( $opts['username'] ) {
			case 'firstlast' :
				$username = $current_user->user_firstname . ' ' . $current_user->user_lastname;
			break;
			default:
				$username = $current_user->display_name;
			break;
		}

		// generate the custom data if required

		$custom = array();

		if ( $role ) {
			$custom[] = '"Role":"' . $role . '"';
		}
		if ( $opts['send-user-id'] ) {
			$custom[] = '"ID":' . $current_user->ID;
		}
		if ( $opts['send-user-url'] and isset( $current_user->user_url ) and !empty( $current_user->user_url ) ) {
			$custom[] = '"Website":"' . $current_user->user_url . '"';
		}

		// allow plugins/themes to add their own custom data

		$custom = apply_filters( 'll_intercom_custom_data', $custom );

		// now put everything together & generate the javascript output

		$out  = '<script id="IntercomSettingsScriptTag">';
		$out .= '// Intercom for WordPress | v' . LL_INTERCOM_VERSION . ' | http://lumpylemon.co.uk/plugins/intercom-crm-for-wordpress' . "\n";
		$out .= 'window.intercomSettings = {';
		$out .= 'app_id:"' . esc_attr( $opts['app-id'] ) . '",';
		$out .= 'email:"' . $current_user->user_email . '",';
		$out .= 'name:"' . $username . '",';
		$out .= 'created_at:' . strtotime( $current_user->user_registered ) . ',';
		if ( $secure ) {
			$out .= 'user_hash:"' . $secure . '",';
		}
		$out .= 'widget:{';
		$out .= 'activator:"#IntercomDefaultWidget"';
		$out .= '}';
		if ( ! empty( $custom ) ) {
			$out .= ',';
			$out .= 'custom_data:{';
			$out .= implode( ',', $custom );
			$out .= '}';
		}
		$out .= '};' . "\n";
		$out .= '</script>' . "\n";
		$out .= '<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic(\'reattach_activator\');ic(\'update\',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement(\'script\');s.type=\'text/javascript\';s.async=true;s.src=\'https://api.intercom.io/api/js/library.js\';var x=d.getElementsByTagName(\'script\')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent(\'onload\',l);}else{w.addEventListener(\'load\',l,false);}};})()</script>' . "\n";

		echo $out;

	}



	/**
	 * show a 'settings saved' notice
	 * and a friendly reminder if the app ID hasn't been entered
	 * @return null
	 */
	function notice() {

		if ( isset( $_GET['page'] ) and ( 'intercom' == $_GET['page'] ) ) {

			if ( is_network_admin() and isset( $_GET['updated'] ) ) { ?>
				<div class="updated" id="ll-intercom-updated"><p><?php _e( 'Settings saved.' ); ?></p></div>
				<?php
			}

		}

		// show a reminder to users who can update options

		if ( ! current_user_can( 'manage_options' ) )
			return;

		$opts = self::get_settings();

		if ( !is_network_admin() and ( !isset( $opts['app-id'] ) or empty( $opts['app-id'] ) ) ) {
			echo '<div class="error" id="ll-intercom-notice"><p><img src="' . $this->warning . '"> <strong>Intercom needs some attention</strong>. ';
			if ( isset( $_GET['page'] ) and 'intercom' == $_GET['page'] ) {
				echo 'Please enter your Intercom application ID';
			} else {
				echo 'Please <a href="options-general.php?page=intercom">configure the Intercom settings</a>';
			}
			echo ' to start tracking your users.</p></div>' . "\n";
		}

	}



	/**
	 * create the relevant type of options page
	 * depending if we're single site or network active
	 * @return null
	 */
	function create_options_page() {

		// annoyingly multisite doesn't play nicely with the settings api
		// so we need to account for that by creating a special page

		if ( self::is_network_active() ) {

			add_submenu_page(
				'settings.php',
				'Intercom Settings',
				'Intercom',
				'manage_network_options',
				'intercom',
				array( $this, 'render_options_page' )
				);

		} else {

			add_options_page(
				'Intercom Settings',
				'Intercom',
				'manage_options',
				'intercom',
				array( $this, 'render_options_page' )
				);

		}

	}



	/**
	 * output the options page
	 * @return null
	 */
	function render_options_page() {

		$opts = self::get_settings();

		$action = is_network_admin() ? 'settings.php?page=intercom' : 'options.php';

		?>

		<div class="wrap">

		<?php screen_icon( 'options-general' ); ?>
		<h2>Intercom for WordPress Configuration</h2>

		<div class="postbox-container" style="width:65%;">

			<form method="post" action="<?php echo $action; ?>">

				<?php settings_fields( 'intercom' ); ?>

				<table class="form-table">
					<tbody>

						<tr valign="top">
							<th scope="row">App ID</th>
							<td>
								<input name="ll-intercom[app-id]" type="text" value="<?php echo esc_attr( $opts['app-id'] ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">Security key</th>
							<td>
								<input name="ll-intercom[secure]" type="text" value="<?php echo esc_attr( $opts['secure'] ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">Username format</th>
							<td>
								<label>
									<input name="ll-intercom[username]" type="radio" value="firstlast" <?php checked( $opts['username'], 'firstlast' ); ?>>
									<span>First name &amp; last name</span>
								</label>
								<br>
								<label>
									<input name="ll-intercom[username]" type="radio" value="display" <?php checked( $opts['username'], 'display' ); ?>>
									<span>Display name</span>
								</label>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">Send user ID?</th>
							<td>
								<input name="ll-intercom[send-user-id]" type="checkbox" value="1" <?php checked( $opts['send-user-id'] ); ?>>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">Send user role?</th>
							<td>
								<input name="ll-intercom[send-user-role]" type="checkbox" value="1" <?php checked( $opts['send-user-role'] ); ?>>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">Send user website?</th>
							<td>
								<input name="ll-intercom[send-user-url]" type="checkbox" value="1" <?php checked( $opts['send-user-url'] ); ?>>
							</td>
						</tr>

					</tbody>

				</table>

				<p class="submit">
					<input class="button-primary" name="ll-intercom-submit" type="submit" value="Save Settings">
				</p>

			</form>

		</div>

		<div class="postbox-container" style="width:20%;">

			<div class="metabox-holder">

				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox ll-intercom-info" id="ll-intercom-support">
						<h3 class="hndle"><span>Need Help?</span></h3>
						<div class="inside">
							<p>If something's not working, the first step is to read the <a href="http://wordpress.org/extend/plugins/intercom-for-wordpress/faq/">FAQ</a>.</p>
							<p>If your question is not answered there, please check the official <a href="http://wordpress.org/tags/intercom-for-wordpress?forum_id=10">support forum</a>.</p>
						</div>
					</div>
				</div>

				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox ll-intercom-info" id="ll-intercom-suggest">
						<h3 class="hndle"><span>Like this Plugin?</span></h3>
						<div class="inside">
							<p>If this plugin has helped you improve your customer relationships, please consider supporting it:</p>
							<ul>
								<li><a href="http://wordpress.org/extend/plugins/intercom-for-wordpress/">Rate it and let other people know it works</a>.</li>
								<li>Link to it or share it on Twitter or Facebook.</li>
								<li>Write a review on your website or blog.</li>
								<li><a href="https://twitter.com/lumpysimon">Follow me on Twitter</a></li>
								<li><a href="http://lumpylemon.co.uk/">Commission me</a> for WordPress development, plugin or design work.</li>
							</ul>
						</div>
					</div>
				</div>

			</div>

		</div>
		</div>
		<?php

	}



	/**
	 * use the WordPress settings API to initiate the various settings
	 * and if it's a network settings page then validate & update any submitted settings
	 * @return null
	 */
	function settings_init() {

		register_setting( 'intercom', 'll-intercom', array( $this, 'validate' ) );
		if ( isset( $_REQUEST['_wpnonce'] ) and wp_verify_nonce( $_REQUEST['_wpnonce'], 'intercom-options' ) ) {

			$file = is_network_admin() ? 'settings.php' : 'options-general.php';

			if ( isset( $_POST['ll-intercom-submit'] ) and is_network_admin() ) {
				$opts = self::validate( $_POST['ll-intercom'] );
				self::update_settings( $opts );
				wp_redirect( add_query_arg( array(
					'page' => 'intercom',
					'updated' => true
					), $file ) );
				die();
			}

		}

	}



	/**
	 * make sure that no dodgy stuff is trying to sneak through
	 * @param  array $input options to validate
	 * @return array        validated options
	 */
	function validate( $input ) {

		$new['app-id']         = wp_kses( trim( $input['app-id'] ), array() );
		$new['secure']         = wp_kses( trim( $input['secure'] ), array() );
		$new['username']       = wp_kses( trim( $input['username'] ), array() );
		$new['send-user-role'] = absint( $input['send-user-role'] );
		$new['send-user-id']   = absint( $input['send-user-id'] );
		$new['send-user-url']  = absint( $input['send-user-url'] );

		return $new;

	}



}



// let's go!

$lumpy_intercom = new lumpyIntercom;



?>