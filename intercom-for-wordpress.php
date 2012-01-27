<?php 
/*
Plugin Name: Intercom for WordPress
Plugin URI: http://lumpylemon.co.uk/plugins/intercom-crm-for-wordpress
Description: Integrates the <a href="http://intercom.io">Intercom</a> CRM and messaging app into your WordPress website.
Author: Simon Blackbourn
Author URI: http://lumpylemon.co.uk
Version: 0.1



	This is a plugin for WordPress (http://wordpress.org).

	Released under the GPL license:
	http://www.opensource.org/licenses/gpl-license.php

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	Copyright 2012 Simon Blackbourn (simon@lumpylemon.co.uk)

*/



define( 'LL_INTERCOM_VERSION', '0.1' );



class lumpyIntercom {



	public function __construct() {

		register_activation_hook(   __FILE__, array( $this, 'hello'   ) );
		register_deactivation_hook( __FILE__, array( $this, 'goodbye' ) );

		add_action( 'wp_head',      array( $this, 'output_snippet'      ) );
		add_action( 'admin_menu',   array( $this, 'create_options_page' ) );
		add_action( 'admin_init',   array( $this, 'settings_init'       ) );
		add_action( 'admin_footer', array( $this, 'notice'              ) );

	}



	function hello() {

		$role = get_role( 'administrator' );
		$role->add_cap( 'hide_from_intercom' );

		$opts = get_option( 'll-intercom' );

		if ( false === $opts ) {
			$defaults = array(
							'label'          => 'Support',
							'username'       => 'firstlast',
							'send-user-role' => 1
							);
			update_option( 'll-intercom', $defaults );
		}

	}



	function goodbye() {

		$role = get_role( 'administrator' );
		$role->remove_cap( 'hide_from_intercom' );

	}



	function output_snippet() {

		if ( current_user_can( 'hide_from_intercom' ) or !is_user_logged_in() )
			return;

		global $current_user;

		get_currentuserinfo();

		$opts = get_option( 'll-intercom' );

		if ( $opts['send-user-role'] ) {
			$user = new WP_User( $current_user->ID );
			$role = false;
			if ( !empty( $user->roles ) and is_array( $user->roles ) ) {
				foreach ( $user->roles as $user_role ) {
					$role = $user_role;
				}
			}
		}

		$secure = false;
		if ( isset( $opts['secure'] ) and ! empty( $opts['secure'] ) ) {
			$secure = $opts['secure'] . $current_user->user_email;
		}

		switch ( $opts['username'] ) {
			case 'firstlast' :
				$username = $current_user->user_firstname . ' ' . $current_user->user_lastname;
			break;
			default:
				$username = $current_user->display_name;
			break;
		}

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

		$out  = '<script>';
		$out .= '//<![CDATA[' . "\n";
		$out .= '// Intercom for WordPress | v' . LL_INTERCOM_VERSION . ' | http://lumpylemon.co.uk/plugins/intercom-crm-for-wordpress' . "\n";
		$out .= 'var intercomSettings = {';
		$out .= 'app_id:"' . esc_attr( $opts['app-id'] ) . '",';
		$out .= 'email:"' . $current_user->user_email . '",';
		$out .= 'name:"' . $username . '",';
		$out .= 'created_at:"' . strtotime( $current_user->user_registered ) . '",';
		if ( $secure ) {
			$out .= 'user_hash:"' . sha1( $secure ) . '",';
		}
		$out .= 'widget:{';
		$out .= 'activator:"#IntercomDefaultWidget",';
		$out .= 'label:"' . esc_attr( $opts['label'] ) . '"';
		$out .= '}';
		if ( !empty( $custom ) ) {
			$out .= ',';
			$out .= 'custom_data:{';
			$out .= implode( ',', $custom );
			$out .= '}';
		}
		$out .= '};' . "\n";
		$out .= '(function() {';
		$out .= 'function async_load() {';
		$out .= 'var s = document.createElement("script");';
		$out .= 's.type = "text/javascript"; s.async = true;';
		$out .= 's.src = "https://api.intercom.io/api/js/library.js";';
		$out .= 'var x = document.getElementsByTagName("script")[0];';
		$out .= 'x.parentNode.insertBefore(s, x);';
		$out .= '}';
		$out .= 'if (window.attachEvent) {';
		$out .= 'window.attachEvent("onload", async_load);';
		$out .= '} else {';
		$out .= 'window.addEventListener("load", async_load, false);';
		$out .= '};';
		$out .= '})();' . "\n";
		$out .= '//]]>';
		$out .= '</script>' . "\n";

		echo $out;

	}



	function notice() {

		if ( !current_user_can( 'hide_from_intercom' ) )
			return;

		$opts = get_option( 'll-intercom' );

		if ( ! isset( $opts['app-id'] ) or empty( $opts['app-id'] ) ) {
			echo '<div class="error" id="ll-intercom-notice"><p><img src="' . $this->warning . '"> <strong>Intercom needs some attention</strong>. ';
			if ( isset( $_GET['page'] ) and 'intercom-options' == $_GET['page'] ) {
				echo 'Please enter your Intercom application ID';
			} else {
				echo 'Please <a href="options-general.php?page=intercom-options">configure the Intercom settings</a>';
			}
			echo ' to start tracking your users.</p></div>' . "\n";
		}

	}



