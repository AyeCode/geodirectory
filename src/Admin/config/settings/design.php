<?php
/**
 * V3 Design Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'design',
	'name'        => __( 'Design', 'geodirectory' ),
	'icon'        => 'fa-solid fa-palette',
	'description' => __( 'Customize the appearance of archive pages, listing details, and reviews.', 'geodirectory' ),
	'subsections' => array(

		/**
		 * Subsection: Archives
		 */
		array(
			'id'          => 'archives',
			'name'        => __( 'Archives', 'geodirectory' ),
			'description' => __( 'Settings related to the appearance of listing archive pages (e.g., category and location pages).', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'archive_page_template',
					'type'    => 'select',
					'label'   => __( 'Archive Page Template Override', 'geodirectory' ),
					'description' => __( 'Optionally override the default page template used for archive pages.', 'geodirectory' ),
					'options' => self::single_page_templates(),
					'class'   => 'aui-select2',
					'searchable' => array('design', 'archive', 'template', 'override', 'layout'),
				),
				array(
					'id'      => 'listing_default_image',
					'type'    => 'image',
					'label'   => __( 'Default Listing Image', 'geodirectory' ),
					'description' => __( 'This image is used for listings that do not have a featured image. It can be overridden by a category-specific default image.', 'geodirectory' ),
					'searchable' => array('design', 'image', 'default', 'placeholder', 'listing', 'fallback'),
				),
			),
		),

		/**
		 * Subsection: Details Page
		 */
		array(
			'id'          => 'details',
			'name'        => __( 'Details Page', 'geodirectory' ),
			'description' => __( 'Customize the appearance and layout of the single listing detail pages.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'details_disable_featured',
					'type'    => 'toggle',
					'label'   => __( 'Disable Theme Featured Image', 'geodirectory' ),
					'description' => __( 'Enable this to prevent themes from outputting a duplicate featured image on the listing detail page.', 'geodirectory' ),
					'default' => false,
					'searchable' => array('design', 'details', 'image', 'featured', 'theme', 'duplicate'),
				),
				array(
					'id'      => 'details_page_template',
					'type'    => 'select',
					'label'   => __( 'Details Page Template Override', 'geodirectory' ),
					'description' => __( 'Optionally override the default page template used for listing detail pages.', 'geodirectory' ),
					'options' => self::single_page_templates(),
					'class'   => 'aui-select2',
					'searchable' => array('design', 'details', 'template', 'override', 'layout', 'single'),
				),
			),
		),

		/**
		 * Subsection: Reviews
		 */
		array(
			'id'          => 'reviews',
			'name'        => __( 'Reviews & Ratings', 'geodirectory' ),
			'description' => __( 'Customize the appearance of rating stars and review elements.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'rating_color',
					'type'    => 'color',
					'label'   => __( 'Rating "On" Color', 'geodirectory' ),
					'description' => __( 'The color of active rating stars.', 'geodirectory' ),
					'default' => '#ff9900',
					'searchable' => array('design', 'review', 'rating', 'star', 'color', 'on'),
				),
				array(
					'id'      => 'rating_color_off',
					'type'    => 'color',
					'label'   => __( 'Rating "Off" Color', 'geodirectory' ),
					'description' => __( 'The color of inactive rating stars.', 'geodirectory' ),
					'default' => '#afafaf',
					'searchable' => array('design', 'review', 'rating', 'star', 'color', 'off', 'inactive'),
				),
				array(
					'id'      => 'rating_type',
					'type'    => 'select',
					'label'   => __( 'Rating Type', 'geodirectory' ),
					'description' => __( 'Select the rating type to use.', 'geodirectory' ),
					'options' => array(
						'font-awesome'  => __( 'Font Awesome', 'geodirectory' ),
						'image'  => __( 'Transparent Image', 'geodirectory' ),
					),
					'default' => 'font-awesome',
					'class'   => 'aui-select2',
					'searchable' => array('design', 'review', 'rating', 'star', 'type', 'icon', 'image'),
				),
				array(
					'id'      => 'rating_icon',
					'type'    => 'font-awesome',
					'label'   => __( 'Rating Icon', 'geodirectory' ),
					'description' => __( 'Enter the Font Awesome class for the rating icon (e.g., "fas fa-star").', 'geodirectory' ),
					'default' => 'fas fa-star',
					'searchable' => array('design', 'review', 'rating', 'star', 'icon', 'font awesome'),
					'show_if' => '[%rating_type%] == "font-awesome"',
				),
				array(
					'id'      => 'rating_icon_fw',
					'type'    => 'toggle',
					'label'   => __( 'FA fixed width', 'geodirectory' ),
					'description' => __( 'This can add more spacing between font awesome icons if they are tight.', 'geodirectory' ),
					'default' => true,
					'searchable' => array('design', 'review', 'rating', 'star', 'icon', 'font awesome'),
					'show_if' => '[%rating_type%] == "font-awesome"',
				),
				array(
					'id'       => 'rating_image',
					'type'     => 'image',
					'label'    => __( 'Rating transparent image', 'geodirectory' ),
					'description' => __( 'Used only if the transparent image option is set, this image will be used to select ratings.', 'geodirectory' ),
					'default'  => '',
					'searchable' => array('design', 'review', 'rating', 'star', 'icon','image'),
					'show_if' => '[%rating_type%] == "image"',
				),
				array(
					'id'      => 'rating_text_1',
					'type'    => 'text',
					'label'   => __( '1-Star Rating Text', 'geodirectory' ),
					'description' => __( 'The descriptive text shown when a user selects a 1-star rating.', 'geodirectory' ),
					'placeholder' => GeoDir_Comments::rating_texts_default()[1],
					'searchable' => array('design', 'review', 'rating', 'text', 'label', '1 star'),
				),
				array(
					'id'      => 'rating_text_2',
					'type'    => 'text',
					'label'   => __( '2-Star Rating Text', 'geodirectory' ),
					'description' => __( 'The descriptive text shown for a 2-star rating.', 'geodirectory' ),
					'placeholder' => GeoDir_Comments::rating_texts_default()[2],
					'searchable' => array('design', 'review', 'rating', 'text', 'label', '2 star'),
				),
				array(
					'id'      => 'rating_text_3',
					'type'    => 'text',
					'label'   => __( '3-Star Rating Text', 'geodirectory' ),
					'description' => __( 'The descriptive text shown for a 3-star rating.', 'geodirectory' ),
					'placeholder' => GeoDir_Comments::rating_texts_default()[3],
					'searchable' => array('design', 'review', 'rating', 'text', 'label', '3 star'),
				),
				array(
					'id'      => 'rating_text_4',
					'type'    => 'text',
					'label'   => __( '4-Star Rating Text', 'geodirectory' ),
					'description' => __( 'The descriptive text shown for a 4-star rating.', 'geodirectory' ),
					'placeholder' => GeoDir_Comments::rating_texts_default()[4],
					'searchable' => array('design', 'review', 'rating', 'text', 'label', '4 star'),
				),
				array(
					'id'      => 'rating_text_5',
					'type'    => 'text',
					'label'   => __( '5-Star Rating Text', 'geodirectory' ),
					'description' => __( 'The descriptive text shown for a 5-star rating.', 'geodirectory' ),
					'placeholder' => GeoDir_Comments::rating_texts_default()[5],
					'searchable' => array('design', 'review', 'rating', 'text', 'label', '5 star'),
				),
			),
		),
	)
);
