<?php
/**
 * CPT Settings Configuration
 * Uses a single section with multiple subsections for organization.
 *
 * @var stdClass $cpt The CPT object, passed from Dynamic_CPT_Settings::get_config().
 */

use AyeCode\GeoDirectory\Admin\Utils\FormHelper;

if ( ! defined( 'ABSPATH' ) ) { exit; }

return [
	'id'          => 'cpt_settings',
	'name'        => __( 'Settings', 'geodirectory' ),
	'icon'        => 'fa-solid fa-gear',
	'description' => '',
	'subsections' => [
		/**
		 * Subsection: General Settings
		 */
		[
			'id'          => 'general',
			'name'        => __( 'General Settings', 'geodirectory' ),
			'description' => __( 'Manage core details, features, and functionality for this post type.', 'geodirectory' ),
			'fields'      => [
				[
					'id'      => 'post_type',
					'type'    => 'text',
					'label'   => __( 'Post type', 'geodirectory' ),
					'description' => __( 'The system name for the post type. Cannot be changed after creation. Lower-case characters and underscores only.', 'geodirectory' ),
					'placeholder' => '',
					'extra_attributes' => [
						'disabled'  => true,
						'required' => 'required',
						'maxlength' => 17
					],
					'searchable' => ['post type', 'name', 'system', 'slug'],
				],
				[
					'id'    => 'slug',
					'type'  => 'text',
					'label' => __( 'URL Slug', 'geodirectory' ),
					'description'  => __( 'The URL slug for this post type. Usually plural. Alphanumeric, underscores, and hyphens only. <b class="text-danger">Warning: Changing this on a live site can break URLs.</b>', 'geodirectory' ),
					'extra_attributes' => [
						'required' => 'required',
						'maxlength' => 20
					],
					'searchable' => ['slug', 'url', 'permalink', 'link'],
				],
				[
					'id'    => 'order',
					'type'  => 'number',
					'label' => __( 'Order in Post Type List', 'geodirectory' ),
					'description'  => __( 'The numerical order in which this post type appears in lists.', 'geodirectory' ),
					'searchable' => ['order', 'position', 'sort', 'list'],
				],
				[
					'id'      => 'menu_icon',
					'type'    => 'font-awesome', // Mapped from 'dashicon'
					'label'   => __( 'Menu Icon', 'geodirectory' ),
					'description'    => __( 'The icon used in the WordPress admin menu.', 'geodirectory' ),
					'default' => 'fa-solid fa-location-dot', // Equivalent to 'admin-site' or generic placeholder
					'searchable' => ['icon', 'menu', 'admin', 'dashicon'],
				],
				[
					'id'    => 'default_image',
					'type'  => 'image',
					'label' => __( 'Default Image', 'geodirectory' ),
					'description'  => __( 'Used if a listing has no image and its category has no default image.', 'geodirectory' ),
					'searchable' => ['image', 'default', 'thumbnail', 'placeholder'],
				],
				[
					'id'    => 'description',
					'type'  => 'textarea',
					'label' => __( 'Description', 'geodirectory' ),
					'description'  => __( 'A short, descriptive summary of what this post type is.', 'geodirectory' ),
					'searchable' => ['description', 'summary', 'text'],
				],
				[
					'id'    => 'disable_comments',
					'type'    => 'toggle', // Mapped from 'checkbox'
					'label'   => __( 'Disable Comments', 'geodirectory' ),
					'description'    => __( 'Turn off comments entirely for this post type.', 'geodirectory' ),
					'default' => false,
					'searchable' => ['comments', 'disable', 'discussion', 'turn off'],
				],
				[
					'id'    => 'disable_reviews',
					'type'    => 'toggle', // Mapped from 'checkbox'
					'label'   => __( 'Disable Ratings', 'geodirectory' ),
					'description'    => __( 'Disable the star rating system without disabling comments.', 'geodirectory' ),
					'default' => false,
					'searchable' => ['ratings', 'reviews', 'stars', 'disable'],
				],
				[
					'id'    => 'single_review',
					'type'    => 'toggle', // Mapped from 'checkbox'
					'label'   => __( 'Single Review Per User', 'geodirectory' ),
					'description'    => __( 'Restrict users to leaving only one review per listing.', 'geodirectory' ),
					'default' => false,
					'searchable' => ['review', 'single', 'limit', 'one per user'],
				],
				[
					'id'    => 'disable_favorites',
					'type'    => 'toggle', // Mapped from 'checkbox'
					'label'   => __( 'Disable Favorites', 'geodirectory' ),
					'description'    => __( 'Disable the "add to favorites" feature for this post type.', 'geodirectory' ),
					'default' => false,
					'searchable' => ['favorites', 'disable', 'bookmark', 'like'],
				],
				[
					'id'    => 'disable_frontend_add',
					'type'    => 'toggle', // Mapped from 'checkbox'
					'label'   => __( 'Disable Frontend Submissions', 'geodirectory' ),
					'description'    => __( 'Prevent users from submitting this post type via the frontend "Add Listing" form.', 'geodirectory' ),
					'default' => false,
					'searchable' => ['frontend', 'add', 'submit', 'disable', 'submission'],
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
				[
					'id'    => 'name',
					'type'  => 'text',
					'label' => __( 'Plural Name', 'geodirectory' ),
					'description'  => __( 'General name for the post type, usually plural. Example: "Businesses"', 'geodirectory' ),
					'extra_attributes' => ['required' => 'required'],
					'searchable' => ['label', 'name', 'plural', 'text'],
				],
				[
					'id'    => 'singular_name',
					'type'  => 'text',
					'label' => __( 'Singular Name', 'geodirectory' ),
					'description'  => __( 'Name for one object of this post type. Example: "Business"', 'geodirectory' ),
					'extra_attributes' => ['required' => 'required'],
					'searchable' => ['label', 'name', 'singular', 'text'],
				],
				[
					'id'    => 'add_new',
					'type'  => 'text',
					'label' => __( 'Add New', 'geodirectory' ),
					'description'  => __( 'The "Add New" text in the admin menu. Example: "Add New"', 'geodirectory' ),
					'searchable' => ['label', 'add new', 'text'],
				],
				[
					'id'    => 'add_new_item',
					'type'  => 'text',
					'label' => __( 'Add New Item', 'geodirectory' ),
					'description'  => __( 'Example: "Add New Business"', 'geodirectory' ),
					'searchable' => ['label', 'add new item', 'text'],
				],
				[
					'id'    => 'edit_item',
					'type'  => 'text',
					'label' => __( 'Edit Item', 'geodirectory' ),
					'description'  => __( 'Example: "Edit Business"', 'geodirectory' ),
					'searchable' => ['label', 'edit item', 'text'],
				],
				[
					'id'    => 'new_item',
					'type'  => 'text',
					'label' => __( 'New Item', 'geodirectory' ),
					'description'  => __( 'Example: "New Business"', 'geodirectory' ),
					'searchable' => ['label', 'new item', 'text'],
				],
				[
					'id'    => 'view_item',
					'type'  => 'text',
					'label' => __( 'View Item', 'geodirectory' ),
					'description'  => __( 'Example: "View Business"', 'geodirectory' ),
					'searchable' => ['label', 'view item', 'text'],
				],
				[
					'id'    => 'search_items',
					'type'  => 'text',
					'label' => __( 'Search Items', 'geodirectory' ),
					'description'  => __( 'Example: "Search Businesses"', 'geodirectory' ),
					'searchable' => ['label', 'search items', 'text'],
				],
				[
					'id'    => 'not_found',
					'type'  => 'text',
					'label' => __( 'Not Found', 'geodirectory' ),
					'description'  => __( 'Example: "No Businesses Found"', 'geodirectory' ),
					'searchable' => ['label', 'not found', 'text'],
				],
				[
					'id'    => 'not_found_in_trash',
					'type'  => 'text',
					'label' => __( 'Not Found in Trash', 'geodirectory' ),
					'description'  => __( 'Example: "No Businesses Found in Trash"', 'geodirectory' ),
					'searchable' => ['label', 'not found in trash', 'text'],
				],
				[
					'id'    => 'listing_owner', // Migrated from `label_listing_owner`
					'type'  => 'text',
					'label' => __( 'Listing Owner', 'geodirectory' ),
					'description'  => __( 'The label for the listing owner. Example: "Business Owner"', 'geodirectory' ),
					'placeholder' => __( 'Listing Owner', 'geodirectory' ),
					'searchable' => ['label', 'owner', 'author', 'text'],
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
				[
					'id'    => 'page_add',
					'type'    => 'select', // Mapped from 'single_select_page'
					'label'   => __( 'Add Listing Page', 'geodirectory' ),
					'description'    => __( 'Override the default page for the "Add Listing" form.', 'geodirectory' ),
					'options' => FormHelper::get_pages_as_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => ['template', 'page', 'add listing', 'form', 'assign'],
				],
				[
					'id'    => 'page_details',
					'type'    => 'select',
					'label'   => __( 'Details Page Template', 'geodirectory' ),
					'description'    => __( 'Override the default template for the single listing detail page.', 'geodirectory' ),
					'options' => FormHelper::get_pages_as_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => ['template', 'page', 'details', 'single', 'assign'],
				],
				[
					'id'    => 'page_archive',
					'type'    => 'select',
					'label'   => __( 'Archive Page Template', 'geodirectory' ),
					'description'    => __( 'Override the default template for archive pages (categories, tags, etc.).', 'geodirectory' ),
					'options' => FormHelper::get_pages_as_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => ['template', 'page', 'archive', 'category', 'tag', 'assign'],
				],
				[
					'id'    => 'page_archive_item',
					'type'    => 'select',
					'label'   => __( 'Archive Item Template', 'geodirectory' ),
					'description'    => __( 'Override the default template for a single item within an archive loop.', 'geodirectory' ),
					'options' => FormHelper::get_pages_as_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => ['template', 'page', 'archive item', 'loop', 'card', 'assign'],
				],
				[
					'id'    => 'author_posts_private',
					'type'    => 'select',
					'label'   => __( 'Author Posts Visibility', 'geodirectory' ),
					'description'    => __( 'Set the visibility of posts on the author page.', 'geodirectory' ),
					'options' => [
						'0' => __( 'Public', 'geodirectory' ),
						'1' => __( 'Private', 'geodirectory' ),
					],
					'default' => '0',
					'searchable' => ['author', 'posts', 'visibility', 'public', 'private'],
				],
				[
					'id'    => 'author_favorites_private',
					'type'    => 'select',
					'label'   => __( 'Author Favorites Visibility', 'geodirectory' ),
					'description'    => __( 'Set the visibility of favorites on the author page.', 'geodirectory' ),
					'options' => [
						'0' => __( 'Public', 'geodirectory' ),
						'1' => __( 'Private', 'geodirectory' ),
					],
					'default' => '0',
					'searchable' => ['author', 'favorites', 'visibility', 'public', 'private'],
				],
				[
					'id'    => 'limit_posts',
					'type'        => 'number',
					'label'       => __( 'Limit Posts Per User', 'geodirectory' ),
					'description'        => __( 'Limit total posts allowed per user for this post type. Leave blank or enter 0 for unlimited.', 'geodirectory' ),
					'placeholder' => __( 'Unlimited', 'geodirectory' ),
					'extra_attributes' => ['min' => 0, 'step' => 1],
					'searchable' => ['limit', 'posts', 'user', 'max', 'count'],
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
				[
					'id'    => 'title',
					'type'  => 'text',
					'label' => __( 'Archive Page Title (H1)', 'geodirectory' ),
					'description'  => __( 'Override the main H1 title for this post type\'s archive page.', 'geodirectory' ),
					'searchable' => ['seo', 'title', 'h1', 'archive', 'override'],
				],
				[
					'id'    => 'meta_title',
					'type'  => 'text',
					'label' => __( 'Meta Title', 'geodirectory' ),
					'description'  => __( 'Override the meta title (in the browser tab and search results) for the archive page.', 'geodirectory' ),
					'searchable' => ['seo', 'meta title', 'browser title', 'archive', 'override'],
				],
				[
					'id'    => 'meta_description',
					'type'  => 'textarea',
					'label' => __( 'Meta Description', 'geodirectory' ),
					'description'  => __( 'Override the meta description for the archive page.', 'geodirectory' ),
					'searchable' => ['seo', 'meta description', 'archive', 'override'],
				],
			]
		],
	]
];
