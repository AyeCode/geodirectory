<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Single_Taxonomies extends WP_Super_Duper {

	/**
	 * Holds the CSS global.
	 *
	 * @var array
	 */
	private $css_rules = array();

	/**
	 * Register the advanced search widget with WordPress.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'admin-site',
			'block-category' => 'geodirectory',
			'block-keywords' => "['category','taxonomies','geodir']",
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_single_taxonomies', // this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Single Taxonomies', 'geodirectory' ), // the name of the widget.
			'widget_ops'     => array(
				// widget class.
				'classname'    => 'geodir-single-taxonomies-container ' . geodir_bsui_class(),
				// widget description.
				'description'  => esc_html__( 'Shows the current post`s categories and tags.', 'geodirectory' ),
				'geodirectory' => true,
			),
			'arguments'      => array(
				'taxonomy' => array(
					'title'    => __( 'Taxonomy', 'geodirectory' ),
					'desc'     => __( 'Select the taxonomy types to output.', 'geodirectory' ),
					'type'     => 'select',
					'options'  => array(
						''     => __( 'Categories and Tags', 'geodirectory' ),
						'cats' => __( 'Categories', 'geodirectory' ),
						'tags' => __( 'Tags', 'geodirectory' ),
					),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false,
				),
				'limit' => array(
					'title'    => __( 'Limit', 'geodirectory' ),
					'desc'     => __( 'Set a limit of the number to output.', 'geodirectory' ),
					'type'     => 'number',
					'placeholder' => '10 (would limit to 10)',
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false,
				),
				'prefix'   => array(
					'title'    => __( 'Prefix', 'geodirectory' ),
					'desc'     => __( 'Select the taxonomy types to output', 'geodirectory' ),
					'type'     => 'select',
					'options'  => array(
						''      => __( 'Taxonomy names', 'geodirectory' ),
						'icons' => __( 'Icons', 'geodirectory' ),
						'0'     => __( 'None', 'geodirectory' ),
					),
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false,
				),
			),
		);

		$design_style = geodir_design_style();

		if ( $design_style ) {
			$options['arguments']['link_style'] = array(
				'title'    => __( 'Link style', 'geodirectory' ),
				'desc'     => __( 'Select the style for the links.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''     => __( 'Badge', 'geodirectory' ),
					'pill' => __( 'Pill', 'geodirectory' ),
					'link' => __( 'Link', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Design', 'geodirectory' ),
			);

			$options['arguments']['link_color'] = array(
				'title'           => __( 'Link color', 'geodirectory' ),
				'desc'            => __( 'Sets the link color.', 'geodirectory' ),
				'type'            => 'select',
				'options'         => array(
					                     '' => __( 'Category color', 'geodirectory' ),
				                     ) + geodir_aui_colors( true ),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Design', 'geodirectory' ),
				'element_require' => '[%link_style%]!="link"',
			);

			$options['arguments']['link_color_custom'] = array(
				'title'           => __( 'Link custom color', 'geodirectory' ),
				'desc'            => __( 'Sets the link color to a custom color.', 'geodirectory' ),
				'type'            => 'color',
				'desc_tip'        => true,
				'default'         => '',
				'group'           => __( 'Design', 'geodirectory' ),
				'element_require' => '[%link_color%]=="custom"',
			);

			$options['arguments']['link_icon'] = array(
				'title'    => __( 'Link icons', 'geodirectory' ),
				'desc'     => __( 'Show icons for the links.', 'geodirectory' ),
				'type'     => 'checkbox',
				'desc_tip' => true,
				'value'    => '1',
				'default'  => '',
				'group'    => __( 'Design', 'geodirectory' ),
			);

			// margins.
			$options['arguments']['mt'] = geodir_get_sd_margin_input( 'mt' );
			$options['arguments']['mr'] = geodir_get_sd_margin_input( 'mr' );
			$options['arguments']['mb'] = geodir_get_sd_margin_input( 'mb', array( 'default' => 2 ) );
			$options['arguments']['ml'] = geodir_get_sd_margin_input( 'ml' );

			// padding.
			$options['arguments']['pt'] = geodir_get_sd_padding_input( 'pt' );
			$options['arguments']['pr'] = geodir_get_sd_padding_input( 'pr' );
			$options['arguments']['pb'] = geodir_get_sd_padding_input( 'pb' );
			$options['arguments']['pl'] = geodir_get_sd_padding_input( 'pl' );

		}

		parent::__construct( $options );
	}

	/**
	 * The Super block output function.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $preview, $post, $gd_post;

		$is_preview = $this->is_preview();
		if ( empty( $post->post_type ) && ! $is_preview ) {
			return;
		}

		// Default options.
		$defaults = array(
			'taxonomy'          => '',
			'limit'             => '',
			'prefix'            => '',
			'link_style'        => '',
			'link_color'        => '',
			'link_color_custom' => '',
			'link_icon'         => '',
			'mt'                => '',
			'mb'                => '2',
			'mr'                => '',
			'ml'                => '',
			'pt'                => '',
			'pb'                => '',
			'pr'                => '',
			'pl'                => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args      = wp_parse_args( $args, $defaults );
		$css_rules = array();

		$post_id   = isset( $post->ID ) ? $post->ID : '';
		$post_type = ! empty( $post->post_type ) ? $post->post_type : '';
		if ( $is_preview ) {
			$post_type = 'page';
		}
		$post_type_name = geodir_post_type_singular_name( $post_type, true );
		$cat_taxonomy   = $post_type . 'category';
		$tag_taxonomy   = $post_type . '_tags';
		$design_style   = geodir_design_style();
		$taxonomies     = array();
		$limit          = ! empty( $args['limit'] ) ? absint( $args['limit'] ) : 0;

		if ( ! empty( $gd_post->post_tags ) && $args['taxonomy'] != 'cats' ) {
			if ( taxonomy_exists( $tag_taxonomy ) ) {
				// To limit post tags.
				$post_tags = trim( $gd_post->post_tags, "," );

				/**
				 * Filter the post tags.
				 *
				 * Allows you to filter the post tags output on the details page of a post.
				 *
				 * @param string $post_tags A comma seperated list of tags.
				 * @param int $post_id The current post id.
				 *
				 * @since 1.0.0
				 */
				$post_tags = apply_filters( 'geodir_action_details_post_tags', $post_tags, $post_id );

				$gd_post->post_tags = $post_tags;
				$post_tags          = explode( ",", trim( $gd_post->post_tags, "," ) );

				$terms = array();
				$links = array();
				$count = 0;
				foreach ( $post_tags as $post_term ) {
					// Fix slug creation order for tags & location
					$post_term = trim( $post_term );

					if ( $insert_term = term_exists( $post_term, $tag_taxonomy ) ) {
						$term = get_term_by( 'id', $insert_term['term_id'], $tag_taxonomy );
					} else {
						continue;
					}

					if ( ! empty( $term ) && ! is_wp_error( $term ) && is_object( $term ) ) {
						$tag_href = esc_attr( get_term_link( $term->term_id, $term->taxonomy ) );
						$tag_link = $this->style_tax_link( 'tag', $term->name, $tag_href, $term, $args );

						/**
						 * Filter the tag name on the details page.
						 *
						 * @param string $tag_link The tag link html.
						 * @param object $term The tag term object.
						 *
						 * @since 1.5.6
						 */
						$tag_link = apply_filters( 'geodir_details_taxonomies_tag_link', $tag_link, $term );
						$links[]  = $tag_link;
						$terms[]  = $term;

						$count++;

						// limit
						if ( $limit && $count >= $limit ) {
							break;
						}
					}
				}

				$taxonomies[ $tag_taxonomy ] = $this->output_tax_list( 'tag', $post_type_name, $links, $terms, $args );
			}
		}

		if ( ! empty( $gd_post->post_category ) && $args['taxonomy'] != 'tags' ) {
			if ( ! is_array( $gd_post->post_category ) ) {
				$post_terms = explode( ",", trim( $gd_post->post_category, "," ) );
			} else {
				$post_terms = $gd_post->post_category;

				if ( $preview ) {
					$post_terms = geodir_add_parent_terms( $post_terms, $cat_taxonomy );
				}
			}

			$post_terms   = array_unique( $post_terms );
			$terms        = array();
			$links        = array();
			$termsOrdered = array();
			$count        = 0;
			if ( ! empty( $post_terms ) ) {
				foreach ( $post_terms as $post_term ) {
					$post_term = trim( $post_term );

					if ( $post_term != '' ) {
						$term = get_term_by( 'id', $post_term, $cat_taxonomy );

						if ( ! empty( $term ) && ! is_wp_error( $term ) && is_object( $term ) ) {
							$cat_href = esc_attr( get_term_link( $term->term_id, $term->taxonomy ) );
							$term_link = $this->style_tax_link( 'cat', $term->name, $cat_href, $term, $args );

							/**
							 * Filter the category name on the details page.
							 *
							 * @param string $term_link The link html to the category.
							 * @param object $term The category term object.
							 *
							 * @since 1.5.6
							 */
							$term_link = apply_filters( 'geodir_details_taxonomies_cat_link', $term_link, $term );
							$links[]   = $term_link;
							$terms[]   = $term;

							$count++;

							// limit
							if ( $limit && $count >= $limit ) {
								break;
							}
						}
					}
				}
				// Order alphabetically
				asort( $links );
				foreach ( array_keys( $links ) as $key ) {
					$termsOrdered[ $key ] = $terms[ $key ];
				}
				$terms = $termsOrdered;
			}

			$taxonomies[ $cat_taxonomy ] = $this->output_tax_list( 'cat', $post_type_name, $links, $terms, $args );
		}

		/**
		 * Filter the taxonomies array before output.
		 *
		 * @param array $taxonomies The array of cats and tags.
		 * @param string $post_type The post type being output.
		 * @param string $post_type_name The post type label.
		 * @param string $post_type_name The post type label with ucwords function.
		 *
		 * @since 1.5.9
		 */
		$taxonomies = apply_filters( 'geodir_details_taxonomies_output', $taxonomies, $post_type, $post_type_name, geodir_ucwords( $post_type_name ) );

		// Block demo content.
		if ( $is_preview && empty( $taxonomies ) && $post_type == 'page' ) {

			if ( $args['taxonomy'] != 'tags' ) {
				$links                       = array();
				$links[]                     = $this->style_tax_link( 'cat', "Demo", '#', '', $args );
				$links[]                     = $this->style_tax_link( 'cat', "Example", '#', '', $args );
				$taxonomies[ $cat_taxonomy ] = $this->output_tax_list( 'cat', "Demo", $links, $links, $args );
			}

			if ( $args['taxonomy'] != 'cats' ) {
				$links                       = array();
				$links[]                     = $this->style_tax_link( 'tag', "Demo", '#', '', $args );
				$links[]                     = $this->style_tax_link( 'tag', "Example", '#', '', $args );
				$taxonomies[ $tag_taxonomy ] = $this->output_tax_list( 'tag', "Demo", $links, $links, $args );
			}
		}

		$template = $design_style ? $design_style . "/single/taxonomies.php" : "legacy/single/taxonomies.php";

		// wrapper class
		$wrap_class = geodir_build_aui_class( $args );

		$args = array(
			'args'         => $args,
			'taxonomies'   => $taxonomies,
			'cat_taxonomy' => $cat_taxonomy,
			'tag_taxonomy' => $tag_taxonomy,
			'wrap_class'   => $wrap_class,
		);
		$content = geodir_get_template_html( $template, $args );

		// maybe add css
		if ( ! empty( $this->css_rules ) && method_exists( $this, 'get_instance_style' ) ) {
			$content .= $this->get_instance_style( $this->css_rules );
		}

		return $content;
	}

	/**
	 * Build the output from the taxonomy list.
	 *
	 * @param string $type
	 * @param $post_type_name
	 * @param $links
	 * @param $terms
	 * @param $args
	 *
	 * @return string
	 */
	public function output_tax_list( $type = 'cat', $post_type_name = '', $links = array(), $terms = array(), $args = array() ) {
		$output       = '';
		$design_style = geodir_design_style();
		$links_array  = $links;
		$links        = $design_style && $args['link_style'] != 'link' ? implode( " ", $links ) : $links;
		if ( $type == 'cat' ) {
			if ( $args['prefix'] === '0' ) {
				$output = wp_sprintf( __( '%s%l', 'geodirectory' ), '', $links, (object) $terms );
			} elseif ( $args['prefix'] == 'icons' ) {
				$output = wp_sprintf( __( '%s%s Categories%s %l', 'geodirectory' ), '<i class="fas fa-folder" data-toggle="tooltip" title="', $post_type_name, '"></i>', $links, (object) $terms );
			} else {
				if ( count( $links_array ) > 1 ) {
					$output = wp_sprintf( __( '%s Categories: %l', 'geodirectory' ), $post_type_name, $links, (object) $terms );
				} else {
					$output = wp_sprintf( __( '%s Category: %l', 'geodirectory' ), $post_type_name, $links, (object) $terms );
				}
			}
		} else {
			if ( $args['prefix'] === '0' ) {
				$output = wp_sprintf( __( '%s%l', 'geodirectory' ), '', $links, (object) $terms );
			} elseif ( $args['prefix'] == 'icons' ) {
				$output = wp_sprintf( __( '%s%s Tags%s %l', 'geodirectory' ), '<i class="fas fa-tags" data-toggle="tooltip" title="', $post_type_name, '"></i>', $links, (object) $terms );
			} else {
				$output = wp_sprintf( __( '%s Tags: %l', 'geodirectory' ), $post_type_name, $links, (object) $terms );
			}
		}


		return $output;
	}

	/**
	 * Build a link from taxonomy information.
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $href
	 * @param $term
	 * @param array $args
	 *
	 * @return string
	 */
	public function style_tax_link( $type = 'cat', $name = '', $href = '#', $term = array(), $args = array() ) {
		global $aui_bs5;

		$link         = '';
		$icon_output  = '';
		$icon         = $type == 'cat' ? '<i class="fas fa-folder"></i>' : '<i class="fas fa-tag"></i>';
		$design_style = geodir_design_style();

		// style
		$term_class = ! empty( $term->term_id ) ? 'gd-termid-' . absint( $term->term_id ) : 'gd-termid-0';
		$link_class = $term_class . " ";
		if ( $design_style && $args['link_style'] != 'link' ) {
			$link_class .= 'badge ';
			if ( $args['link_style'] == 'pill' ) {
				$link_class .= ( $aui_bs5 ? ' rounded-pill ' : ' badge-pill ' );
			}
		}

		// color
		if ( $design_style && $args['link_style'] != 'link' ) {
			if ( ! empty( $args['link_color_custom'] ) && $args['link_color'] == 'custom' ) {
				$link_color_custom = sanitize_hex_color( $args['link_color_custom'] );
				$css_target        = $args['link_style'] != 'link' ? ".badge" : "a";
				$css_type          = $args['link_style'] != 'link' ? "background" : "color";
				$this->css_rules[] = $css_target . " {{$css_type}: $link_color_custom;color:#fff;}";
				$this->css_rules[] = $css_target . ":hover {color: #fff;}";
			} elseif ( empty( $args['link_color'] ) ) {
				$default_class = $aui_bs5 ? ' text-bg-dark' : ' badge-dark text-light';
				if ( $type == 'tag' ) {
					$link_class .= $default_class;
				} else {
					$cat_color = '';
					if ( ! empty( $term ) ) {
						$cat_color = get_term_meta( $term->term_id, 'ct_cat_color', true );
					}

					if ( ! $cat_color ) {
						$link_class .= $default_class;
					} else {
						$link_color_custom = sanitize_hex_color( $cat_color );
						$css_target        = $args['link_style'] != 'link' ? ".badge." . $term_class : "a." . $term_class;
						$css_type          = $args['link_style'] != 'link' ? "background" : "color";
						$this->css_rules[] = $css_target . " {{$css_type}: $link_color_custom;color:#fff;}";
						$this->css_rules[] = $css_target . ":hover {color: #fff;}";
					}
				}

			} elseif ( ! empty( $args['link_color'] ) ) {
				$link_class .= ( $aui_bs5 ? ' bg-' : ' badge-' ) . sanitize_html_class( $args['link_color'] );
			}

			// if show icon
			if ( $args['link_icon'] ) {
				$icon_output = $icon . " ";

				if ( ! empty( $term ) ) {
					$cat_icon = get_term_meta( $term->term_id, 'ct_cat_font_icon', true );
					if ( $cat_icon ) {
						$icon_output = '<i class="' . $cat_icon . '"></i> ';
					}
				}
			}
		}


		$link = "<a href='" . esc_url_raw( $href ) . "' class='$link_class'>$icon_output" . esc_attr( $name ) . "</a>";

		return $link;
	}
}
