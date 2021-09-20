<?php
/*
* plugin Name: GitSearch Repositories
* Description: Cadastre o token e utilize o shortcode [gitsearch] em qualquer página do site.
* Version: 1.0
* Author: Renato Marques
*
*/

if (!defined('ABSPATH')) {
    exit;
}

//load function
require_once(plugin_dir_path(__FILE__) . '/includes/gitsearch-scripts.php');


add_action('wp_enqueue_scripts', 'gitearch_add_scripts');


//load function
require_once(plugin_dir_path(__FILE__) . '/includes/gitsearch-function.php');

//adiciona o shortcode
add_shortcode('gitsearch', 'shortcode_gitsearchRepositories');


//load class
require_once(plugin_dir_path(__FILE__) . '/includes/gitsearch-class.php');

GitSearchToken::getInstance();
