<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * @package Woo-Ware2Go
 */
/*
Plugin Name: Amazon Master Seller Dashboard
Plugin URI: http://saniulahsan.info
Description: This plugins checks the Amazon Seller Survey and Other things related to it. Use this shortcode in page [amazon-seller-survey-form].
Version: 1.0.0
Author: Saniul Ahsan
Author URI: http://saniulahsan.info
Text Domain: amazon-seller-dash
*/

// Make sure we don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
	die( 'No script kiddies please!' );
}

define( 'AMAZON_SELLER_DASHBOARD', plugin_dir_path( __FILE__ ) );
define( 'AMAZON_SELLER_CLIENT_ROLE', 'amazon_seller_client' );

// function to create the DB / Options / Defaults
function amazon_seller_trigger_activating_plugin() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table_name = $wpdb->prefix . 'amazon_seller_products';
	if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
		$sql = 'CREATE TABLE ' . $table_name . ' (
				`id` bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`product_id` bigint NOT NULL,
				`name` varchar(255) NOT NULL,
				`order_number` varchar(255) NOT NULL,
				`amount` double NOT NULL,
				`email` varchar(255) NOT NULL,
				`phone` varchar(255) NOT NULL,
				`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated` datetime DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
				';
		dbDelta( $sql );

		$sql = 'CREATE  INDEX idx_product_id ON '. $wpdb->prefix .'amazon_seller_products(product_id);';
		$wpdb->query($sql);

		$sql = 'CREATE  INDEX idx_order_number ON ' . $wpdb->prefix . 'amazon_seller_products(order_number);';
		$wpdb->query($sql);

		$sql = 'CREATE  INDEX idx_email ON ' . $wpdb->prefix . 'amazon_seller_products(email);';
		$wpdb->query($sql);

		$sql = 'CREATE  INDEX idx_phone ON ' . $wpdb->prefix . 'amazon_seller_products(phone);';
		$wpdb->query($sql);

		$sql = 'CREATE  INDEX idx_name ON ' . $wpdb->prefix . 'amazon_seller_products(name);';
		$wpdb->query($sql);
	}

}

// Delete the table when uninstalling the plugin
function amazon_seller_trigger_deactivating_plugin() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'amazon_seller_products';
	$sql        = "DROP TABLE IF EXISTS $table_name;";
	$wpdb->query( $sql );

}

// run the install scripts upon plugin activation
register_activation_hook( __FILE__, 'amazon_seller_trigger_activating_plugin' );

// run the uninstall scripts upon the plugin deactivation
register_deactivation_hook( __FILE__, 'amazon_seller_trigger_deactivating_plugin' );

require_once AMAZON_SELLER_DASHBOARD . '/class/bootfile.class.php';

BpaxAddFile::addFiles( '/', 'helpers', 'php' );
BpaxAddFile::addFiles( '/', 'shortcode', 'php' );

BpaxAddFile::addFiles('views', 'settings', 'php');

add_action('admin_menu', 'amazon_seller_dashboard_settings');
function amazon_seller_dashboard_settings()
{
	add_menu_page('Seller Dashboard', 'Seller Dashboard', 'read', 'amazon-seller-dashboard-api-settings', 'amazon_seller_dashboard_settings_details', BpaxAddFile::addFiles('assets/images', 'icon-small', 'png', true), 99);
	add_submenu_page('amazon-seller-dashboard-api-settings', 'Keywords', 'Keywords', 'manage_options', 'amazon-seller-dashboard-keywords', 'amazon_seller_dashboard_keywords');
}