	function create_options_page() {

		add_options_page(
			'Intercom Settings',
			'Intercom',
			'manage_options',
			'intercom-options',
			array( $this, 'render_options_page' )
			);

	}



	function render_options_page() {

		$tick = plugins_url( '/i/tick.png', __FILE__ );
		$warn = plugins_url( '/i/warning.png', __FILE__ );

		?>

		<div class="wrap">
		<h2>Intercom for WordPress Configuration</h2>
		<div class="postbox-container" style="width:65%;">
		<form method="post" action="options.php">
		<?php
		settings_fields( 'intercom' );
		do_settings_sections( 'intercom' );
		?>
		<p class="submit">
			<input class="button-primary" name="Submit" type="submit" value="Save Changes">
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

<!--
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox ll-intercom-info" id="ll-intercom-fb">
						<h3 class="hndle"><span>News &amp; Updates</span></h3>
						<div class="inside">
							<ul>
								<li>Follow the <a href="#">Intercom for WordPress Facebook page</a> for news, updates, details of new releases, etc.</li>
							</ul>
						</div>
					</div>
				</div>
-->

			</div>

		</div>
		</div>
		<?php

	}



	function settings_init() {

		register_setting( 'intercom', 'll-intercom', array( $this, 'validate' ) );

		add_settings_section( 'intercom', 'Intercom Options', array( $this, 'settings_text' ), 'intercom' );

		add_settings_field( 'intercom-app-id',         'App ID',            array( $this, 'setting_app_id'         ), 'intercom', 'intercom' );
		add_settings_field( 'intercom-secure',         'Security key',      array( $this, 'setting_secure'         ), 'intercom', 'intercom' );
		add_settings_field( 'intercom-label',          'Label',             array( $this, 'setting_label'          ), 'intercom', 'intercom' );
		add_settings_field( 'intercom-username',       'Username format',   array( $this, 'setting_username'       ), 'intercom', 'intercom' );
		add_settings_field( 'intercom-send-user-role', 'Send user role',    array( $this, 'setting_send_user_role' ), 'intercom', 'intercom' );
		add_settings_field( 'intercom-send-user-id',   'Send user ID',      array( $this, 'setting_send_user_id'   ), 'intercom', 'intercom' );
		add_settings_field( 'intercom-send-user-url',  'Send user website', array( $this, 'setting_send_user_url'  ), 'intercom', 'intercom' );

	}



	function settings_text() {

		echo '<p>Intercom CRM integration settings</p>';

	}



	function setting_app_id() {

		$opts = get_option( 'll-intercom' );

		echo '<input name="ll-intercom[app-id]" type="text" value="' . esc_attr( $opts['app-id'] ) . '">';

	}



	function setting_secure() {

		$opts = get_option( 'll-intercom' );

		echo '<input name="ll-intercom[secure]" type="text" value="' . esc_attr( $opts['secure'] ) . '">';

	}



	function setting_label() {

		$opts = get_option( 'll-intercom' );

		echo '<input name="ll-intercom[label]" type="text" value="' . esc_attr( $opts['label'] ) . '">';

	}



	function setting_username() {

		$opts = get_option( 'll-intercom' );

		echo '<label title="firstlast"><input name="ll-intercom[username]" type="radio" value="firstlast" ' . checked( $opts['username'], 'firstlast', false ) . '> <span>First name &amp; last name</span></label><br>';
		echo '<label title="display"><input name="ll-intercom[username]" type="radio" value="display" ' . checked( $opts['username'], 'display', false ) . '> <span>Display name</span></label>';

	}



	function setting_send_user_role() {

		$opts = get_option( 'll-intercom' );

		echo '<input name="ll-intercom[send-user-role]" type="checkbox" value="1" ' . checked( $opts['send-user-role'], 1, false ) . '>';

	}



	function setting_send_user_id() {

		$opts = get_option( 'll-intercom' );

		echo '<input name="ll-intercom[send-user-id]" type="checkbox" value="1" ' . checked( $opts['send-user-id'], 1, false ) . '>';

	}



	function setting_send_user_url() {

		$opts = get_option( 'll-intercom' );

		echo '<input name="ll-intercom[send-user-url]" type="checkbox" value="1" ' . checked( $opts['send-user-url'], 1, false ) . '>';

	}



	function validate( $input ) {

		$new['app-id']         = wp_kses( trim( $input['app-id'] ) );
		$new['secure']         = wp_kses( trim( $input['secure'] ) );
		$new['label']          = wp_kses( trim( $input['label'] ) );
		$new['username']       = wp_kses( trim( $input['username'] ) );
		$new['send-user-role'] = absint( $input['send-user-role'] );
		$new['send-user-id']   = absint( $input['send-user-id'] );
		$new['send-user-url']  = absint( $input['send-user-url'] );

		return $new;

	}



}



$lumpy_intercom = new lumpyIntercom;



?>