<?php

/**
 * Plugin Name: NerdPress Support
 * Description: Adds the NerdPress support widget to your WordPress dashboard for easy access to our knowledge base and contact form.
 * Version: 	  0.5.3
 * Author:      NerdPress
 * Author URI:  https://www.nerdpress.net
 * GitHub URI: 	blogtutor/blog-tutor-support
 * License: 	  GPLv2 or later
 * Text Domain: blog-tutor
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

// GitHub updater
include( dirname( __FILE__ ) . '/github-updater.php' );

// Load Admin menu
include( dirname( __FILE__ ) . '/includes/admin-menu.php' );

if ( ! class_exists( 'Blog_Tutor_Support' ) ) :
/**
 * Blog_Tutor_Support main class.
 *
 * @package  Blog_Tutor_Support
 * @category Core
 * @author   Fernando Acosta, Andrew Wilder, Sergio Scabuzzo
 */
class Blog_Tutor_Support {
  /**
   * Plugin version.
   *
   * @var string
   */
  const VERSION = '0.5.3';

  /**
   * Instance of this class.
   *
   * @var object
   */
  protected static $instance = null;

  /**
   * Initialize the plugin.
   */
  private function __construct() {
    // Load plugin text domain.
    add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

    // Include classes.
    $this->includes();

    if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
      $this->admin_includes();
    }
  }

  /**
   * Return an instance of this class.
   *
   * @return object A single instance of this class.
   */
  public static function get_instance() {
    if ( null == self::$instance ) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  /**
   * Load the plugin text domain for translation.
   */
  public function load_plugin_textdomain() {
    load_plugin_textdomain( 'blog-tutor-support', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
  }

  /**
   * Include admin actions.
   */
  protected function admin_includes() {
    include dirname( __FILE__ ) . '/includes/admin/class-support-admin.php';
  }

  /**
   * Include plugin functions.
   */
  protected function includes() {
    include_once dirname( __FILE__ ) . '/includes/class-support-helpers.php';
    include_once dirname( __FILE__ ) . '/includes/class-support-widget.php';
    include_once dirname( __FILE__ ) . '/includes/class-support-cloudproxy.php';
  }
}

/**
 * Init the plugin.
 */
add_action( 'plugins_loaded', array( 'Blog_Tutor_Support', 'get_instance' ) );

endif;
