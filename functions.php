<?php

define('LOAD_ON_INIT', 1);
define('LOAD_AFTER_WP', 10);
define('LOAD_AFTER_THEME', 100);
define('PUBLIC_FOLDER', get_stylesheet_directory_uri() . '/public');
define('COMPONENTS', '/app/views/components');

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

class WPTheme {
  public static function init() {
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

    self::enqueue_file(
      'vendor_js',
      PUBLIC_FOLDER . '/js/vendor.js',
      'script',
      [
        'deps' => ['jquery'],
        'in_footer' => true
      ]
    );
    self::enqueue_file(
      'theme_js',
      PUBLIC_FOLDER . '/js/app.js',
      'script',
      [
        'deps' => ['jquery', 'vendor_js'],
        'in_footer' => true
      ]
    );
    self::enqueue_file(
      'vendor_style',
      PUBLIC_FOLDER . '/css/vendor.css',
      'style'
    );
    self::enqueue_file('theme_style', PUBLIC_FOLDER . '/css/app.css', 'style');
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

  private static function enqueue_file($handle, $file_path, $type = 'script', array $enqueue_args = []) {
    if(file_exists(self::real_file_path($file_path))) {
      $_self = __CLASS__;
      $register_args = call_user_func(
        "$_self::merge_args_for_$type",
        $enqueue_args
      );

      call_user_func(
        "$_self::load_file_as_$type",
        $handle,
        $file_path,
        $register_args
      );
    }
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

  private static function real_file_path($file_path) {
    if(strpos($file_path, PUBLIC_FOLDER) !== false) {
      $real_file_path = str_replace(
        PUBLIC_FOLDER,
        get_stylesheet_directory() . '/public',
        $file_path
      );

      return $real_file_path;
    }

    return $file_path;
  }

  private static function merge_args_for_script($args) {
    $default_args = [
      'deps' => [],
      'version' => false,
      'in_footer' => false
    ];

    return array_merge($default_args, $args);
  }

  private static function merge_args_for_style($args) {
    $default_args = [
      'deps' => [],
      'version' => false,
      'media' => 'all'
    ];

    return array_merge($default_args, $args);
  }

  private static function load_file_as_script($handle, $file_path, $args) {
    wp_register_script(
      $handle,
      $file_path,
      $args['deps'],
      $args['version'],
      $args['in_footer']
    );
    wp_enqueue_script($handle);
  }

  private static function load_file_as_style($handle, $file_path, $args) {
    wp_register_style(
      $handle,
      $file_path,
      $args['deps'],
      $args['version'],
      $args['media']
    );
    wp_enqueue_style($handle);
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
