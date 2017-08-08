<?php
global $_nonHierarchicalCheckboxTaxonomies;
$_nonHierarchicalCheckboxTaxonomies = array();

function add_post_type($slug, $name, $singleName, $args = array()) {
    $_defaultPTLabelPlural = array(
        'name'               => true,
        'singular_name'      => false,
        'add_new'            => false,
        'add_new_item'       => false,
        'edit_item'          => false,
        'new_item'           => false,
        'all_items'          => true,
        'view_item'          => false,
        'search_items'       => true,
        'not_found'          => true,
        'not_found_in_trash' => true,
        'parent_item_colon'  => false,
        'menu_name'          => true
    );

    $_defaultPTLabels = array(
        'name'               => '%s',
        'singular_name'      => '%s',
        'add_new'            => __('Add %s', THEME_TEXT),
        'add_new_item'       => __('Add New %s', THEME_TEXT),
        'edit_item'          => __('Edit %s', THEME_TEXT),
        'new_item'           => __('New %s', THEME_TEXT),
        'all_items'          => __('All %s', THEME_TEXT),
        'view_item'          => __('View %s', THEME_TEXT),
        'search_items'       => __('Search %s', THEME_TEXT),
        'not_found'          => __('No %s found', THEME_TEXT),
        'not_found_in_trash' => __('No %s found in Trash', THEME_TEXT),
        'parent_item_colon'  => __('Parent %s:', THEME_TEXT),
        'menu_name'          => '%s'
    );

    $_defaultPT = array(
        'labels'             => $_defaultPTLabels,

        // Visibility
        'public'             => false,
        //'publicly_queryable' => false,
        //'exclude_from_search'=> true,
        'has_archive'        => false,
        //'query_var'          => false,

        // UI
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => false,

        //'rewrite'            => array( 'slug' => '%s' ),
        'capability_type'    => 'post',

        // Support
        'hierarchical'       => false,
        'menu_position'      => 21,
        'supports'           => array( 'title', 'editor', 'thumbnail') //  'page-attributes'
    );

    $params = $_defaultPT;

    foreach ($params['labels'] as $label => $text) {
        if (isset($_defaultPTLabelPlural[$label]) && $_defaultPTLabelPlural[$label])
            $params['labels'][$label] = sprintf($params['labels'][$label], $name);
        else
            $params['labels'][$label] = sprintf($params['labels'][$label], $singleName);
    }

    $params = array_merge($params, $args);

    register_post_type($slug, $params);
}

function add_taxonomy($postType, $slug, $name, $singleName, $args = array()) {
    global $_nonHierarchicalCheckboxTaxonomies;

    $_defaultTaxLabelPlural = array(
        'name'                       => true,
        'singular_name'              => false,
        'add_new_item'               => false,
        'new_item_name'              => false,
        'edit_item'                  => false,
        'all_items'                  => true,
        'view_item'                  => false,
        'update_item'                => false,
        'search_items'               => true,
        'not_found'                  => true,
        'parent_item'                => false,
        'parent_item_colon'          => false,
        'menu_name'                  => true,
        'popular_items'              => true,
        'separate_items_with_commas' => true,
        'add_or_remove_items'        => true,
        'choose_from_most_used'      => true,
    );

    $_defaultTaxLabels = array(
        'name'                       => __('%s', THEME_TEXT),
        'singular_name'              => __('%s', THEME_TEXT),
        'add_new_item'               => __('Add New %s', THEME_TEXT),
        'new_item_name'              => __('New %s Name', THEME_TEXT),
        'edit_item'                  => __('Edit %s', THEME_TEXT),
        'all_items'                  => __('All %s', THEME_TEXT),
        'view_item'                  => __('View %s', THEME_TEXT),
        'update_item'                => __('Update %s', THEME_TEXT),
        'search_items'               => __('Search %s', THEME_TEXT),
        'not_found'                  => __('No %s found', THEME_TEXT),
        'parent_item'                => __('Parent %s', THEME_TEXT),
        'parent_item_colon'          => __('Parent %s:', THEME_TEXT),
        'menu_name'                  => __('%s', THEME_TEXT),
        'popular_items'              => __('Popular %s', THEME_TEXT),
        'separate_items_with_commas' => __('Separate %s with commas', THEME_TEXT),
        'add_or_remove_items'        => __('Add or remove %s', THEME_TEXT),
        'choose_from_most_used'      => __('Choose from the most used %s', THEME_TEXT),
    );

    $_defaultPT = array(
        'labels'             => $_defaultTaxLabels,

        // Visibility
        'public'             => false,
        //'query_var'          => false,

        // UI
        'show_ui'            => true,
        'show_in_nav_menus'  => false,
        'show_tagcloud'      => false,

        //'rewrite'            => array( 'slug' => '%s' ),

        // Support
        'hierarchical'       => false,
        'use_checkboxes'     => true,
        'sort'               => false
    );

    $params = $_defaultPT;

    foreach ($params['labels'] as $label => $text) {
        if (isset($_defaultTaxLabelPlural[$label]) && $_defaultTaxLabelPlural[$label])
            $params['labels'][$label] = sprintf($params['labels'][$label], $name);
        else
            $params['labels'][$label] = sprintf($params['labels'][$label], $singleName);
    }

    $params = array_merge($params, $args);

    if (!empty($params['use_checkboxes'])) {
        $_nonHierarchicalCheckboxTaxonomies[] = $slug;
        $params['meta_box_cb'] = '_taxonomy_checklist_box_nonhierachical_box';
    }

    unset($params['use_checkboxes']);

    register_taxonomy($slug, $postType, $params);
    register_taxonomy_for_object_type($slug, $postType);
}

add_filter('wp_insert_post_data', function($data) {
    global $wp_taxonomies, $_nonHierarchicalCheckboxTaxonomies;

    foreach ($_nonHierarchicalCheckboxTaxonomies as $t)
        $wp_taxonomies[$t]->hierarchical = true;

    return $data;
});

add_action('save_post', function() {
    global $wp_taxonomies, $_nonHierarchicalCheckboxTaxonomies;

    foreach ($_nonHierarchicalCheckboxTaxonomies as $t)
        $wp_taxonomies[$t]->hierarchical = false;
});

function _taxonomy_checklist_box_nonhierachical_box($post, $box) {
    add_filter('post_edit_category_parent_dropdown_args', '_taxonomy_checklist_box_nonhierachical_filter_parent');

    post_categories_meta_box( $post, $box );

    remove_filter('post_edit_category_parent_dropdown_args', '_taxonomy_checklist_box_nonhierachical_filter_parent');
}

function _taxonomy_checklist_box_nonhierachical_filter_parent($args) {
    $args['child_of'] = -1;
    $args['class'] = 'hidden';

    return $args;
}