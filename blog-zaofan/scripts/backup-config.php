<?php
require '/var/www/html/wp-load.php';
if (!function_exists('get_plugins')) {
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

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
  'theme_mods_argon' => get_option('theme_mods_argon'),
  'nav_menu_locations' => get_nav_menu_locations(),
  'menus' => array(),
  'pages' => array(),
  'plugins' => array(),
  'themes' => array(),
  'argon_options' => array(),
  'sidebars_widgets' => wp_get_sidebars_widgets(),
  'wp_statistics_widget' => null,
);

foreach (wp_get_nav_menus() as $menu) {
  $items = array();
  foreach ((array) wp_get_nav_menu_items($menu->term_id) as $it) {
    $items[] = array(
      'id' => $it->ID,
      'title' => $it->title,
      'url' => $it->url,
      'type' => $it->type,
      'object' => $it->object,
      'object_id' => $it->object_id,
      'menu_order' => $it->menu_order,
      'parent' => $it->menu_item_parent,
    );
  }
  $bundle['menus'][] = array(
    'term_id' => $menu->term_id,
    'name' => $menu->name,
    'slug' => $menu->slug,
    'items' => $items,
  );
}

$pages = get_posts(array(
  'post_type' => 'page',
  'post_status' => array('publish', 'draft'),
  'numberposts' => -1,
));
foreach ($pages as $p) {
  $bundle['pages'][] = array(
    'ID' => $p->ID,
    'title' => $p->post_title,
    'slug' => $p->post_name,
    'status' => $p->post_status,
    'template' => get_page_template_slug($p->ID),
    'comment_status' => $p->comment_status,
    'content' => $p->post_content,
  );
}

foreach (get_plugins() as $file => $data) {
  $bundle['plugins'][] = array(
    'file' => $file,
    'name' => $data['Name'],
    'version' => $data['Version'],
    'active' => is_plugin_active($file),
  );
}
foreach (wp_get_themes() as $slug => $t) {
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

$optClass = 'WP_STATISTICS\\Option';
if (class_exists($optClass)) {
  $bundle['wp_statistics_widget'] = $optClass::get('widget');
}

file_put_contents(
  '/tmp/blog-zaofan-full-export.json',
  wp_json_encode($bundle, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);
echo 'bytes=' . filesize('/tmp/blog-zaofan-full-export.json') . "\n";
