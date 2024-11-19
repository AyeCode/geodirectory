<?php
namespace ElementorPro\Modules\Posts\Skins;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Widget_Base;
use ElementorPro\Plugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Props to this plugin  https://wordpress.org/plugins/ele-custom-skin/
 *
 * Class GeoDir_Elementor_Skin_Posts
 * @package ElementorPro\Modules\Posts\Skins
 */
class GeoDir_Elementor_Skin_Posts extends Skin_Base {
	private $template_cache = [];
	private $pid;
	public $settings;
	public $used_templates = [];

	public function get_id() {
		return 'gd_custom';
	}

	public function get_title() {
		return __( 'GD Archive Item', 'geodirectory' );
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/posts/' . $this->get_id() . '_section_design_layout/after_section_end', [ $this, 'register_navigation_design_controls' ] );
		add_action( 'elementor/element/posts/section_pagination/after_section_end', [ $this, 'register_navigation_controls' ] );
	}

	public function register_navigation_controls() {
		do_action( 'geodir_ecs_after_pagination_controls', $this );
	}

	public function register_navigation_design_controls() {
		do_action( 'geodir_ecs_after_style_controls', $this );
	}

	public function register_controls( Widget_Base $widget ) {
		$this->parent = $widget;

		$this->add_control(
			'skin_template',
			[
				'label' => __( 'Select a default template', 'geodirectory' ),
				'description' => '<div style="text-align:center;"><a target="_blank" style="text-align: center;font-style: normal;" href="' . esc_url( admin_url( '/edit.php?post_type=elementor_library&tabs_group&elementor_library_type=geodirectory-archive-item' ) ) . '" class="elementor-button elementor-button-default elementor-repeater-add">' . __( 'Create/edit a Loop Template', 'geodirectory' ) . '</a></div>',
				'type' => Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => [],
				'options' => $this->get_skin_template(),
			]
		);

		$this->add_control(//this would make use of 100% if width
			'view',
			[
				'label' => __( 'View', 'geodirectory' ),
				'type' => \Elementor\Controls_Manager::HIDDEN,
				'default' => 'top',
				'prefix_class' => 'elementor-posts--thumbnail-',
			]
		);

		do_action( 'geodir_ecs_after_control', $this );

		$this->add_control(
			'hr2',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);

	parent::register_controls( $widget );
		$this->remove_control( 'img_border_radius' );
		$this->remove_control( 'meta_data' );
		$this->remove_control( 'item_ratio' );
		$this->remove_control( 'image_width' );
		$this->remove_control( 'show_title' );
		$this->remove_control( 'title_tag' );
		$this->remove_control( 'masonry' );
		$this->remove_control( 'thumbnail' );
		$this->remove_control( 'thumbnail_size' );
		$this->remove_control( 'show_read_more' );
		$this->remove_control( 'read_more_text' );
		$this->remove_control( 'show_excerpt' );
		$this->remove_control( 'excerpt_length' );
	}

	private function get_post_id(){
		return $this->pid;
	}

	function get_skin_template(){
		global $wpdb;

		$cache = wp_cache_get( "geodir_elementor_pro_skins" );
		if ( $cache !== false ) {
			return $cache;
		}

		$templates = $wpdb->get_results(
			"SELECT $wpdb->term_relationships.object_id as ID, $wpdb->posts.post_title as post_title FROM $wpdb->term_relationships
				INNER JOIN $wpdb->term_taxonomy ON
					$wpdb->term_relationships.term_taxonomy_id=$wpdb->term_taxonomy.term_taxonomy_id
				INNER JOIN $wpdb->terms ON
					$wpdb->term_taxonomy.term_id=$wpdb->terms.term_id AND $wpdb->terms.slug='geodirectory-archive-item'
				INNER JOIN $wpdb->posts ON
					$wpdb->term_relationships.object_id=$wpdb->posts.ID
			WHERE  $wpdb->posts.post_status='publish'"
		);

		$options = [ 0 => __( 'Select a template', 'geodirectory' ) ];
		foreach ( $templates as $template ) {
			$options[ $template->ID ] = $template->post_title;
		}

		wp_cache_set( "geodir_elementor_pro_skins", $options );

		return $options;
	}

