<?php
/*
Plugin Name:  EU Cookies Notice
Plugin URI:   https://github.com/cftp/eu-cookies
Description:  Adds a notice to your site which complies with the EU e-Privacy Directive on cookies via implied constent. Compatible with static page caching.
Version:      1.0
Author:       <a href="http://johnblackbourn.com/">John Blackbourn</a> & <a href="htp://codeforthepeople.com/">Code for the People</a>
Text Domain:  cftpeuc
Domain Path:  /languages/
License:      GPL v2 or later

Copyright © 2012 John Blackbourn / Code for the People Ltd
				_____________
			   /      ____   \
		 _____/       \   \   \
		/\    \        \___\   \
	   /  \    \                \
	  /   /    /          _______\
	 /   /    /          \       /
	/   /    /            \     /
	\   \    \ _____    ___\   /
	 \   \    /\    \  /       \
	  \   \  /  \____\/    _____\
	   \   \/        /    /    / \
		\           /____/    /___\
		 \                        /
		  \______________________/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,	
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class CFTP_EU_Cookies {

	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'wp_footer',          array( $this, 'message' ) );
		add_action( 'admin_init',         array( $this, 'admin_init' ) );

		$this->settings = get_option( 'cftpeuc', array() );

	}

	/**
	 * Enqueue the CSS and JS that we need.
	 *
	 * @author John Blackbourn
	 **/
	public function assets() {

		wp_register_script(
			'jquery.cookie',
			$this->plugin_url( 'js/jquery.cookie.js' ),
			array( 'jquery' ),
			'1.3'
		);
		wp_enqueue_script(
			'cftpeuc',
			$this->plugin_url( 'js/script.js' ),
			array( 'jquery', 'jquery.cookie' ),
			$this->plugin_ver( 'js/script.js' ),
			true
		);
		wp_localize_script(
			'cftpeuc',
			'cftpeuc',
			array(
				'path' => COOKIEPATH
			)
		);
		wp_enqueue_style(
			'cftpeuc',
			$this->plugin_url( 'css/style.css' ),
			null,
			$this->plugin_ver('css/style.css' )
		);

	}

	/**
	 * Register our settings and settings field.
	 *
	 * @return void
	 * @author John Blackbourn
	 **/
	public function admin_init() {

		register_setting( 'reading', 'cftpeuc' );

		add_settings_field(
			'cftpeuc',
			__( 'Cookie Notice "More Info" page', 'cftpeuc' ),
			array( $this, 'settings_field' ),
			'reading'
		);

	}

	/**
	 * Output our settings field.
	 *
	 * @return void
	 * @author John Blackbourn
	 **/
	public function settings_field() {

		if ( isset( $this->settings['page'] ) )
			$selected = $this->settings['page'];
		else
			$selected = null;

		wp_dropdown_pages( array(
			'selected'         => $selected,
			'name'             => 'cftpeuc[page]',
			'id'               => 'cftpeuc-page',
			'show_option_none' => __( '&mdash; Select &mdash;', 'cftpeuc' )
		) );

	}

	/**
	 * Drop our cookie message into the footer.
	 *
	 * @author John Blackbourn
	 **/
	public function message() {
		$this->render( 'cookie-message.php' );
	}

	/**
	 * Renders a template, looking first for the template file in the theme directory
	 * and afterwards in this plugin's /theme/ directory.
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	protected function render( $template_file, $vars = null ) {
		// Maybe override the template with our own file
		$template_file = $this->locate_template( $template_file );
		
		// Ensure we have the same vars as regular WP templates
		global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		if ( is_array($wp_query->query_vars) )
			extract($wp_query->query_vars, EXTR_SKIP);

		// Plus our specific template vars
		if ( is_array( $vars ) )
			extract( $vars );
		
		require_once( $template_file );
	}

	/**
	 * Takes a filename and attempts to find that in the designated plugin templates
	 * folder in the theme (defaults to main theme directory, but uses a custom filter
	 * to allow theme devs to specify a sub-folder for all plugin template files using
	 * this system).
	 * 
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH to cope with themes which
	 * inherit from a parent theme by just overloading one file.
	 *
	 * @param string $template_file A template filename to search for 
	 * @return string The path to the template file to use
	 * @author Simon Wheatley
	 **/
	protected function locate_template( $template_file ) {
		$located = '';
		$sub_dir = apply_filters( 'sw_plugin_tpl_dir', '' );
		if ( $sub_dir )
			$sub_dir = trailingslashit( $sub_dir );
		// If there's a tpl in a (child theme or theme with no child)
		if ( file_exists( STYLESHEETPATH . "/$sub_dir" . $template_file ) )
			return STYLESHEETPATH . "/$sub_dir" . $template_file;
		// If there's a tpl in the parent of the current child theme
		else if ( file_exists( TEMPLATEPATH . "/$sub_dir" . $template_file ) )
			return TEMPLATEPATH . "/$sub_dir" . $template_file;
		// Fall back on the bundled plugin template (N.B. no filtered subfolder involved)
		else if ( file_exists( $this->plugin_path( "templates/$template_file" ) ) )
			return $this->plugin_path( "templates/$template_file" );
		// Oh dear. We can't find the template.
		$msg = sprintf( __( "This plugin template could not be found: %s", 'cftpeuc' ), $template_file );
		echo "<p style='background-color: #ffa; border: 1px solid red; color: #300; padding: 10px;'>$msg</p>";
	}

	/**
	 * Returns the URL for for a file/dir within this plugin.
	 *
	 * @param $path string The path within this plugin, e.g. '/js/clever-fx.js'
	 * @return string URL
	 * @author John Blackbourn
	 **/
	protected function plugin_url( $file = '' ) {
		return $this->plugin( 'url', $file );
	}

	/**
	 * Returns the filesystem path for a file/dir within this plugin.
	 *
	 * @param $path string The path within this plugin, e.g. '/js/clever-fx.js'
	 * @return string Filesystem path
	 * @author John Blackbourn
	 **/
	protected function plugin_path( $file = '' ) {
		return $this->plugin( 'path', $file );
	}

	/**
	 * Returns a version number for the given plugin file.
	 *
	 * @param $path string The path within this plugin, e.g. '/js/clever-fx.js'
	 * @return string Version
	 * @author John Blackbourn
	 **/
	protected function plugin_ver( $file ) {
		return filemtime( $this->plugin_path( $file ) );
	}

	/**
	 * Returns the current plugin's basename, eg. 'my_plugin/my_plugin.php'.
	 *
	 * @return string Basename
	 * @author John Blackbourn
	 **/
	protected function plugin_base() {
		return $this->plugin( 'base' );
	}

	function plugin( $item, $file = '' ) {
		if ( !isset( $this->plugin ) ) {
			$this->plugin = array(
				'url'  => plugin_dir_url( __FILE__ ),
				'path' => plugin_dir_path( __FILE__ ),
				'base' => plugin_basename( __FILE__ )
			);
		}
		return $this->plugin[$item] . ltrim( $file, '/' );
	}

}

defined( 'ABSPATH' ) or die();

global $cftpeuc;

$cftpeuc = new CFTP_EU_Cookies;

?>