<?php
/**
 * Textarea/HTML Field Output Rendering Trait
 *
 * Handles output rendering for textarea and HTML fields with content processing.
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

/**
 * Textarea/HTML field output methods.
 *
 * @since 3.0.0
 */
trait TextareaFieldOutputTrait {

	/**
	 * Render the output HTML for textarea/HTML field types.
	 *
	 * Replaces: geodir_cf_textarea(), geodir_cf_html()
	 *
	 * @param object|array $gd_post GeoDirectory post object with custom fields already loaded.
	 * @param array        $args    Output arguments:
	 *                              - 'show' (string|array): What to display.
	 *                              - 'location' (string): Output location.
	 * @return string
	 */
	public function render_output( $gd_post, $args = [] ) {
		global $gd_skip_the_content;

		// Use the $gd_post directly (no DB call needed - already has all custom fields!)
		if ( ! is_object( $gd_post ) ) {
			$gd_post = (object) $gd_post;
		}

		if ( empty( $gd_post ) ) {
			return '';
		}

		// Parse args with defaults
		$args = wp_parse_args( $args, [
			'show'     => '',
			'location' => '',
		] );

		$location = $args['location'];
		$html_var = $this->field_data['htmlvar_name'];
		$field_type = $this->field_data['field_type'];

		// Parse output arguments (convert string to array)
		$output = $this->parse_output_args( $args['show'] );

		// Block demo content
		if ( $this->is_block_demo() ) {
			$gd_post = $this->set_demo_content( $gd_post, $field_type, $html_var );
		}

		// Get field value
		$value = isset( $gd_post->{$html_var} ) ? $gd_post->{$html_var} : '';

		// Empty value check
		if ( empty( $value ) ) {
			return '';
		}

		$html = '';

		// Apply custom filters first
		$html = $this->apply_output_filters( $html, $location, $output );

		// If filters provided custom HTML, return it
		if ( ! empty( $html ) ) {
			return $html;
		}

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return stripslashes( $value );
		}

		// Get extra field settings
		$extra_fields = ! empty( $this->field_data['extra_fields'] ) ? stripslashes_deep( maybe_unserialize( $this->field_data['extra_fields'] ) ) : null;
		$embed = ! empty( $extra_fields['embed'] ) || $html_var == 'video' ? true : false;

