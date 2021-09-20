<?php
//Load Scripts
function gitearch_add_scripts()
{
    wp_enqueue_style('gtisearch_style', plugin_dir_url(__FILE__) . '../css/gitsearch-style.min.css');
    wp_enqueue_script('gtisearch_script', plugin_dir_url(__FILE__) . '../js/gitsearch-main.min.js');

    $gitsearch_script_params = array(
        'gitsearchtoken' => get_option('gitsearchtoken')
    );
    wp_localize_script('gtisearch_script', 'gitsearch_script_params', $gitsearch_script_params);
}
