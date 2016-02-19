<?php
/**
* Plugin Name: WP Custom Post Types
* Plugin URI: https://github.com/zajohnson/wp-custom-post-types
* Description: An ongoing build of custom post types.
* Version: 0.1
* Author: Zach Johnson
* Author URI: http://zachjohnson.name
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CPT_PREFIX', 'zjcpt_');

require PLUGIN_PATH . '/config.php';
require PLUGIN_PATH . '/class-CPT_meta_box.php';

foreach($cpts_to_load as $cpt => $load) {
    if($load)
        include PLUGIN_PATH . '/cpts/' . $cpt . '.php';
}