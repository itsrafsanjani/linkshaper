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
		'linkshaper_page' // function to display page content
	);
}
add_action('admin_menu', 'linkshaper_menu');

function linkshaper_page() {
	?>
    <div class="wrap">
        <h1>LinkShaper</h1>
        <form method="post" action="">
            <label for="long_url">Long URL:</label>
            <input type="text" name="long_url" id="long_url" required>
            <br><br>
            <input type="submit" name="submit" value="Shorten URL" class="button button-primary">
        </form>
		<?php
		if(isset($_POST['submit'])) {
			// code to shorten URL and save to database
			global $wpdb;
			$table_name = $wpdb->prefix . 'linkshaper';
			$long_url = $_POST['long_url'];
			$short_code = substr(md5($long_url), 0, 8); // generate short URL using MD5 hash
			try {
				$wpdb->insert($table_name, array('long_url' => $long_url, 'short_code' => $short_code));
				echo 'Short URL: ' . home_url() . '/' . $short_code; // display short URL to user
			} catch (Exception $e) {
                echo 'Error: ' . $e->getMessage();
            }
		}
		?>
    </div>
	<?php
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