	public function render_amp() {

	}

	/**
	 * This is for terms we don't need passid so we can actually add them in cache.
	 */
	protected function set_used_template( $skin ) {
		if ( ! $skin ) {
			return;
		}

		$this->used_templates[ $skin ] = $skin;
	}

	protected function get_template() {
		global $gdecs_render_loop, $wp_query, $gd_loop_wp_query, $gdecs_index, $geodir_el_archive_item_tl;

		$gdecs_index++;
		$old_query = $wp_query;
		$gd_loop_wp_query = $wp_query;
		$new_query = new \WP_Query( array( 'p' => get_the_ID(), 'post_type' => get_post_type() ) );
		$wp_query = $new_query;
		$settings = $this->parent->get_settings();
		$this->pid = get_the_ID(); // set the current id in private var useful to passid
		$default_template = $this->get_instance_value( 'skin_template' ) ;
		$template = $default_template;

		$template = apply_filters( 'geodir_ecs_action_template', $template, $this, $gdecs_index );
		$template = $this->get_current_ID( $template );

		$gdecs_render_loop = get_the_ID() . "," . $template;

		$return = '';
		if ( $template ) {
			$this->set_used_template( $template );

			$geodir_el_archive_item_tl = $template;

			$return = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template );

			if ( $return && version_compare( ELEMENTOR_VERSION, '3.0.0', '<' ) ) {
				$css_file = \Elementor\Core\DynamicTags\Dynamic_CSS::create( $this->pid, $template );
				$css_file->enqueue();

				if ( $css = $css_file->get_content() ) {
					$css = str_replace( '.elementor-' . $this->pid . ' ', '.elementor-' . $template . '.post-' . $this->pid . ' ', $css );
					$css = '<style id="elementor-post-dynamic-' . $this->pid . '">' . $css . '</style>';
					$return = $css . $return;
				}
			}
		}

		$gdecs_render_loop = false;
		$wp_query = $old_query;

