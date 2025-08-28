<?php
/**
 * CPT Settings Configuration
 * Uses a single section with multiple subsections for organization.
 *
 * @var stdClass $cpt The CPT object, passed from Dynamic_CPT_Settings::get_config().
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

return [
	'id'          => 'cpt_settings',
	'name'        => __( 'Settings', 'geodirectory' ),
	'icon'        => 'fa-solid fa-gear',
	'description' => '',
//	'icon'        => 'fas fa-cog',
	'subsections' => [
		/**
		 * Subsection: General Settings
		 */
		[
			'id'          => 'general',
			'name'        => __( 'General Settings', 'geodirectory' ),
			'description' => __( 'Manage core details, features, and functionality for this post type.', 'geodirectory' ),
			'fields'      => [
				'slug' => [
					'type'  => 'text',
					'label' => __( 'URL Slug', 'geodirectory' ),
					'desc'  => __( 'The URL slug for this post type. Usually plural. Alphanumeric, underscores, and hyphens only. <b class="text-danger">Warning: Changing this on a live site can break URLs.</b>', 'geodirectory' ),
				],
				'order' => [
					'type'  => 'number',
					'label' => __( 'Order in Post Type List', 'geodirectory' ),
					'desc'  => __( 'The numerical order in which this post type appears in lists.', 'geodirectory' ),
				],
				'menu_icon' => [
					'type'    => 'font-awesome', // Or your custom 'dashicon' type if available
					'label'   => __( 'Menu Icon', 'geodirectory' ),
					'desc'    => __( 'The icon used in the WordPress admin menu.', 'geodirectory' ),
					'default' => 'dashicons-location-alt',
				],
				'default_image' => [
					'type'  => 'image',
					'label' => __( 'Default Image', 'geodirectory' ),
					'desc'  => __( 'Used if a listing has no image and its category has no default image.', 'geodirectory' ),
				],
				'description' => [
					'type'  => 'textarea',
					'label' => __( 'Description', 'geodirectory' ),
					'desc'  => __( 'A short, descriptive summary of what this post type is.', 'geodirectory' ),
				],
				[
					'type' => 'divider',
					'label' => __( 'Feature Toggles', 'geodirectory' )
				],
				'disable_comments' => [
					'type'    => 'toggle',
					'label'   => __( 'Disable Comments', 'geodirectory' ),
					'desc'    => __( 'Turn off comments entirely for this post type.', 'geodirectory' ),
					'default' => false,
				],
				'disable_reviews' => [
					'type'    => 'toggle',
					'label'   => __( 'Disable Ratings', 'geodirectory' ),
					'desc'    => __( 'Disable the star rating system without disabling comments.', 'geodirectory' ),
					'default' => false,
				],
				'single_review' => [
					'type'    => 'toggle',
					'label'   => __( 'Single Review Per User', 'geodirectory' ),
					'desc'    => __( 'Restrict users to leaving only one review per listing.', 'geodirectory' ),
					'default' => false,
				],
				'disable_favorites' => [
					'type'    => 'toggle',
					'label'   => __( 'Disable Favorites', 'geodirectory' ),
					'desc'    => __( 'Disable the "add to favorites" feature for this post type.', 'geodirectory' ),
					'default' => false,
				],
				'disable_frontend_add' => [
					'type'    => 'toggle',
					'label'   => __( 'Disable Frontend Submissions', 'geodirectory' ),
					'desc'    => __( 'Prevent users from submitting this post type via the frontend "Add Listing" form.', 'geodirectory' ),
					'default' => false,
				],
			],
		],

		/**
		 * Subsection: Labels
		 */
		[
			'id'          => 'labels',
			'name'        => __( 'Labels', 'geodirectory' ),
			'description' => __( 'Customize the text used for this post type throughout the WordPress admin area.', 'geodirectory' ),
			'fields'      => [
				'name' => [
					'type'  => 'text',
					'label' => __( 'Plural Name', 'geodirectory' ),
					'desc'  => __( 'General name for the post type, usually plural. Example: "Places"', 'geodirectory' ),
				],
				'singular_name' => [
					'type'  => 'text',
					'label' => __( 'Singular Name', 'geodirectory' ),
					'desc'  => __( 'Name for one object of this post type. Example: "Place"', 'geodirectory' ),
				],
				'add_new' => [
					'type'  => 'text',
					'label' => __( 'Add New', 'geodirectory' ),
					'desc'  => __( 'The "Add New" text in the admin menu. Example: "Add New"', 'geodirectory' ),
				],
				'add_new_item' => [
					'type'  => 'text',
					'label' => __( 'Add New Item', 'geodirectory' ),
					'desc'  => __( 'Example: "Add New Place"', 'geodirectory' ),
				],
				'edit_item' => [
					'type'  => 'text',
					'label' => __( 'Edit Item', 'geodirectory' ),
					'desc'  => __( 'Example: "Edit Place"', 'geodirectory' ),
				],
				'new_item' => [
					'type'  => 'text',
					'label' => __( 'New Item', 'geodirectory' ),
					'desc'  => __( 'Example: "New Place"', 'geodirectory' ),
				],
				'view_item' => [
					'type'  => 'text',
					'label' => __( 'View Item', 'geodirectory' ),
					'desc'  => __( 'Example: "View Place"', 'geodirectory' ),
				],
				'search_items' => [
					'type'  => 'text',
					'label' => __( 'Search Items', 'geodirectory' ),
					'desc'  => __( 'Example: "Search Places"', 'geodirectory' ),
				],
				'not_found' => [
					'type'  => 'text',
					'label' => __( 'Not Found', 'geodirectory' ),
					'desc'  => __( 'Example: "No Places Found"', 'geodirectory' ),
				],
				'not_found_in_trash' => [
					'type'  => 'text',
					'label' => __( 'Not Found in Trash', 'geodirectory' ),
					'desc'  => __( 'Example: "No Places Found in Trash"', 'geodirectory' ),
				],
			]
		],

		/**
		 * Subsection: Display & Templates
		 */
		[
			'id'          => 'templates',
			'name'        => __( 'Display & Templates', 'geodirectory' ),
			'description' => __( 'Configure frontend display, template assignments, and author page behavior.', 'geodirectory' ),
			'fields'      => [
				'page_add' => [
					'type'    => 'select',
					'label'   => __( 'Add Listing Template', 'geodirectory' ),
					'desc'    => __( 'Override the default template for the "Add Listing" page.', 'geodirectory' ),
					'options' => [], // You will need to populate this from a helper function
					'class'   => 'aui-select2',
				],
				'page_details' => [
					'type'    => 'select',
					'label'   => __( 'Details Page Template', 'geodirectory' ),
					'desc'    => __( 'Override the default template for the single listing detail page.', 'geodirectory' ),
					'options' => [],
					'class'   => 'aui-select2',
				],
				'page_archive' => [
					'type'    => 'select',
					'label'   => __( 'Archive Page Template', 'geodirectory' ),
					'desc'    => __( 'Override the default template for archive pages (categories, tags, etc.).', 'geodirectory' ),
					'options' => [],
					'class'   => 'aui-select2',
				],
				'page_archive_item' => [
					'type'    => 'select',
					'label'   => __( 'Archive Item Template', 'geodirectory' ),
					'desc'    => __( 'Override the default template for a single item within an archive loop.', 'geodirectory' ),
					'options' => [],
					'class'   => 'aui-select2',
				],
				[
					'type' => 'divider',
					'label' => __( 'Author Page Settings', 'geodirectory' )
				],
				'author_posts_private' => [
					'type'    => 'select',
					'label'   => __( 'Author Posts Visibility', 'geodirectory' ),
					'desc'    => __( 'Set the visibility of posts on the author page.', 'geodirectory' ),
					'options' => [
						'0' => __( 'Public', 'geodirectory' ),
						'1' => __( 'Private', 'geodirectory' ),
					],
					'default' => '0',
				],
				'author_favorites_private' => [
					'type'    => 'select',
					'label'   => __( 'Author Favorites Visibility', 'geodirectory' ),
					'desc'    => __( 'Set the visibility of favorites on the author page.', 'geodirectory' ),
					'options' => [
						'0' => __( 'Public', 'geodirectory' ),
						'1' => __( 'Private', 'geodirectory' ),
					],
					'default' => '0',
				],
				'limit_posts' => [
					'type'        => 'number',
					'label'       => __( 'Limit Posts Per User', 'geodirectory' ),
					'desc'        => __( 'Limit total posts allowed per user for this post type. Leave blank for unlimited.', 'geodirectory' ),
					'placeholder' => __( 'Unlimited', 'geodirectory' ),
					'extra_attributes' => ['min' => 0, 'step' => 1],
				],
			]
		],

		/**
		 * Subsection: Advanced
		 */
		[
			'id'          => 'advanced',
			'name'        => __( 'Advanced', 'geodirectory' ),
			'description' => __( 'Configure SEO overrides and other specialized core features.', 'geodirectory' ),
			'fields'      => [
				'title' => [
					'type'  => 'text',
					'label' => __( 'Archive Page Title (H1)', 'geodirectory' ),
					'desc'  => __( 'Override the main H1 title for this post type\'s archive page.', 'geodirectory' ),
				],
				'meta_title' => [
					'type'  => 'text',
					'label' => __( 'Meta Title', 'geodirectory' ),
					'desc'  => __( 'Override the meta title (in the browser tab and search results) for the archive page.', 'geodirectory' ),
				],
				'meta_description' => [
					'type'  => 'textarea',
					'label' => __( 'Meta Description', 'geodirectory' ),
					'desc'  => __( 'Override the meta description for the archive page.', 'geodirectory' ),
				],
				[
					'type' => 'divider',
					'label' => __( 'Core Features', 'geodirectory' )
				],
				'classifieds_measures' => [
					'type'    => 'select',
					'label'   => __( 'Include Classifieds Measures', 'geodirectory' ),
					'desc'    => __( 'Select the measurement features to include for Classifieds or Real Estate.', 'geodirectory' ),
					'options' => [
						'none' => __( 'None', 'geodirectory' ),
						'basic' => __( 'Basic Measures (e.g., sq ft)', 'geodirectory' ),
						'advanced' => __( 'Advanced Measures (e.g., bedrooms, bathrooms)', 'geodirectory' ),
					],
					'default' => 'none',
				],
			]
		],
	]
];
