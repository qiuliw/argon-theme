<?php
require '/var/www/html/wp-load.php';
$bundle = array(
  'exported_at' => gmdate('c'),
  'site' => array(
    'name' => get_option('blogname'),
    'description' => get_option('blogdescription'),
    'siteurl' => get_option('siteurl'),
    'home' => get_option('home'),
    'WPLANG' => get_option('WPLANG'),
    'timezone_string' => get_option('timezone_string'),
    'permalink_structure' => get_option('permalink_structure'),
  ),
  'theme' => array(
    'stylesheet' => get_option('stylesheet'),
    'template' => get_option('template'),
  ),
  'plugins' => array(),
  'themes' => array(),
  'argon_options' => array(),
  'sidebars_widgets' => wp_get_sidebars_widgets(),
  'wp_statistics_widget' => null,
);

if (!function_exists('get_plugins')) {
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
foreach (get_plugins() as $file => $data) {
  $bundle['plugins'][] = array(
    'file' => $file,
    'name' => $data['Name'],
    'version' => $data['Version'],
    'active' => is_plugin_active($file),
  );
}
$themes = wp_get_themes();
foreach ($themes as $slug => $t) {
  $bundle['themes'][] = array(
    'stylesheet' => $slug,
    'name' => $t->get('Name'),
    'version' => $t->get('Version'),
    'active' => ($slug === get_option('stylesheet')),
  );
}
global $wpdb;
$rows = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'argon_%' ORDER BY option_name");
foreach ($rows as $r) {
  $bundle['argon_options'][$r->option_name] = maybe_unserialize($r->option_value);
}
if (class_exists('WP_STATISTICS\Option')) {
  $bundle['wp_statistics_widget'] = WP_STATISTICS\Option::get('widget');
}
$json = wp_json_encode($bundle, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
file_put_contents('/tmp/blog-zaofan-full-export.json', $json);
echo strlen($json) . " bytes\n";
