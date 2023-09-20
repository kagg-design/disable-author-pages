<?php
/*
 * Plugin Name: Disable Author Pages
 * Plugin URI: https://github.com/kagg-design/disable-author-pages
 * Description: Disable the author pages in WordPress and redirect to the homepage.
 * Author: Frank Staude, KAGG Design
 * Version: 1.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Text Domain: disable-author-pages
 * Domain Path: languages
 * Author URI: https://kagg.eu/en/
 */

require_once __DIR__ . '/DisableAuthorPages.php';

/**
 * Delete options on plugin install
 */
function disable_author_pages_uninstall() {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name like 'disable_author_pages_%';" );
}

register_uninstall_hook( __FILE__, 'disable_author_pages_uninstall' );

$disable_author_pages = new DisableAuthorPages();
