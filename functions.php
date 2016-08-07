<?php

define('LOAD_ON_INIT', 1);
define('LOAD_AFTER_WP', 10);
define('LOAD_AFTER_THEME', 100);
define('PUBLIC_FOLDER', get_stylesheet_directory_uri() . '/public');
define('COMPONENTS', '/app/views/components');

require_once(get_template_directory() . '/includes/class.wp-with-brunch.php');

function get_template_page_id($template) {
  global $wpdb;

  $sql = 'SELECT post_id FROM ' . $wpdb->postmeta . '
    WHERE meta_key = "_wp_page_template"
    AND meta_value = "' . $template . '"';

  return $wpdb->get_var($sql);
}

function format_class_filename($filename) {
  return strtolower(
    implode(
      '-',
      preg_split('/(?=[A-Z])/', $filename, -1, PREG_SPLIT_NO_EMPTY)
    )
  );
}

class WPTheme extends WPBrunch {
  public static function init() {
    parent::init();
    add_action('wp_enqueue_scripts', [__CLASS__, 'style_script_includes']);
    spl_autoload_register([__CLASS__, 'autoload_classes']);
    spl_autoload_register([__CLASS__, 'autoload_lib_classes']);
    self::theme_support();
    self::register_nav_menus();
    self:: custom_image_sizes();
    add_action('init', [__CLASS__, 'include_additional_files'], LOAD_ON_INIT);
    add_action('send_headers', [__CLASS__, 'add_ie_xua_header']);
  }

  public static function style_script_includes() {
    wp_enqueue_script('jquery');
    wp_register_style(
      'font-awesome',
      '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css',
      false,
      '4.6.3'
    );
    wp_enqueue_style('font-awesome');
  }

  public static function register_nav_menus() {
    register_nav_menus([
      'main_menu' => 'Primary navigation menu in header',
      'footer_menu' => 'Secondary navigation menu in footer'
    ]);
  }

  public static function custom_image_sizes() {
    add_image_size('dance-gallery', 400, 400, true);
  }

  public static function theme_support() {
    add_theme_support('html5', ['comment-list', 'comment-form', 'search-form']);
    add_theme_support('automatic-feed-links');
    add_theme_support('post-thumbnails');
  }

  public static function format_class_filename($filename) {
    return strtolower(
      implode(
        '-',
        preg_split('/(?=[A-Z])/', $filename, -1, PREG_SPLIT_NO_EMPTY)
      )
    );
  }

  public static function autoload_classes($name) {
    $class_name = self::format_class_filename($name);
    $class_path = get_template_directory() . '/includes/class.'
                  . $class_name . '.php';
    if(file_exists($class_path)) require_once $class_path;
  }

  public static function autoload_lib_classes($name) {
    $lib_class_name = get_template_directory() . '/includes/class.'
                      . strtolower($name) . '.php';
    if(file_exists($lib_class_name)) require_once($lib_class_name);
  }

 public static function include_additional_files() {
    $template_url = get_template_directory();
    // new CustomMetaboxes();
    // new CustomPostTypes();

    if(is_admin()) {
      // $cdAdmin = new cdAdmin();
      // $cdAdmin->hooks();
    }
  }

  public static function add_ie_xua_header() {
    if(isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
      header('X-UA-Compatible: IE=edge,chrome=1');
    }
  }
}

WPTheme::init();

function render($template_name, array $render_args = null) {
  include(locate_template($template_name));
}

function is_ie() {
  if((strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') ||
      strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ||
      strpos($_SERVER['HTTP_USER_AGENT'], 'Edge')))

        return true;
}
