<?php
/**
 * V3 SEO Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'seo',
	'name'        => __( 'SEO', 'geodirectory' ),
	'icon'        => 'fa-solid fa-magnifying-glass-chart',
	'description' => __( 'Manage page titles and meta descriptions for various GeoDirectory pages to optimize for search engines.', 'geodirectory' ),
	'subsections' => array(

		/**
		 * Subsection: CPT Archives
		 */
		array(
			'id'          => 'cpt_archives',
			'name'        => __( 'Post Type Archives', 'geodirectory' ),
			'description' => __( 'Settings for the main archive pages of your custom post types (e.g., /places/).', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'seo_cpt_helper_tags',
					'type'    => 'helper_tags',
					'label'   => __( 'Dynamic Tags', 'geodirectory' ),
					'placeholder' => '%%pt_name%% Archive',
					'options' => \AyeCode\GeoDirectory\Core\Seo\VariableReplacer::get_variables('pt'),
				),
				array(
					'id'      => 'seo_cpt_title',
					'type'    => 'text',
					'label'   => __( 'Page Title', 'geodirectory' ),
					'description' => __( 'The main H1 title for the page. Use tags like %%pt_name%%.', 'geodirectory' ),
					'placeholder' => '%%pt_name%% Archive',
					'active_placeholder' => true,
					'searchable' => array('seo', 'title', 'cpt', 'post type', 'archive'),
				),
				array(
					'id'      => 'seo_cpt_meta_title',
					'type'    => 'text',
					'label'   => __( 'Meta Title', 'geodirectory' ),
					'description' => __( 'The title that appears in the browser tab and search engine results.', 'geodirectory' ),
					'placeholder' => '%%pt_name%% in %%site_title%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'title', 'cpt', 'post type', 'archive'),
				),
				array(
					'id'      => 'seo_cpt_meta_description',
					'type'    => 'textarea',
					'label'   => __( 'Meta Description', 'geodirectory' ),
					'description' => __( 'The description used by search engines. Keep it concise.', 'geodirectory' ),
					'placeholder' => 'Find the best %%pt_plural_name%% on %%site_title%%.',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'description', 'cpt', 'post type', 'archive'),
				),
			),
		),

		/**
		 * Subsection: Category & Tag Archives
		 */
		array(
			'id'          => 'tax_archives',
			'name'        => __( 'Category & Tag Archives', 'geodirectory' ),
			'description' => __( 'Settings for your category and tag archive pages.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'seo_cpt_archive_helper_tags',
					'type'    => 'helper_tags',
					'label'   => __( 'Dynamic Tags', 'geodirectory' ),
					'placeholder' => '%%pt_name%% Archive',
					'options'   => \AyeCode\GeoDirectory\Core\Seo\VariableReplacer::get_variables('archive')
				),
				array(
					'id'      => 'seo_cat_archive_title',
					'type'    => 'text',
					'label'   => __( 'Category Page Title', 'geodirectory' ),
					'description' => __( 'The H1 title for category pages. Use tags like %%term_name%%.', 'geodirectory' ),
					'placeholder' => '%%term_name%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'title', 'category', 'archive', 'taxonomy'),
				),
				array(
					'id'      => 'seo_cat_archive_meta_title',
					'type'    => 'text',
					'label'   => __( 'Category Meta Title', 'geodirectory' ),
					'description' => __( 'The SEO title for category pages.', 'geodirectory' ),
					'placeholder' => '%%term_name%% - %%site_title%%',
					'class'   => 'active-placeholder',
					'searchable' => array('seo', 'meta', 'title', 'category', 'archive'),
				),
				array(
					'id'      => 'seo_cat_archive_meta_description',
					'type'    => 'textarea',
					'label'   => __( 'Category Meta Description', 'geodirectory' ),
					'description' => __( 'The SEO description for category pages.', 'geodirectory' ),
					'placeholder' => 'Find %%pt_plural_name%% in %%term_name%%.',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'description', 'category', 'archive'),
				),
				array(
					'id'      => 'seo_tag_archive_title',
					'type'    => 'text',
					'label'   => __( 'Tag Page Title', 'geodirectory' ),
					'description' => __( 'The H1 title for tag pages. Use tags like %%term_name%%.', 'geodirectory' ),
					'placeholder' => '%%term_name%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'title', 'tag', 'archive', 'taxonomy'),
				),
				array(
					'id'      => 'seo_tag_archive_meta_title',
					'type'    => 'text',
					'label'   => __( 'Tag Meta Title', 'geodirectory' ),
					'description' => __( 'The SEO title for tag pages.', 'geodirectory' ),
					'placeholder' => '%%term_name%% - %%site_title%%',
					'class'   => 'active-placeholder',
					'searchable' => array('seo', 'meta', 'title', 'tag', 'archive'),
				),
				array(
					'id'      => 'seo_tag_archive_meta_description',
					'type'    => 'textarea',
					'label'   => __( 'Tag Meta Description', 'geodirectory' ),
					'description' => __( 'The SEO description for tag pages.', 'geodirectory' ),
					'placeholder' => 'Find %%pt_plural_name%% tagged with %%term_name%%.',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'description', 'tag', 'archive'),
				),
			),
		),

		/**
		 * Subsection: Listing Detail Pages
		 */
		array(
			'id'          => 'single',
			'name'        => __( 'Listing Detail Pages', 'geodirectory' ),
			'description' => __( 'Settings for the single listing detail pages.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'seo_single_helper_tags',
					'type'    => 'helper_tags',
					'label'   => __( 'Dynamic Tags', 'geodirectory' ),
					'placeholder' => '%%pt_name%% Archive',
					'options'   => \AyeCode\GeoDirectory\Core\Seo\VariableReplacer::get_variables('single')
				),
				array(
					'id'      => 'seo_single_title',
					'type'    => 'text',
					'label'   => __( 'Page Title', 'geodirectory' ),
					'description' => __( 'The H1 title for the listing detail page. Use tags like %%post_title%%.', 'geodirectory' ),
					'placeholder' => '%%post_title%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'title', 'single', 'listing', 'details'),
				),
				array(
					'id'      => 'seo_single_meta_title',
					'type'    => 'text',
					'label'   => __( 'Meta Title', 'geodirectory' ),
					'description' => __( 'The SEO title for the listing detail page.', 'geodirectory' ),
					'placeholder' => '%%post_title%% - %%site_title%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'title', 'single', 'listing', 'details'),
				),
				array(
					'id'      => 'seo_single_meta_description',
					'type'    => 'textarea',
					'label'   => __( 'Meta Description', 'geodirectory' ),
					'description' => __( 'The SEO description for the listing detail page. Use tags like %%post_excerpt%%.', 'geodirectory' ),
					'placeholder' => '%%post_excerpt%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'description', 'single', 'listing', 'details'),
				),
			),
		),

		/**
		 * Subsection: Location Page
		 */
		array(
			'id'          => 'location_page',
			'name'        => __( 'Location Page', 'geodirectory' ),
			'description' => __( 'SEO settings for the main location page.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'seo_location_helper_tags',
					'type'    => 'helper_tags',
					'label'   => __( 'Dynamic Tags', 'geodirectory' ),
					'placeholder' => '%%pt_name%% Archive',
					'options'   => \AyeCode\GeoDirectory\Core\Seo\VariableReplacer::get_variables('location')
				),
				array(
					'id'      => 'seo_location_title',
					'type'    => 'text',
					'label'   => __( 'Page Title', 'geodirectory' ),
					'description' => __( 'The H1 title for the main location page.', 'geodirectory' ),
					'placeholder' => '%%location%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'title', 'location'),
				),
				array(
					'id'      => 'seo_location_meta_title',
					'type'    => 'text',
					'label'   => __( 'Meta Title', 'geodirectory' ),
					'description' => __( 'The SEO title for the main location page.', 'geodirectory' ),
					'placeholder' => '%%location%% - %%site_title%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'title', 'location'),
				),
				array(
					'id'      => 'seo_location_meta_description',
					'type'    => 'textarea',
					'label'   => __( 'Meta Description', 'geodirectory' ),
					'description' => __( 'The SEO description for the main location page.', 'geodirectory' ),
					'placeholder' => '%%location%% - %%site_title%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'description', 'location'),
				),
			),
		),

		/**
		 * Subsection: Search Page
		 */
		array(
			'id'          => 'search_page',
			'name'        => __( 'Search Page', 'geodirectory' ),
			'description' => __( 'SEO settings for the search results page.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'seo_location_helper_tags',
					'type'    => 'helper_tags',
					'label'   => __( 'Dynamic Tags', 'geodirectory' ),
					'placeholder' => '%%pt_name%% Archive',
					'options'   => \AyeCode\GeoDirectory\Core\Seo\VariableReplacer::get_variables('search')
				),
				array(
					'id'      => 'seo_search_title',
					'type'    => 'text',
					'label'   => __( 'Page Title', 'geodirectory' ),
					'description' => __( 'The H1 title for the search results page.', 'geodirectory' ),
					'placeholder' => 'Search Results',
					'active_placeholder' => true,
					'searchable' => array('seo', 'title', 'search'),
				),
				array(
					'id'      => 'seo_search_meta_title',
					'type'    => 'text',
					'label'   => __( 'Meta Title', 'geodirectory' ),
					'description' => __( 'The SEO title for the search results page.', 'geodirectory' ),
					'placeholder' => 'Search Results - %%site_title%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'title', 'search'),
				),
				array(
					'id'      => 'seo_search_meta_description',
					'type'    => 'textarea',
					'label'   => __( 'Meta Description', 'geodirectory' ),
					'description' => __( 'The SEO description for the search results page.', 'geodirectory' ),
					'placeholder' => 'Search results for your query on %%site_title%%.',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'description', 'search'),
				),
			),
		),

		/**
		 * Subsection: Add Listing Page
		 */
		array(
			'id'          => 'add_listing_page',
			'name'        => __( 'Add Listing Page', 'geodirectory' ),
			'description' => __( 'SEO settings for the "Add Listing" and "Edit Listing" pages.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'seo_location_helper_tags',
					'type'    => 'helper_tags',
					'label'   => __( 'Dynamic Tags', 'geodirectory' ),
					'placeholder' => '%%pt_name%% Archive',
					'options'   => \AyeCode\GeoDirectory\Core\Seo\VariableReplacer::get_variables('add-listing')
				),
				array(
					'id'      => 'seo_add_listing_title',
					'type'    => 'text',
					'label'   => __( 'Add Listing Page Title', 'geodirectory' ),
					'description' => __( 'The H1 title for the "Add Listing" page.', 'geodirectory' ),
					'placeholder' => 'Add Listing',
					'active_placeholder' => true,
					'searchable' => array('seo', 'title', 'add listing', 'submit'),
				),
				array(
					'id'      => 'seo_add_listing_title_edit',
					'type'    => 'text',
					'label'   => __( 'Edit Listing Page Title', 'geodirectory' ),
					'description' => __( 'The H1 title for the "Edit Listing" page.', 'geodirectory' ),
					'placeholder' => 'Edit %%post_title%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'title', 'edit listing', 'submit'),
				),
				array(
					'id'      => 'seo_add_listing_meta_title',
					'type'    => 'text',
					'label'   => __( 'Meta Title', 'geodirectory' ),
					'description' => __( 'The SEO title for the add/edit listing pages.', 'geodirectory' ),
					'placeholder' => 'Add Listing - %%site_title%%',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'title', 'add listing', 'edit'),
				),
				array(
					'id'      => 'seo_add_listing_meta_description',
					'type'    => 'textarea',
					'label'   => __( 'Meta Description', 'geodirectory' ),
					'description' => __( 'The SEO description for the add/edit listing pages.', 'geodirectory' ),
					'placeholder' => 'Submit your listing to %%site_title%%.',
					'active_placeholder' => true,
					'searchable' => array('seo', 'meta', 'description', 'add listing', 'edit'),
				),
			),
		),
	)
);
