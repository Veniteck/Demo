<?php
/**
* Taxonomies
*/

// Custom taxonomies
add_action('init', function () {
    $custom_taxonomies = array(
        array(
            'key' => 'country',
            'singular' => 'Country',
            'plural' => 'Countries',
            'hierarchical' => true,
            'public' => false,
            'post_type_for' => 'resource'
        ),
        array(
            'key' => 'media_type',
            'singular' => 'Media Type',
            'plural' => 'Media Types',
            'hierarchical' => true,
            'public' => false,
            'post_type_for' => 'resource'
        )
    );

    foreach ($custom_taxonomies as $custom_tax) {
        $labels = array(
            'name' => $custom_tax['plural'],
            'singular_name' => $custom_tax['singular'],
            'menu_name' => $custom_tax['plural'],
            'all_items' => 'All ' . $custom_tax['plural'],
            'parent_item' => 'Parent ' . $custom_tax['singular'],
            'parent_item_colon' => 'Parent ' . $custom_tax['singular'] . ':',
            'new_item_name' => 'New ' . $custom_tax['singular'] . ' Name',
            'add_new_item' => 'Add New ' . $custom_tax['singular'],
            'edit_item' => 'Edit ' . $custom_tax['singular'],
            'update_item' => 'Update ' . $custom_tax['singular'],
            'separate_items_with_commas' => 'Separate ' . $custom_tax['singular'] . ' with commas',
            'search_items' => 'Search ' . $custom_tax['plural'],
            'add_or_remove_items' => 'Add or remove ' . $custom_tax['plural'],
            'choose_from_most_used' => 'Choose from the most used ' . $custom_tax['plural'],
        );
        $args = array(
            'labels' => $labels,
            'hierarchical' => $custom_tax['hierarchical'],
            'public' => $custom_tax['public'],
            'show_ui' => true,
            'show_admin_column' => false,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
        );
        register_taxonomy($custom_tax['key'], $custom_tax['post_type_for'], $args);
        register_taxonomy_for_object_type($custom_tax['key'], $custom_tax['post_type_for']);
    }
});