		// Process content based on field type
		$content = $this->process_content( $value, $html_var, $embed, $output );

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return $content;
		}

		// Apply content limit if specified
		if ( ! empty( $output['limit'] ) ) {
			$limit = absint( $output['limit'] );
			$content = wp_trim_words( $content, $limit, '' );
		}

		// Build full HTML output
		$design_style = $this->get_design_style();
		$icon_data = $this->process_icon();
		$field_icon_style = $icon_data['style'];
		$field_icon_html = $icon_data['icon_html'];

		$css_class = isset( $this->field_data['css_class'] ) ? $this->field_data['css_class'] : '';

		// Add position-relative for design style
		if ( $design_style ) {
			$css_class .= ' position-relative';
		}

		// Handle fade effect (max-height)
		$max_height = ! empty( $output['fade'] ) ? absint( $output['fade'] ) . 'px' : '';
		$max_height_style = $max_height ? " style='max-height:$max_height;overflow:hidden;' " : '';

		$html = '<div class="geodir_post_meta ' . esc_attr( $css_class ) . ' geodir-field-' . esc_attr( $html_var ) . '" ' . $max_height_style . '>';

		$maybe_secondary_class = isset( $output['icon'] ) ? 'gv-secondary' : '';

		// Icon
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon geodir-i-text" style="' . esc_attr( $field_icon_style ) . '">' . $field_icon_html;
		}

		// Label
		if ( $output == '' || isset( $output['label'] ) ) {
			$frontend_title = isset( $this->field_data['frontend_title'] ) ? trim( $this->field_data['frontend_title'] ) : '';
			if ( $frontend_title ) {
				$html .= '<span class="geodir_post_meta_title ' . esc_attr( $maybe_secondary_class ) . '">' . __( $frontend_title, 'geodirectory' ) . ': </span>';
			}
		}

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}

		// Value
		if ( $output == '' || isset( $output['value'] ) ) {
			if ( $content ) {
				$html .= $content;

				// Add "read more" link if specified
				if ( isset( $output['more'] ) ) {
					$post_id = isset( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;
					$more_text = empty( $output['more'] ) ? __( 'Read more...', 'geodirectory' ) : __( $output['more'], 'geodirectory' );
					$link = get_permalink( $post_id ) . '#' . $html_var;
					$link_class = ! empty( $output['fade'] ) ? 'gd-read-more-fade' : '';
					$link_style = '';

					if ( $design_style && $max_height ) {
						$link_class .= ' w-100 position-absolute text-center pt-5';
						$link_style .= 'bottom:0;left:0;background-image: linear-gradient(to bottom,transparent,#fff);';
					}

					$html .= ' <a href="' . esc_url( $link ) . '" class="gd-read-more ' . esc_attr( $link_class ) . '" style="' . esc_attr( $link_style ) . '">' . esc_html( $more_text ) . '</a>';
				}
			}
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Process content based on field type and settings.
	 *
	 * @param string $value    Raw field value.
	 * @param string $html_var Field htmlvar_name.
	 * @param bool   $embed    Whether to process embeds.
	 * @param array  $output   Output settings.
	 * @return string Processed content.
	 */
	protected function process_content( $value, $html_var, $embed, $output ) {
		global $gd_skip_the_content, $wp_embed;

		$value = stripslashes( $value );
		$design_style = $this->get_design_style();
		$field_type = $this->field_data['field_type'];

		// Set global variable to prevent looping on some themes/plugins
		$gd_skip_the_content = true;

		$content = '';

		// Handle post_content specially
		if ( $html_var == 'post_content' ) {
			if ( isset( $output['strip'] ) ) {
				$content = wp_strip_all_tags( apply_filters( 'the_content', $value ) );
			} else {
				$content = apply_filters( 'the_content', $value );
			}
		} else {
			// Regular textarea/html field
			if ( isset( $output['strip'] ) ) {
				// Strip all tags for plain text output
				$content = wp_strip_all_tags( do_shortcode( wpautop( $value ) ) );
			} else {
				// Process embeds for textarea with embed enabled
				if ( $field_type === 'textarea' && $embed ) {
					// Check for Matterport
					$matterport = $value ? parse_url( $value, PHP_URL_HOST ) : '';
					$matterport = $matterport && strpos( $matterport, 'my.matterport.com' ) === 0;

					$value = $wp_embed->autoembed( $value );

					// Fix Matterport sandbox attribute
					if ( $matterport ) {
						$value = str_replace( 'sandbox="allow-scripts"', 'sandbox="allow-scripts allow-same-origin"', $value );
					}
				}

				// Process shortcodes and wpautop
				if ( $field_type === 'html' ) {
					$content = wpautop( do_shortcode( stripslashes( $value ) ) );
				} else {
					$content = do_shortcode( wpautop( $value ) );
				}
			}
		}

		// Wrap iframes in responsive container for design style
		if ( $design_style && ! isset( $output['strip'] ) ) {
			$content = str_replace(
				[ '<iframe ', '</iframe>' ],
				[ '<div class="geodir-embed-container embed-responsive embed-responsive-16by9 ratio ratio-16x9"><iframe ', '</iframe></div>' ],
				$content
			);
		}

		$gd_skip_the_content = false;

		return $content;
	}

	/**
	 * Set demo content for block editor.
	 *
	 * @param object $gd_post    Post object.
	 * @param string $field_type Field type.
	 * @param string $html_var   Field htmlvar_name.
	 * @return object Modified post object.
	 */
	protected function set_demo_content( $gd_post, $field_type, $html_var ) {
		if ( $html_var == 'video' ) {
			$gd_post->{$html_var} = 'https://www.youtube.com/watch?v=eEzD-Y97ges';
		} elseif ( $html_var == 'virtual_tour' || $html_var == 'cf360_tour' ) {
			$gd_post->{$html_var} = '<iframe border="0" loading="lazy" src="https://my.matterport.com/show/?m=Zh14WDtkjdC"></iframe>';
		} else {
			if ( $field_type === 'html' ) {
				$gd_post->{$html_var} = '<b>This is some bold HTML</b>';
			} else {
				$gd_post->{$html_var} = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam risus metus, rutrum in nunc eu, vestibulum iaculis lacus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Aenean tristique arcu et eros convallis elementum. Maecenas sit amet quam eu velit euismod viverra. Etiam magna augue, mollis id nisi sit amet, eleifend sagittis tortor. Suspendisse vitae dignissim arcu, ac elementum eros. Mauris hendrerit at massa ut pellentesque.';
			}
		}

		return $gd_post;
	}

	/**
	 * Helper methods from AbstractFieldOutput.
	 */
	abstract protected function parse_output_args( $args );
	abstract protected function apply_output_filters( $html, $location, $output );
	abstract protected function process_icon();
	abstract protected function is_block_demo();
	abstract protected function get_design_style();
}