		return $return;
	}

	/**
	 * This is for multilang porpouses. Ex: WPML.
	 */
	private function get_current_ID( $id ) {
		$newid = apply_filters( 'wpml_object_id', $id, 'elementor_library', TRUE  );

		return $newid ? $newid : $id;
	}

	protected function render_post_header() {
		$classes = 'elementor-post elementor-grid-item';
		$parent_settings = $this->parent->get_settings();
		$parent_settings[ $this->get_id().'_post_slider' ] = isset( $parent_settings[ $this->get_id() . '_post_slider' ] )? $parent_settings[ $this->get_id() . '_post_slider' ] : "";

		if ( $parent_settings[ $this->get_id() . '_post_slider' ] == "yes") {
			$classes .= ' swiper-slide';
		}
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( [ $classes ] ); ?>>
		<?php
	}

	protected function render_post_footer() {
	  ?></article><?php
	}

	protected function render_post() {
		global $geodir_el_archive_item_tl;

		do_action( 'geodir_ecs_before_render_post_header', $this );
			$this->render_post_header();
		do_action( 'geodir_ecs_after_render_post_header', $this );

		ob_start();
		if ( $this->get_instance_value( 'skin_template' ) ) {
			if ( $this->get_instance_value( 'use_keywords' ) == "yes" ) {
				global $post;
				$template = $this->get_template();
				$new_template = apply_filters( 'geodir_ecs_dynamic_filter', $template, $post, "", $this->parent->get_settings() );
				echo $new_template ? $new_template : $template;
			} else {
				echo $this->get_template();
			}
		} else  {
			echo '<div style="display:table;border:1px solid #c6ced5; background:#dde1e5; width:100%; height:100%; min-height:200px;text-align:center; padding:20px;"><span style="vertical-align:middle;display: table-cell;color:#8995a0;">' . __( "Please select a default template! ", 'geodirectory' ) . '</span></div>';
		}
		$item_content = ob_get_clean();

		// Remove css with template id.
		if ( $item_content && $geodir_el_archive_item_tl && version_compare( ELEMENTOR_VERSION, '3.0.0', '<' ) && get_post_type( $geodir_el_archive_item_tl ) == 'elementor_library' ) {
			$item_content = preg_replace( '#<style id="elementor-post-dynamic-' . $geodir_el_archive_item_tl . '">(.*?)</style>#', '', $item_content );
		}

		echo $item_content;

		do_action( 'geodir_ecs_before_render_post_footer', $this );
			$this->render_post_footer();
		do_action( 'geodir_ecs_after_render_post_footer', $this );

		unset( $geodir_el_archive_item_tl );
	}

	protected function render_loop_header() {
		$parent_settings = $this->parent->get_settings();
		$parent_settings[ $this->get_id().'_post_slider' ] = isset( $parent_settings[ $this->get_id() . '_post_slider'] )? $parent_settings[ $this->get_id() . '_post_slider' ] : "";

		$this->parent->add_render_attribute( 'container', [
			'class' => [
				'elementor-posts-container',
				'elementor-posts',
				$parent_settings[ $this->get_id().'_post_slider' ] == "yes" ? 'swiper-wrapper' : 'elementor-grid',
				$this->get_container_class(),
			],
		] );

		if ( $parent_settings[ $this->get_id() . '_post_slider' ] == "yes" ) {
			echo '<div class="swiper-container swiper">';
		}
		?>
		<div <?php echo $this->parent->get_render_attribute_string( 'container' ); ?>>
		<?php
	}

	protected function render_loop_footer() {
		?>
		</div>
		<?php

		$parent_settings = $this->parent->get_settings();
		$parent_settings[ $this->get_id() . '_post_slider' ] = isset( $parent_settings[ $this->get_id() . '_post_slider' ] )? $parent_settings[ $this->get_id() . '_post_slider' ] : "";
		if ( $parent_settings[ $this->get_id() . '_post_slider' ] == "yes" ) {
			$this->slider_elements();
			echo '</div>';
		}

		if ( '' === $parent_settings['pagination_type'] ) {
			return;
		}

		$page_limit = $this->parent->get_query()->max_num_pages;
		if ( '' !== $parent_settings['pagination_page_limit'] ) {
			$page_limit = min( $parent_settings['pagination_page_limit'], $page_limit );
		}

		if ( 2 > $page_limit ) {
			return;
		}

		$this->parent->add_render_attribute( 'pagination', 'class', 'elementor-pagination' );

		$has_numbers = in_array( $parent_settings['pagination_type'], [ 'numbers', 'numbers_and_prev_next' ] );
		$has_prev_next = in_array( $parent_settings['pagination_type'], [ 'prev_next', 'numbers_and_prev_next' ] );

		$links = [];

		if ( $has_numbers ) {
			$paginate_args = [
				'type' => 'array',
				'current' => $this->parent->get_current_page(),
				'total' => $page_limit,
				'prev_next' => false,
				'show_all' => 'yes' !== $parent_settings['pagination_numbers_shorten'],
				'before_page_number' => '<span class="elementor-screen-only">' . __( 'Page', 'geodirectory' ) . '</span>',
			];

			if ( is_singular() && ! is_front_page() ) {
				global $wp_rewrite;

				if ( $wp_rewrite->using_permalinks() ) {
					$paginate_args['base'] = trailingslashit( get_permalink() ) . '%_%';
					$paginate_args['format'] = user_trailingslashit( '%#%', 'single_paged' );
				} else {
					$paginate_args['format'] = '?page=%#%';
				}
			}

			$links = paginate_links( $paginate_args );
		}

		if ( $has_prev_next ) {
			$prev_next = $this->parent->get_posts_nav_link( $page_limit );
			array_unshift( $links, $prev_next['prev'] );
			$links[] = $prev_next['next'];
		}

		?>
		<nav class="elementor-pagination" role="navigation" aria-label="<?php esc_attr_e( 'Pagination', 'geodirectory' ); ?>">
			<?php echo implode( PHP_EOL, $links ); ?>
		</nav>
		<?php
	}

	private function slider_elements(){
		$this->settings = $this->parent->get_settings();

		do_action( 'geodir_ecs_after_slider_elements', $this );
	}

	private function nothing_found(){
		$this->render_loop_header();

		$should_escape = apply_filters( 'elementor_pro/theme_builder/archive/escape_nothing_found_message', true );
		$message = $this->parent->get_settings_for_display( 'nothing_found_message' );
		if ( $should_escape ) {
			$message = esc_html( $message );
		}

		$message = '<div class="elementor-posts-nothing-found">' . $message . '</div>';
		do_action( 'geodir_ecs_not_found', $this );
		echo  $message;

		$this->render_loop_footer();
	}

	public function render() {
		$this->parent->query_posts();

		/** @var \WP_Query $query */
		$query = $this->parent->get_query();

		do_action( "geodir_ecs_before_loop_query", $query, $this );

		if ( ! $query->found_posts ) {
			$this->nothing_found();
			return;
		}

		$this->render_loop_header();

		// It's the global `wp_query` it self. and the loop was started from the theme.
		if ( $query->in_the_loop ) {
			$this->current_permalink = get_permalink();
			$this->render_post();
		} else {
			while ( $query->have_posts() ) {
				$query->the_post();

				$this->current_permalink = get_permalink();
				$this->render_post();
			}
		}

		do_action( "geodir_ecs_after_loop_query", $query, $this );

		wp_reset_postdata();

		$this->render_loop_footer();
	}

	public function get_settings_for_display( $setting_key = null ) {
		if ( $setting_key ) {
			$settings = [
				$setting_key => $this->parent->get_settings( $setting_key ),
			];
		} else {
			$settings = $this->parent->get_active_settings();
		}

		$controls = $this->parent->get_controls();
		$controls = array_intersect_key( $controls, $settings );
		$parsed_settings = $this->parent->parse_dynamic_settings( $settings, $controls );

		if ( $setting_key ) {
			return $parsed_settings[ $setting_key ];
		}

		return $parsed_settings;
	}
}

