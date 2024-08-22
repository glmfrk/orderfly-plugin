<?php
/*
 * Plugin Name:       Orderfly Plugin
 * Plugin URI:        https://github.com/glmfrk/orderfly-plugin
 * Description:       A plugin to handle customer orders.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Gulam Faruk
 * Author URI:        https://join.skype.com/invite/Wp7iCIir7DpR
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/glmfrk/orderfly-plugin
 * Text Domain:       orderfly
 * Domain Path:       /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ORDERFLY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ORDERFLY_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
foreach (['orderfly-shortcode.php', 'orderfly-posttype.php', 'database-functions.php', 'ajax-handler.php', 'email-functions.php', 'generatefpdf.php'] as $file) {
  require_once ORDERFLY_PLUGIN_PATH . 'includes/' . $file;
}

// Activation hook
register_activation_hook(__FILE__, 'orderfly_activate');

// Deactivation hook
register_deactivation_hook(__FILE__, 'orderfly_deactivate');

// Plugin activation function
function orderfly_activate() {
  orderfly_create_database_tables();

  flush_rewrite_rules();
}

// Plugin deactivation function
function orderfly_deactivate() {
    // Actions to perform upon deactivation
    flush_rewrite_rules();
}


// All Vendors file enqueue here 
function orderfly_enqueue_scripts() {
  // Enqueue Font Awesome from CDN
  wp_enqueue_style('orderfly-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), '6.0.0-beta3', 'all');

  // Enqueue CSS files
  wp_enqueue_style('orderfly-bootstrap', plugins_url('assets/css/bootstrap.min.css', __FILE__), array(), '1.0.0', 'all');
  wp_enqueue_style('orderfly-style', plugins_url('assets/css/orderfly.min.css', __FILE__), array(), '1.0.0', 'all');

  // Enqueue bootstrap file
  wp_enqueue_script('orderfly-bootstrap', plugins_url('assets/js/bootstrap.min.js', __FILE__), array('jquery'), null, true);

  // Enqueue jsPDF CDN
  wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js', array(), '2.4.0', true);

  // Enqueue and localize script for AJAX requests
  wp_enqueue_script('orderfly-main', plugins_url('assets/js/orderfly.js', __FILE__), array('jquery', 'jspdf'), null, true);
  wp_localize_script('orderfly-main', 'orderfly_api', array(
    'ajaxurl' => admin_url('admin-ajax.php')
  ));
}
add_action('wp_enqueue_scripts', 'orderfly_enqueue_scripts');


