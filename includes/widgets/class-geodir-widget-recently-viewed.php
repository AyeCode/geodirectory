<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Recently_Viewed extends WP_Super_Duper {

	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'admin-site',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['Recently Viewed','geodir']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_recently_viewed',
			'name'          => __('GD > Recently Viewed','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-recently-viewed '.geodir_bsui_class(), // widget class
				'description' => esc_html__('Shows the GeoDirectory Most Recently Viewed Listings.','geodirectory'),
				'geodirectory' => true,
			),
		);

		parent::__construct( $options );

		add_action('wp_enqueue_scripts',array( $this,  'enqueue_script') );
	}

	/**
	 * Set the arguments later.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function set_arguments() {
		$design_style = geodir_design_style();

		$widget_args = array();

		$widget_args['title'] = array(
			'title' => __('Title:', 'geodirectory'),
			'desc' => __('The Recently Viewed widget title.', 'geodirectory'),
			'type' => 'text',
			'placeholder' => __( 'Recently Viewed', 'geodirectory' ),
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false,
		);

		$widget_args['post_limit'] = array(
			'title' => __('Posts to show:', 'geodirectory'),
			'desc' => __('The number of posts to show by default. (max 50)', 'geodirectory'),
			'type' => 'number',
			'default'  => '6',
			'desc_tip' => true,
			'advanced' => true
		);

		$widget_args['layout'] = array(
			'title' => __('Layout:', 'geodirectory'),
			'desc' => __('How the listings should laid out by default.', 'geodirectory'),
			'type' => 'select',
			'options'   =>  geodir_get_layout_options(),
			'default'  => '2',
			'desc_tip' => true,
			'advanced' => true
		);

		$get_posts = geodir_get_posttypes('options-plural');

		$widget_args['post_type'] = array(
			'title' => __('Post Type:', 'geodirectory'),
			'desc' => __('The custom post types to show. Only used when there are multiple CPTs.', 'geodirectory'),
			'type' => 'select',
			'options'   =>  $get_posts,
			'default'  => '',
			'desc_tip' => true,
			'advanced' => true
		);

		$widget_args['use_viewing_post_type'] = array(
			'title' => __("Use current viewing post type?", 'geodirectory'),
			'type' => 'checkbox',
			'desc_tip' => true,
			'value'  => '1',
			'default'  => '0',
			'advanced' => true,
		);
		// not needed in AUI
		if(!$design_style){
			$widget_args['enqueue_slider']  = array(
				'title' => __('Enqueue Slider Script:', 'geodirectory'),
				'desc' => __('This is only needed if your archive items are using a image slider.', 'geodirectory'),
				'type' => 'checkbox',
				'desc_tip' => true,
				'value'  => '1',
				'default'  => 0,
				'advanced' => true
			);
		}

		if ( $design_style ) {
			$arguments['template_type'] = array(
				'type' => 'select',
				'title' => __( 'Archive Item Template Type:', 'geodirectory' ),
				'desc' => __( 'Select archive item template type to assign template to listings loop.', 'geodirectory' ),
				'options' => geodir_template_type_options(),
				'default' => '',
				'desc_tip' => true,
				'advanced' => false,
				'group' => __( 'Card Design', 'geodirectory' ),
			);

			$arguments['tmpl_page'] = array(
				'type' => 'select',
				'title' => __( 'Archive Item Template Page:', 'geodirectory' ),
				'desc' => __( 'Select archive item template page.', 'geodirectory' ),
				'options' => geodir_template_page_options(),
				'default' => '',
				'desc_tip' => true,
				'advanced' => false,
				'element_require' => '[%template_type%]=="page"',
				'group' => __( 'Card Design', 'geodirectory' ),
			);

			if ( geodir_is_block_theme() ) {
				$arguments['tmpl_part'] = array(
					'type' => 'select',
					'title' => __( 'Archive Item Template Part:', 'geodirectory' ),
					'desc' => __( 'Select archive item template part.', 'geodirectory' ),
					'options' => geodir_template_part_options(),
					'default' => '',
					'desc_tip' => true,
					'advanced' => false,
					'element_require' => '[%template_type%]=="template_part"',
					'group' => __( 'Card Design', 'geodirectory' ),
				);
			}
		}

		/*
		* Elementor Pro features below here
		*/
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			$arguments['skin_id'] = array(
				'title'           => __( 'Archive Item Elementor Skin:', 'geodirectory' ),
				'desc'            => '',
				'type'            => 'select',
				'options'         => GeoDir_Elementor::get_elementor_pro_skins(),
				'default'         => '',
				'desc_tip'        => false,
				'advanced'        => false,
				'element_require' => '([%template_type%]=="" || [%template_type%]=="elementor_skin")',
				'group'           => __( 'Card Design', 'geodirectory' ),
			);

			$arguments['skin_column_gap'] = array(
				'title'           => __( 'Skin column gap', 'geodirectory' ),
				'desc'            => __( 'The px value for the column gap.', 'geodirectory' ),
				'type'            => 'number',
				'default'         => '30',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '([%template_type%]=="" || [%template_type%]=="elementor_skin")',
				'group'           => __( 'Card Design', 'geodirectory' ),
			);

			$arguments['skin_row_gap'] = array(
				'title'           => __( 'Skin row gap', 'geodirectory' ),
				'desc'            => __( 'The px value for the row gap.', 'geodirectory' ),
				'type'            => 'number',
				'default'         => '35',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '([%template_type%]=="" || [%template_type%]=="elementor_skin")',
				'group'           => __( 'Card Design', 'geodirectory' ),
			);
		}

		if ( $design_style ) {
			$arguments['row_gap'] = array(
				'title' => __( "Card row gap", 'geodirectory' ),
				'desc' => __( 'This adjusts the spacing between the cards horizontally.', 'geodirectory' ),
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
			$arguments['mb']  = geodir_get_sd_margin_input('mb',array('default'=>3));
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

			$widget_args = $widget_args + $arguments;
		}

		return $widget_args;
	}

	/**
	 * Outputs the map widget on the front-end.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $geodir_recently_viewed_count, $geodir_item_tmpl, $gd_layout_class;

		$args = wp_parse_args(
			(array) $args,
			array(
				'title' => '',
				'post_type' => '',
				'layout' => '2',
				'post_limit' => '6',
				// Template Settings
				'template_type' => '',
				'tmpl_page' => '',
				'tmpl_part' => '',
				// Elementor settings
				'skin_id' => '',
				'skin_column_gap' => '',
				'skin_row_gap' => '',
				// AUI settings
				'column_gap' => '',
				'row_gap' => '',
				'card_border' => '',
				'card_shadow' => '',
				'bg' => '',
				'mt' => '',
				'mb' => '3',
				'mr' => '',
				'ml' => '',
				'pt' => '',
				'pb' => '',
				'pr' => '',
				'pl' => '',
				'border' => '',
				'rounded' => '',
				'rounded_size' => '',
				'shadow' => '',
			)
		);

		if ( empty( $geodir_recently_viewed_count ) ) {
			$geodir_recently_viewed_count = 1;
		} else {
			$geodir_recently_viewed_count++;
		}

		$design_style = geodir_design_style();

		$post_page_limit = ! empty( $args['post_limit'] ) ? absint( $args['post_limit'] ) : '6';
		$layout = ! empty( $args['layout'] ) ? absint( $args['layout'] ) : '2';
		$post_type = ! empty( $args['post_type'] ) ? sanitize_text_field( $args['post_type'] ) : 'gd_place';
		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			$post_type = 'gd_place';
		}
		$use_viewing_post_type = ! empty( $args['use_viewing_post_type'] ) ? true : false;
		// set post type to current viewing post type
		if ( $use_viewing_post_type ) {
			$current_post_type = geodir_get_current_posttype();
			if ( $current_post_type != '' && $current_post_type != $post_type ) {
				$post_type = $current_post_type;
			}
		}

		$enqueue_slider = ! empty( $args['enqueue_slider'] ) ? true : false;

		/**
		 * Filter the widget template_type param.
		 *
		 * @param string $template_type Filter template_type.
		 *
		 * @since 2.8.101
		 *
		 */
		$template_type = apply_filters( 'geodir_widget_gd_recently_viewed_template_type', ( ! empty( $args['template_type'] ) ? $args['template_type'] : '' ), $args, $this->id_base );

		$template_page = 0;
		/**
		 * Filter the widget tmpl_page param.
		 *
		 * @param int $template_page Filter tmpl_page.
		 *
		 * @since 2.8.101
		 *
		 */
		if ( $template_type == 'page' ) {
			$template_page = apply_filters( 'geodir_widget_gd_recently_viewed_tmpl_page', ( ! empty( $args['tmpl_page'] ) ? (int) $args['tmpl_page'] : 0 ), $args, $this->id_base );
		}

		$template_part = '';
		/**
		 * Filter the widget tmpl_part param.
		 *
		 * @param string $template_part Filter tmpl_part.
		 *
		 * @since 2.8.101
		 *
		 */
		if ( $template_type == 'template_part' && geodir_is_block_theme() ) {
			$template_part = apply_filters( 'geodir_widget_gd_recently_viewed_tmpl_part', ( ! empty( $args['tmpl_part'] ) ? $args['tmpl_part'] : '' ), $args, $this->id_base );
		}

		$skin_id = 0;
		if ( empty( $template_type ) || $template_type == 'elementor_skin' ) {
			/**
			 * Filter the widget skin_id param.
			 *
			 * @param int $skin_id Filter skin_id.
			 *
			 * @since 2.8.101
			 *
			 */
			$skin_id = apply_filters( 'geodir_widget_gd_recently_viewed_skin_id', ( ! empty( $args['skin_id'] ) ? (int) $args['skin_id'] : 0 ), $args, $this->id_base );
		}

		$geodir_item_tmpl = array();

		if ( ! empty( $template_page ) && get_post_type( $template_page ) == 'page' && get_post_status( $template_page ) == 'publish' ) {
			$geodir_item_tmpl = array(
				'id'   => $template_page,
				'type' => 'page',
			);
		} else if ( ! empty( $template_part ) && ( $_template_part = geodir_get_template_part_by_slug( $template_part ) ) ) {
			$geodir_item_tmpl = array(
				'id'      => $_template_part->slug,
				'content' => $_template_part->content,
				'type'    => 'template_part',
			);
		}

		// Elementor Pro
		$skin_active = false;
		$elementor_wrapper_class = '';

		if ( defined( 'ELEMENTOR_PRO_VERSION' ) && $skin_id ) {
			if ( $this->is_preview() && ! $this->is_elementor_preview() ) {
				$skin_id = 0;
			}

			if ( get_post_status( $skin_id ) == 'publish' ) {
				$skin_active = true;

				$geodir_item_tmpl = array(
					'id'   => $skin_id,
					'type' => 'elementor_skin',
				);
			}

			if ( $skin_active ) {
				$columns = isset( $layout ) ? absint( $layout ) : 1;

				if ( $columns < 1 ) {
					$columns = 6; // We have no 6 row option to lets use list view
				}

				$elementor_wrapper_class = ' elementor-element elementor-element-9ff57fdx elementor-posts--thumbnail-top elementor-grid-' . $columns . ' elementor-grid-tablet-2 elementor-grid-mobile-1 elementor-widget elementor-widget-posts ';
			}
		}

		// Spinner
		if ( $design_style ) {
			$spinner = '<div class="spinner-border" role="status"><span class="sr-only visually-hidden">' . esc_html__( "Loading...", "geodirectory" ) . '</span></div>';
		} else {
			$spinner = '<i class="fas fa-sync fa-spin fa-2x"></i>';
		}

		// Preview
		$preview_listings = '';

		if ( $this->is_preview() && $design_style ) {
			$gd_layout_class = geodir_convert_listing_view_class( $layout );

			// Card border class
			$card_border_class = '';

			if ( ! empty( $args['card_border'] ) ) {
				if ( $args['card_border'] == 'none' ) {
					$card_border_class = 'border-0';
				} else {
					$card_border_class = 'border-' . sanitize_html_class( $args['card_border'] );
				}
			}

			// Card shadow
			$card_shadow_class = '';

			if ( ! empty( $args['card_shadow'] ) ) {
				if ( $args['card_shadow'] == 'small' ) {
					$card_shadow_class = 'shadow-sm';
				} else if ( $args['card_shadow'] == 'medium' ) {
					$card_shadow_class = 'shadow';
				} else if ( $args['card_shadow'] == 'large' ) {
					$card_shadow_class = 'shadow-lg';
				}
			}

			$query_args = array(
				'posts_per_page' => absint( $args['post_limit'] ),
				'is_geodir_loop' => true,
				'post_type' => $post_type,
			);

			$widget_listings = geodir_get_widget_listings( $query_args );

			if ( $skin_active ) {
				$column_gap = ! empty( $args['skin_column_gap'] ) ? absint( $args['skin_column_gap'] ) : '';
				$row_gap    = ! empty( $args['skin_row_gap'] ) ? absint( $args['skin_row_gap'] ) : '';

				ob_start();

				geodir_get_template(
					'elementor/content-widget-listing.php',
					array(
						'widget_listings' => $widget_listings,
						'skin_id'         => $skin_id,
						'columns'         => $columns,
						'column_gap'      => $column_gap,
						'row_gap'         => $row_gap,
					)
				);

				$preview_listings = ob_get_clean();
			} else {
				$template = $design_style ? $design_style . '/content-widget-listing.php' : 'content-widget-listing.php';

				$preview_listings = geodir_get_template_html(
					$template,
					array(
						'widget_listings'   => $widget_listings,
						'column_gap_class'  => $args['column_gap'] ? 'mb-' . absint( $args['column_gap'] ) : 'mb-4',
						'row_gap_class'     => $args['row_gap'] ? 'px-' . absint( $args['row_gap'] ) : '',
						'card_border_class' => $card_border_class,
						'card_shadow_class' => $card_shadow_class,
					)
				);
			}
		}

		// Wrap class
		$wrap_class = geodir_build_aui_class( $args );

		ob_start();

		if ( $enqueue_slider && ! $design_style ) {
			// Enqueue flexslider JS
			GeoDir_Frontend_Scripts::enqueue_script( 'jquery-flexslider' );
		}
		?>
		<div class="geodir-recently-reviewed <?php echo esc_attr( $wrap_class ); ?>">
			<div class="recently-reviewed-content recently-reviewed-content-<?php echo absint( $geodir_recently_viewed_count ) . esc_attr( $elementor_wrapper_class ); ?>"><?php echo $preview_listings; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<div class="recently-reviewed-loader" style="<?php echo ( $this->is_preview() ? 'display:none;' : '' ); ?>text-align:center;"><?php echo $spinner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
	if(!geodir_is_localstorage()){return;}
	jQuery('.recently-reviewed-loader').show();

	var recently_viewed = localStorage.getItem("gd_recently_viewed");
	var data = {
		'action': 'geodir_recently_viewed_listings',
		'viewed_post_id' : recently_viewed,
		'layout' : '<?php echo (int) $layout; ?>',
		'post_type':'<?php echo esc_js( $post_type ); ?>',
		'column_gap':'<?php echo esc_attr( $args['column_gap'] ); ?>',
		'row_gap':'<?php echo esc_attr( $args['row_gap'] ); ?>',
		'card_border':'<?php echo esc_attr( $args['card_border'] ); ?>',
		'card_shadow':'<?php echo esc_attr( $args['card_shadow'] ); ?>',
		'template_type':'<?php echo esc_attr( $template_type ); ?>',
		'tmpl_page':'<?php echo esc_attr( $template_page ); ?>',
		'tmpl_part':'<?php echo esc_attr( $template_part ); ?>',
		<?php if( defined( 'ELEMENTOR_PRO_VERSION' ) ) { ?>
		'skin_id':'<?php echo ( $skin_id ? (int) $skin_id : '' ); ?>',
		'skin_column_gap':'<?php echo ( ! empty( $args['skin_column_gap'] ) ? (int) $args['skin_column_gap'] : '' ); ?>',
		'skin_row_gap':'<?php echo ( ! empty( $args['skin_row_gap'] ) ? (int) $args['skin_row_gap'] : '' ); ?>',
		<?php } ?>
		'list_per_page' :'<?php echo (int) $post_page_limit; ?>'
	};

	jQuery.post(geodir_params.ajax_url,data,function(response){jQuery('.geodir-recently-reviewed .recently-reviewed-content-<?php echo absint( $geodir_recently_viewed_count ); ?>').html(response);jQuery('.recently-reviewed-loader').hide();init_read_more();geodir_init_lazy_load();geodir_refresh_business_hours();geodir_load_badge_class();geodir_init_flexslider();});
});
</script>
		<?php
		$geodir_item_tmpl = array();

		return ob_get_clean();
	}

	/**
	 * Added reviewed posts on local storage.
	 *
	 * Check if is_single page then added reviewed on local storage.
	 *
	 * @since 2.0.0
	 */
	public static function geodir_recently_viewed_posts() {
		if ( is_single() ) {
			$get_post_id = (int) get_the_ID();
			$get_post_type = get_post_type( $get_post_id );

			if ( geodir_is_gd_post_type( $get_post_type ) ) {
				ob_start();
				if ( 0 ) { ?><script><?php } ?>
document.addEventListener("DOMContentLoaded", function(event) {
	if (!geodir_is_localstorage()) {
		return;
	}
	function gdrv_is_not_empty(obj) {
		for (var key in obj) {
			if (obj.hasOwnProperty(key))
				return true;
		}
		return false;
	}
	function gdrvUnique(value, index, array) {
	  return array.indexOf(value) === index;
	}
	/*localStorage.removeItem("gd_recently_viewed");*/
	var post_id = '<?php echo absint( $get_post_id ); ?>',
		post_type = '<?php echo esc_js( $get_post_type ); ?>',
		reviewed_arr = {},
		recently_reviewed = JSON.parse(localStorage.getItem('gd_recently_viewed'));
	if (null != recently_reviewed) {
		if (gdrv_is_not_empty(recently_reviewed)) {
			if (post_type in recently_reviewed) {
				var temp_post_arr = [];
				if (recently_reviewed[post_type].length > 0) {
					temp_post_arr = recently_reviewed[post_type];
				}
				if (jQuery.inArray(post_id, temp_post_arr) !== -1) {
					temp_post_arr.splice(jQuery.inArray(post_id, temp_post_arr), 1);
				}
				temp_post_arr.push(post_id);
				temp_post_arr = temp_post_arr.filter(gdrvUnique);
				/* Limit to 50 per CPT */
				if (temp_post_arr.length > 50) {
					temp_post_arr = temp_post_arr.slice(-50);
				}
				recently_reviewed[post_type] = temp_post_arr;
			} else {
				recently_reviewed[post_type] = [post_id];
			}
		} else {
			recently_reviewed[post_type] = [post_id];
		}
		localStorage.setItem("gd_recently_viewed", JSON.stringify(recently_reviewed));
	} else {
		reviewed_arr[post_type] = [post_id];
		localStorage.setItem("gd_recently_viewed", JSON.stringify(reviewed_arr));
	}
});
				<?php if ( 0 ) { ?></script><?php }

				$script = ob_get_clean();

				return trim( $script );
			}
		}

		return '';
	}

	public function enqueue_script(){
		wp_add_inline_script( 'geodir', self::geodir_recently_viewed_posts() );
	}
}