/**
 * It seems the same skin brakes if set to 2 widgets in the same time.
 */
class GeoDir_Elementor_Skin_Archive extends GeoDir_Elementor_Skin_Posts {
	private $template_cache =[];
	private $pid;

	protected function _register_controls_actions() {
		add_action( 'elementor/element/archive-posts/section_layout/before_section_end', [ $this, 'register_controls' ] );
		add_action( 'elementor/element/archive-posts/section_layout/after_section_end', [ $this, 'register_style_sections' ] );

		add_action( 'elementor/element/archive-posts/' . $this->get_id() . '_section_design_layout/after_section_end', [ $this, 'register_navigation_design_controls' ] );
		add_action( 'elementor/element/archive-posts/section_pagination/after_section_end', [ $this, 'register_navigation_controls' ] );
	}

	public function get_id() {
		return 'gd_archive_custom';
	}

	public function get_title() {
		return __( 'GD Archive Item', 'geodirectory' );
	}

	/* Remove `posts_per_page` control */
	protected function register_post_count_control() {
	}

}

// Add a custom skin for the POSTS widget
add_action( 'elementor/widget/posts/skins_init', function( $widget ) {
	$widget->add_skin( new GeoDir_Elementor_Skin_Posts( $widget ) );
} );

// Add a custom skin for the POST Archive widget
add_action( 'elementor/widget/archive-posts/skins_init', function( $widget ) {
	$widget->add_skin( new GeoDir_Elementor_Skin_Archive( $widget ) );
} );
