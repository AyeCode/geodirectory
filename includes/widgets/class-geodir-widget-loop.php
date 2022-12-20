<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Loop extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['loop','archive','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_loop', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Loop','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-loop-container '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Shows the current posts from the main WP query according to the URL.  This is only used on the `GD Archive template` page.  It loops through each post and outputs the `GD Archive Item` template.','geodirectory'), // widget description
                'geodirectory' => true,
            )
        );


        $arguments['layout'] = array(
            'title' => __('Layout', 'geodirectory'),
            'desc' => __('How the listings should laid out by default.', 'geodirectory'),
            'type' => 'select',
            'options'   =>  geodir_get_layout_options(),
            'default'  => '2',
            'desc_tip' => true,
            'advanced' => false
        );


        $design_style = geodir_design_style();

        if ( $design_style ) {

            $arguments['row_gap'] = array(
                'title' => __( "Card row gap", 'geodirectory' ),
                'desc' => __('This adjusts the spacing between the cards horizontally.','geodirectory'),
                'type' => 'select',
                'options' =>  array(
                    ''  =>  __("Default","geodirectory"),
                    '1'  =>  '1',
                    '2'  =>  '2',
                    '3'  =>  '3',
                    '4'  =>  '4',
                    '5'  =>  '5',
                ),
                'default'  => '',
                'desc_tip' => false,
                'advanced' => false,
                'group'     => __("Card Design","geodirectory")
            );

            $arguments['column_gap'] = array(
                'title' => __( "Card column gap", 'geodirectory' ),
                'desc' => __('This adjusts the spacing between the cards vertically.','geodirectory'),
                'type' => 'select',
                'options' =>  array(
                    ''  =>  __("Default","geodirectory"),
                    '1'  =>  '1',
                    '2'  =>  '2',
                    '3'  =>  '3',
                    '4'  =>  '4',
                    '5'  =>  '5',
                ),
                'default'  => '',
                'desc_tip' => false,
                'advanced' => false,
                'group'     => __("Card Design","geodirectory")
            );

            $arguments['card_border'] = array(
                'title' => __( "Card border", 'geodirectory' ),
                'desc' => __('Set the border style for the card.','geodirectory'),
                'type' => 'select',
                'options' =>  array(
                                  ''  =>  __("Default","geodirectory"),
                                  'none'  =>  __("None","geodirectory"),
                              ) + geodir_aui_colors(),
                'default'  => '',
                'desc_tip' => false,
                'advanced' => false,
                'group'     => __("Card Design","geodirectory")
            );

            $arguments['card_shadow'] = array(
                'title' => __( "Card shadow", 'geodirectory' ),
                'desc' => __('Set the card shadow style.','geodirectory'),
                'type' => 'select',
                'options' =>  array(
                    ''  =>  __("None","geodirectory"),
                    'small'  =>  __("Small","geodirectory"),
                    'medium'  =>  __("Medium","geodirectory"),
                    'large'  =>  __("Large","geodirectory"),
                ),
                'default'  => '',
                'desc_tip' => false,
                'advanced' => false,
                'group'     => __("Card Design","geodirectory")
            );

            // background
            $arguments['bg']  = geodir_get_sd_background_input('mt');

            // margins
            $arguments['mt']  = geodir_get_sd_margin_input('mt');
            $arguments['mr']  = geodir_get_sd_margin_input('mr');
            $arguments['mb']  = geodir_get_sd_margin_input('mb');
            $arguments['ml']  = geodir_get_sd_margin_input('ml');

            // padding
            $arguments['pt']  = geodir_get_sd_padding_input('pt');
            $arguments['pr']  = geodir_get_sd_padding_input('pr');
            $arguments['pb']  = geodir_get_sd_padding_input('pb');
            $arguments['pl']  = geodir_get_sd_padding_input('pl');

            // border
            $arguments['border']  = geodir_get_sd_border_input('border');
            $arguments['rounded']  = geodir_get_sd_border_input('rounded');
            $arguments['rounded_size']  = geodir_get_sd_border_input('rounded_size');

            // shadow
            $arguments['shadow']  = geodir_get_sd_shadow_input('shadow');
        }

		/*
		* Elementor Pro features below here
		*/
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			$arguments['skin_id'] = array(
				'type' => 'select',
				'title' => __( 'Elementor Skin', 'geodirectory' ),
				'desc' => '',
				'options' => GeoDir_Elementor::get_elementor_pro_skins(),
				'default' => '',
				'desc_tip' => false,
				'advanced' => false,
				'group' => __( 'Design', 'geodirectory' )
			);

			$arguments['skin_column_gap'] = array(
				'type' => 'number',
				'title' => __( 'Skin column gap', 'geodirectory' ),
				'desc' => __( 'The px value for the column gap.', 'geodirectory' ),
				'default' => '30',
				'desc_tip' => true,
				'advanced' => false,
				'group' => __( 'Design', 'geodirectory' )
			);

			$arguments['skin_row_gap'] = array(
				'type' => 'number',
				'title' => __( 'Skin row gap', 'geodirectory'),
				'desc' => __( 'The px value for the row gap.', 'geodirectory' ),
				'default' => '35',
				'desc_tip' => true,
				'advanced' => false,
				'group' => __( 'Design', 'geodirectory' )
			);
		}

        $options['arguments'] = $arguments;


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
        global $wp_query, $gd_layout_class;

        ob_start();
        $design_style = geodir_design_style();
        $is_preview = $this->is_preview();

        if ( geodir_is_post_type_archive() || geodir_is_taxonomy() || geodir_is_page( 'search' ) || ( is_author() && ! empty( $wp_query->query['gd_favs'] ) || apply_filters( 'geodir_loop_active', false ) ) || $is_preview ) {
            $widget_args = wp_parse_args( 
				$args, 
				array(
					'layout' => '',
					// AUI settings
					'column_gap'  => '',
					'row_gap'  => '',
					'card_border'  => '',
					'card_shadow'  => '',
					'skin_id' => '',
					'skin_column_gap' => '',
					'skin_row_gap' => ''
				) 
			);

			/**
			 * Filter the widget skin_id param.
			 *
			 * @since 2.2.7
			 *
			 * @param string $widget_args['skin_id'] Filter skin_id.
			 */
			$skin_id = empty( $widget_args['skin_id'] ) ? '' : apply_filters( 'geodir_loop_widget_skin_id', $widget_args['skin_id'], $widget_args, $this->id_base );

            // card border class
            $card_border_class = '';
            if ( ! empty( $widget_args['card_border'] ) ) {
                if ( $widget_args['card_border'] == 'none' ) {
                    $card_border_class = 'border-0';
                } else {
                    $card_border_class = 'border-' . sanitize_html_class( $widget_args['card_border'] );
                }
            }

            // card shadow
            $card_shadow_class = '';
            if ( ! empty( $widget_args['card_shadow'] ) ) {
                if ( $widget_args['card_shadow'] == 'small' ) {
                    $card_shadow_class = 'shadow-sm';
                } elseif ( $widget_args['card_shadow'] == 'medium' ) {
                    $card_shadow_class = 'shadow';
                } elseif ( $widget_args['card_shadow'] == 'large' ) {
                    $card_shadow_class = 'shadow-lg';
                }
            }

            $gd_layout_class = geodir_convert_listing_view_class( $widget_args['layout'] );

            // for preview just get the main posts
            if ( $is_preview ) {
                $wp_query = new WP_Query( array('post_type' => 'gd_place','posts_per_page' => 6 ) );

                // preview message
                if($is_preview && $design_style){
                    echo aui()->alert(array(
                            'type'=> 'info',
                            'content'=> __("This preview shows all content items to give an idea of layout. Dummy data is used in places.","geodirectory")
                        )
                    );
                }
            }

            /**
             * Fires before the loop is rendered.
             *
             * @since 2.2.8
             *
             * @param array $widget_args Widget args.
             * @param object $this Current widget class.
             */
            do_action( 'geodir_widget_loop_before', $widget_args, $this );

            // Check if we have listings or if we are faking it
            if ( $wp_query->post_count == 1 && empty( $wp_query->posts ) ) {
                geodir_no_listings_found();
            } elseif ( geodir_is_page( 'search' ) && ! isset( $_REQUEST['geodir_search'] ) ) {
                geodir_no_listings_found();
            } else {
                // Check we are not inside a template builder container
                if ( isset( $wp_query->posts[0] ) && $wp_query->posts[0]->post_type == 'page' ) {
                    // Reset the query count so the correct number of listings are output.
                    rewind_posts();
                    // Reset the proper loop content
                    global $wp_query, $gd_temp_wp_query;

                    $wp_query->posts = $gd_temp_wp_query;
                }

                // Check if still have listings.
                if ( $wp_query->post_count == 1 && empty( $wp_query->posts ) ) {
                    geodir_no_listings_found();
                } else {
                    $design_style = ! empty( $args['design_style'] ) ? esc_attr( $args['design_style'] ) : geodir_design_style();

                    // wrap class
                    $wrap_class = geodir_build_aui_class( $widget_args );

					// Elementor
					$elementor_skin = false;
					$elementor_wrapper_class = '';

					if ( defined( 'ELEMENTOR_PRO_VERSION' ) && $skin_id ) {
						if ( get_post_status ( $skin_id ) == 'publish' ) {
							$elementor_skin = true;
						}

						if ( $elementor_skin ) {
							$columns = isset( $widget_args['layout'] ) ? absint( $widget_args['layout'] ) : 1;
							if ( $columns == 0 ) {
								$columns = 6; // we have no 6 row option to lets use list view
							}
							$wrap_class .= ' elementor-element elementor-element-9ff57fdx elementor-posts--thumbnail-top elementor-grid-' . $columns . ' elementor-grid-tablet-2 elementor-grid-mobile-1 elementor-widget elementor-widget-archive-posts ';
						}
					}

                    if ( $wrap_class ) {
                        echo "<div class='$wrap_class'>";
                    }


					if ( $elementor_skin ) {
						$column_gap = ! empty( $widget_args['skin_column_gap'] ) ? absint( $widget_args['skin_column_gap'] ) : '';
						$row_gap = ! empty( $widget_args['skin_row_gap'] ) ? absint( $widget_args['skin_row_gap'] ) : '';

						geodir_get_template( 'elementor/content-archive-listing.php', 
							array( 
								'skin_id' => $skin_id, 
								'columns' => $columns, 
								'column_gap' => $column_gap, 
								'row_gap' => $row_gap 
							) 
						);
					} else {
						$template = $design_style ? $design_style . "/content-archive-listing.php" : "content-archive-listing.php";

						 echo geodir_get_template_html( $template, array(
							'column_gap_class' => $widget_args['column_gap'] ? 'mb-' . absint( $widget_args['column_gap'] ) : 'mb-4',
							'row_gap_class' => $widget_args['row_gap'] ? 'px-' . absint( $widget_args['row_gap'] ) : '',
							'card_border_class' => $card_border_class,
							'card_shadow_class' => $card_shadow_class,
						) );
					}

                    if ( $wrap_class ) {
                        echo "</div>";
                    }

                    // set loop as done @todo this needs testing
                    global $wp_query;

                    $wp_query->current_post = $wp_query->post_count;
                }
            }

            /**
             * Fires after the loop is rendered.
             *
             * @since 2.2.8
             *
             * @param array $widget_args Widget args.
             * @param object $this Current widget class.
             */
            do_action( 'geodir_widget_loop_after', $widget_args, $this );
        } else {
            geodir_no_listings_found();
        }

        // Add filter to make main page comments closed after the GD loop
        add_filter( 'comments_open', array( __CLASS__, 'comments_open' ), 10, 2 );

        return ob_get_clean();
    }

    /**
     * Filter to close the comments for archive pages after the GD loop.
     * 
     * @param $open
     * @param $post_id
     *
     * @return bool
     */
    public static function comments_open($open, $post_id){

        global $post;
        if(isset($post->ID) && $post->ID==$post_id){
            $open = false;
        }

        return $open;
    }

}