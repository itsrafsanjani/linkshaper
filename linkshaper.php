<?php
/*
Plugin Name: LinkShaper
Plugin URI: https://codeshaper.net/linkshaper
Description: A link shortener plugin for WordPress.
Version: 1.0.0
Author: Rafsan Jani
Author URI: https://github.com/itsrafsanjani
License: GPL2
*/

// Code to create the database table
function linkshaper_create_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'linkshaper';
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        long_url text NOT NULL,
        short_code varchar(10) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

// Run the function to create the database table on plugin activation
register_activation_hook(__FILE__, 'linkshaper_create_table');

function linkshaper_menu() {
	add_menu_page(
		'LinkShaper', // page title
		'LinkShaper', // menu title
		'manage_options', // capability
		'linkshaper', // menu slug
		'load_main_template' // function to display page content
	);
}
add_action('admin_menu', 'linkshaper_menu');

function load_main_template() {
	require_once( 'template/main.php');
}

// Redirect short URLs to their original URLs
add_action('template_redirect', 'linkshaper_redirect_short_url');

function linkshaper_redirect_short_url() {
	$url = home_url() . $_SERVER['REQUEST_URI'];
	$path = parse_url( $url, PHP_URL_PATH ); // gets the path component of the URL ("/b99c30f7")
	$slug = basename( $path ); // gets the last segment of the path ("b99c30f7")

	// Check if the current URL is a short URL
	global $wpdb;
	$table_name = $wpdb->prefix . 'linkshaper';
	$long_url = $wpdb->get_var("SELECT long_url FROM $table_name WHERE short_code = '$slug'");

	// If it is a short URL, redirect to the original URL
	if ($long_url) {
		wp_redirect($long_url);
		exit;
	}
}

// start session
add_action('init', 'start_session');

function start_session() {
	if(!session_id()) {
		session_start();
	}
}
