<?php

declare(strict_types=1);
defined('ABSPATH') || exit;

/**
 * If the browser of the visitor is not Chrome or Edge.
 * Display message,
 */
add_filter('the_content', function ($content) {
    if (strpos($content, '[gravityforms') === false) {
        return $content;
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $browsers = [
        '/chrome/i' => 'Chrome',
        '/edge/i' => 'Edge',
    ];

    foreach ($browsers as $regex => $value) {
        if (! preg_match($regex, $userAgent)) {
            continue;
        }

        return $content;
    }

    return str_replace('[gravityforms', '<p class="alert-danger | p-2">Dit formulier werkt helaas niet in de browser die u momenteel gebruikt. Open het formulier in <a href="https://www.microsoft.com/nl-nl/edge" target="_blank" rel="noopener noreferrer">Edge</a> of <a href="https://www.google.com/intl/nl_nl/chrome/" target="_blank" rel="noopener noreferrer">Chrome</a> om uw aanvraag te voltooien.</p> [gravityforms', $content);
});

add_action('init', function () {
    $labels = [
        'name'              => _x('Owner', 'taxonomy general name', 'hoekschewaard'),
        'singular_name'     => _x('Owner', 'taxonomy singular name', 'hoekschewaard'),
        'search_items'      => __('Search owners', 'hoekschewaard'),
        'all_items'         => __('All owners', 'hoekschewaard'),
        'parent_item'       => __('Parent owner', 'hoekschewaard'),
        'parent_item_colon' => __('Parent owner:', 'hoekschewaard'),
        'edit_item'         => __('Edit owner', 'hoekschewaard'),
        'update_item'       => __('Update owner', 'hoekschewaard'),
        'add_new_item'      => __('Add new owner', 'hoekschewaard'),
        'new_item_name'     => __('New owner name', 'hoekschewaard'),
        'menu_name'         => __('Owner', 'hoekschewaard'),
    ];

    register_taxonomy('form-owner', 'page', [
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => false,
        'show_in_rest'      => true,
    ]);
    register_taxonomy_for_object_type('category', 'page');
});

add_action('init', function () {
    $labels = [
        'name'              => _x('Link', 'taxonomy general name', 'hoekschewaard'),
        'singular_name'     => _x('Link', 'taxonomy singular name', 'hoekschewaard'),
        'search_items'      => __('Search Links', 'hoekschewaard'),
        'all_items'         => __('All Links', 'hoekschewaard'),
        'parent_item'       => __('Parent Link', 'hoekschewaard'),
        'parent_item_colon' => __('Parent Link:', 'hoekschewaard'),
        'edit_item'         => __('Edit Link', 'hoekschewaard'),
        'update_item'       => __('Update Link', 'hoekschewaard'),
        'add_new_item'      => __('Add new Link', 'hoekschewaard'),
        'new_item_name'     => __('New Link name', 'hoekschewaard'),
        'menu_name'         => __('Link', 'hoekschewaard'),
    ];

    register_taxonomy('form-links', 'page', [
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => false,
        'show_in_rest'      => true,
    ]);
});

/**
 * GravityPerks Limit Choices
 */
add_filter('gplc_remove_choices', '__return_false');
add_filter('gplc_pre_render_choice', function ($choice, $exceededLimit, $field, $form, $count) {
    $limit = rgar($choice, 'limit');
    $choicesLeft = max($limit - $count, 0);
    $message = sprintf('(%s plekken over)', $choicesLeft);
    $choice['text'] = sprintf('%s %s', $choice['text'], $message);

    return $choice;
}, 10, 5);


if (class_exists(\GF_Fields::class)) {
    \GF_Fields::register(new HW\Gravityforms\Fields\HWCodesValidation());
}
add_filter('gform_export_fields', [HW\Gravityforms\Export::class, 'modifyHeading'], 10, 1);
add_filter('gform_export_field_value', [HW\Gravityforms\Export::class, 'modifyValue'], 10, 4);

add_filter('gform_field_groups_form_editor', function ($field_groups) {
    $field_groups['hw_fields'] = [
        'name'   => 'hw_fields',
        'label'  => 'Gemeente Hoeksche Waard velden',
        'fields' => [],
    ];

    return $field_groups;
});

add_filter('owc_gravityforms_zaaksysteem_templates_to_validate', function ($templates) {
    $templates[] = 'template-mijn-zaken';
    $templates[] = 'template-mijn-zaken-main';

    return $templates;
});

add_filter('owc_gravityforms_digid_field_display_title', function () {
    return 'Klik hier om in te loggen';
});

// Increase default timeout for HTTP requests from default 5 to 30 seconds
// Notably the OpenKaarten plugins may need more time to load data layers.
add_filter( 'http_request_timeout', function () {return 30;} );

add_action(
	'rest_api_init',
	function () {
		\HW\DataLayers\HomeAddress::registerRestRoute();
	}
);
