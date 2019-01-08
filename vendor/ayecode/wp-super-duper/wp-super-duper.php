<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Super_Duper' ) ) {


	/**
	 * A Class to be able to create a Widget, Shortcode or Block to be able to output content for WordPress.
	 *
	 * Should not be called direct but extended instead.
	 *
	 * Class WP_Super_Duper
	 * @since 1.0.3 is_block_content_call() method added.
	 * @since 1.0.3 Placeholder text will be shown for widget that return no block content.
	 * @since 1.0.4 is_elementor_widget_output() method added.
	 * @since 1.0.4 is_elementor_preview() method added.
	 * @since 1.0.5 Block checkbox options are set as true by default even when set as false - FIXED
	 * @ver 1.0.5
	 */
	class WP_Super_Duper extends WP_Widget {


		public $version = "1.0.5";
		public $block_code;
		public $options;
		public $base_id;
		public $arguments = array();
		public $instance = array();
		private $class_name;

		/**
		 * Take the array options and use them to build.
		 */
		public function __construct( $options ) {
			global $sd_widgets;

			$sd_widgets[ $options['base_id'] ] = array( 'name'       => $options['name'],
			                                            'class_name' => $options['class_name']
			);
			$this->base_id                     = $options['base_id'];
			// lets filter the options before we do anything
			$options       = apply_filters( "wp_super_duper_options", $options );
			$options       = apply_filters( "wp_super_duper_options_{$this->base_id}", $options );
			$options       = $this->add_name_from_key( $options );
			$this->options = $options;

			$this->base_id   = $options['base_id'];
			$this->arguments = isset( $options['arguments'] ) ? $options['arguments'] : array();

			// init parent
			parent::__construct( $options['base_id'], $options['name'], $options['widget_ops'] );

			if ( isset( $options['class_name'] ) ) {
				// register widget
				$this->class_name = $options['class_name'];

				// register shortcode
				$this->register_shortcode();

				// register block
				add_action( 'admin_enqueue_scripts', array( $this, 'register_block' ) );
			}

			// add the CSS and JS we need ONCE
			global $sd_widget_scripts;

			if ( ! $sd_widget_scripts ) {
				wp_add_inline_script( 'admin-widgets', $this->widget_js() );
				wp_add_inline_script( 'customize-controls', $this->widget_js() );
				wp_add_inline_style( 'widgets', $this->widget_css() );

				$sd_widget_scripts = true;

				// add shortcode insert button once
				add_action( 'media_buttons', array( $this, 'shortcode_insert_button' ) );
				if($this->is_preview()) {
					add_action( 'wp_footer', array( $this, 'shortcode_insert_button_script' ) );
				}
				add_action( 'wp_ajax_super_duper_get_widget_settings', array( __CLASS__, 'get_widget_settings' ) );
			}

			do_action( 'wp_super_duper_widget_init', $options, $this );
		}

		/**
		 * Get widget settings.
		 *
		 * @since 1.0.0
		 */
		public static function get_widget_settings() {
			global $sd_widgets;

			$shortcode = isset( $_REQUEST['shortcode'] ) && $_REQUEST['shortcode'] ? sanitize_title_with_dashes( $_REQUEST['shortcode'] ) : '';
			if ( ! $shortcode ) {
				wp_die();
			}
			$widget_args = isset( $sd_widgets[ $shortcode ] ) ? $sd_widgets[ $shortcode ] : '';
			if ( ! $widget_args ) {
				wp_die();
			}
			$class_name = isset( $widget_args['class_name'] ) && $widget_args['class_name'] ? $widget_args['class_name'] : '';
			if ( ! $class_name ) {
				wp_die();
			}

			// invoke an instance method
			$widget = new $class_name;

			ob_start();
			$widget->form( array() );
			$form = ob_get_clean();
			echo "<form id='$shortcode'>" . $form . "<div class=\"widget-control-save\"></div></form>";
			echo "<style>" . $widget->widget_css() . "</style>";
			echo "<script>" . $widget->widget_js() . "</script>";
			?>
			<?php
			wp_die();
		}

		/**
		 * Insert shortcode builder button to classic editor (not inside Gutenberg, not needed).
		 *
		 * @since 1.0.0
		 *
		 * @param string $editor_id Optional. Shortcode editor id. Default null.
		 * @param string $insert_shortcode_function Optional. Insert shotcode function. Default null.
		 */
		public static function shortcode_insert_button( $editor_id = '', $insert_shortcode_function = '' ) {
			global $sd_widgets, $shortcode_insert_button_once;
			if ( $shortcode_insert_button_once ) {
				return;
			}
			add_thickbox();
			?>
			<div id="super-duper-content" style="display:none;">

				<div class="sd-shortcode-left-wrap">
					<?php
					asort( $sd_widgets );
					if ( ! empty( $sd_widgets ) ) {
						echo '<select class="widefat" onchange="sd_get_shortcode_options(this);">';
						echo "<option>" . __( 'Select shortcode' ) . "</option>";
						foreach ( $sd_widgets as $shortcode => $class ) {
							echo "<option value='" . esc_attr( $shortcode ) . "'>" . esc_attr( $shortcode ) . " (" . esc_attr( $class['name'] ) . ")</option>";
						}
						echo "</select>";

					}
					?>
					<div class="sd-shortcode-settings"></div>

				</div>

				<div class="sd-shortcode-right-wrap">
					<textarea id='sd-shortcode-output' disabled></textarea>
					<div id='sd-shortcode-output-actions'>
						<button class="button"
						        onclick="sd_insert_shortcode(<?php if(!empty($editor_id)){echo "'".$editor_id."'";}?>)"><?php _e( 'Insert shortcode' ); ?></button>
						<button class="button"
						        onclick="sd_copy_to_clipboard()"><?php _e( 'Copy shortcode' ); ?></button>
					</div>
				</div>

			</div>

			<?php
			// if Font Awesome is available then show a icon if not show a WP icon.
			$button_string = wp_style_is( 'font-awesome', 'enqueued' ) && 1 == 2 ? '<i class="fas fa-cubes" aria-hidden="true"></i>' : '<span style="vertical-align: middle;line-height: 18px;font-size: 20px;" class="dashicons dashicons-screenoptions"></span>';
			?>

			<a href="#TB_inline?width=100%&height=550&inlineId=super-duper-content"
			   class="thickbox button super-duper-content-open"
			   title="<?php _e( 'Add Shortcode' ); ?>"><?php echo $button_string; ?></a>

			<?php
			self::shortcode_insert_button_script($editor_id, $insert_shortcode_function);
			$shortcode_insert_button_once = true;
		}

		/**
		 * Output the JS and CSS for the shortcode insert button.
		 *
		 * @since 1.0.6
		 * @param string $editor_id
		 * @param string $insert_shortcode_function
		 */
		public static function shortcode_insert_button_script($editor_id = '', $insert_shortcode_function = ''){
			?>
			<style>
				.sd-shortcode-left-wrap {
					float: left;
					width: 60%;
				}

				.sd-shortcode-left-wrap .gd-help-tip {
					float: none;
				}

				.sd-shortcode-left-wrap .widefat{
					border-spacing: 0;
					width: 100%;
					clear: both;
					margin: 0;
					border: 1px solid #ddd;
					box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
					background-color: #fff;
					color: #32373c;
					outline: 0;
					transition: 50ms border-color ease-in-out;
					padding: 3px 5px;
				}

				.sd-shortcode-left-wrap input[type=checkbox].widefat{
					border: 1px solid #b4b9be;
					background: #fff;
					color: #555;
					clear: none;
					cursor: pointer;
					display: inline-block;
					line-height: 0;
					height: 16px;
					margin: -4px 4px 0 0;
					margin-top: 0;
					outline: 0;
					padding: 0!important;
					text-align: center;
					vertical-align: middle;
					width: 16px;
					min-width: 16px;
					-webkit-appearance: none;
					box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
					transition: .05s border-color ease-in-out;
				}

				.sd-shortcode-left-wrap input[type=checkbox]:checked:before {
					content: "\f147";
					margin: -3px 0 0 -4px;
					color: #1e8cbe;
					float: left;
					display: inline-block;
					vertical-align: middle;
					width: 16px;
					font: normal 21px/1 dashicons;
					speak: none;
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
				}



				#sd-shortcode-output-actions button,
				.sd-advanced-button{
					color: #555;
					border-color: #ccc;
					background: #f7f7f7;
					box-shadow: 0 1px 0 #ccc;
					vertical-align: top;
					display: inline-block;
					text-decoration: none;
					font-size: 13px;
					line-height: 26px;
					height: 28px;
					margin: 0;
					padding: 0 10px 1px;
					cursor: pointer;
					border-width: 1px;
					border-style: solid;
					-webkit-appearance: none;
					border-radius: 3px;
					white-space: nowrap;
					box-sizing: border-box;
				}
				button.sd-advanced-button{
					background: #0073aa;
					border-color: #006799;
					box-shadow: inset 0 2px 0 #006799;
					vertical-align: top;
					color: #fff;
					text-decoration: none;
					text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
					float: right;
					margin-right: 3px !important;
					font-size: 20px !important;
				}

				.sd-shortcode-right-wrap {
					float: right;
					width: 35%;
				}

				#sd-shortcode-output{
					background: rgba(255,255,255,.5);
					border-color: rgba(222,222,222,.75);
					box-shadow: inset 0 1px 2px rgba(0,0,0,.04);
					color: rgba(51,51,51,.5);
					overflow: auto;
					padding: 2px 6px;
					line-height: 1.4;
					resize: vertical;
				}

				#sd-shortcode-output {
					height: 250px;
					width: 100%;
				}
			</style>
			<script>
				<?php
				if(! empty( $insert_shortcode_function )){
					echo $insert_shortcode_function;
				}else{

				/**
				 * Function for super duper insert shortcode.
				 *
				 * @since 1.0.0
				 */
				?>
				function sd_insert_shortcode($editor_id) {
					$shortcode = jQuery('#sd-shortcode-output').val();
					if ($shortcode) {

						if(!$editor_id){
							$editor_id = "<?php if(isset($_REQUEST['et_fb'])){echo "#main_content_content_vb_tiny_mce";}else{ echo "#wp-content-editor-container textarea";} ?>";
						}

						if (tinyMCE && tinyMCE.activeEditor && jQuery($editor_id).attr("aria-hidden") == "true") {
							tinyMCE.execCommand('mceInsertContent', false, $shortcode);
						} else {
							var $txt = jQuery($editor_id);
							var caretPos = $txt[0].selectionStart;
							var textAreaTxt = $txt.val();
							var txtToAdd = $shortcode;
							var textareaValue = textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos);
							$txt.val(textareaValue).change().keydown().blur().keyup().keypress();

							// set Divi react input value
							var input = document.getElementById("main_content_content_vb_tiny_mce");
							if(input){
								sd_setNativeValue(input, textareaValue);
							}

						}
						tb_remove();
					}
				}

				function sd_setNativeValue(element, value) {
					let lastValue = element.value;
					element.value = value;
					let event = new Event("input", { target: element, bubbles: true });
					// React 15
					event.simulated = true;
					// React 16
					let tracker = element._valueTracker;
					if (tracker) {
						tracker.setValue(lastValue);
					}
					element.dispatchEvent(event);
				}
				<?php }?>

				function sd_copy_to_clipboard() {
					/* Get the text field */
					var copyText = document.getElementById("sd-shortcode-output");
					//un-disable the field
					copyText.disabled = false;
					/* Select the text field */
					copyText.select();
					/* Copy the text inside the text field */
					document.execCommand("Copy");
					//re-disable the field
					copyText.disabled = true;
					/* Alert the copied text */
					alert("Copied the text: " + copyText.value);
				}
				function sd_get_shortcode_options($this) {

					$short_code = jQuery($this).val();
					if ($short_code) {

						var data = {
							'action': 'super_duper_get_widget_settings',
							'shortcode': $short_code,
							'attributes': 123,
							'post_id': 321,
							'_ajax_nonce': '<?php echo wp_create_nonce( 'super_duper_output_shortcode' );?>'
						};

						if (typeof ajaxurl === 'undefined') {
							var ajaxurl = "<?php echo admin_url('admin-ajax.php');?>";
						}

						jQuery.post(ajaxurl, data, function (response) {
							jQuery('.sd-shortcode-settings').html(response);

							jQuery('#' + $short_code).on('change', 'select', function () {
								sd_build_shortcode($short_code);
							}); // take care of select tags

							jQuery('#' + $short_code).on('change keypress keyup', 'input', function () {
								sd_build_shortcode($short_code);
							});

							sd_build_shortcode($short_code);

							// resize the window to fit
							setTimeout(function(){
								jQuery('#TB_ajaxContent').css('width', 'auto').css('height', '75vh');
							}, 200);


							return response;
						});
					}

				}

				function sd_build_shortcode($id) {

					var multiSelects = {};
					var multiSelectsRemove = [];

					$output = "[" + $id;

					$form_data = jQuery("#" + $id).serializeArray();

					// run checks for multiselects
					jQuery.each($form_data, function (index, element) {
						if (element && element.value) {
							$field_name = element.name.substr(element.name.indexOf("][") + 2);
							$field_name = $field_name.replace("]", "");
							// check if its a multiple
							if ($field_name.includes("[]")) {
								multiSelectsRemove[multiSelectsRemove.length] = index;
								$field_name = $field_name.replace("[]", "");
								if ($field_name in multiSelects) {
									multiSelects[$field_name] = multiSelects[$field_name] + "," + element.value;
								} else {
									multiSelects[$field_name] = element.value;
								}
							}
						}
					});

					// fix multiselects if any are found
					if (multiSelectsRemove.length) {

						// remove all multiselects
						multiSelectsRemove.reverse();
						multiSelectsRemove.forEach(function (index) {
							$form_data.splice(index, 1);
						});

						$ms_arr = [];
						// add multiselets back
						jQuery.each(multiSelects, function (index, value) {
							$ms_arr[$ms_arr.length] = {"name": "[][" + index + "]", "value": value};
						});
						$form_data = $form_data.concat($ms_arr);
					}


					if ($form_data) {
						$form_data.forEach(function (element) {

							if (element.value) {
								$field_name = element.name.substr(element.name.indexOf("][") + 2);
								$field_name = $field_name.replace("]", "");
								$output = $output + " " + $field_name + '="' + element.value + '"';
							}

						});
					}
					$output = $output + "]";
					jQuery('#sd-shortcode-output').html($output);
				}
			</script>
			<?php
		}

		public function widget_css() {
			ob_start();
			?>
			<style>
				.sd-advanced-setting {
					display: none;
				}

				.sd-advanced-setting.sd-adv-show {
					display: block;
				}

				.sd-argument.sd-require-hide,
				.sd-advanced-setting.sd-require-hide {
					display: none;
				}

				button.sd-advanced-button {
					margin-right: 3px !important;
					font-size: 20px !important;
				}
			</style>
			<?php
			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<style>',
				'</style>'
			), '', $output );
		}

		public function widget_js() {
			ob_start();
			?>
			<script>

				/**
				 * Toggle advanced settings visibility.
				 */
				function sd_toggle_advanced($this) {
					var form = jQuery($this).parents('form,.form');
					form.find('.sd-advanced-setting').toggleClass('sd-adv-show');
					return false;// prevent form submit
				}

				/**
				 * Check a form to see what items shoudl be shown or hidden.
				 */
				function sd_show_hide(form) {
					console.log('show/hide');
					jQuery(form).find(".sd-argument").each(function () {

						var $element_require = jQuery(this).data('element_require');

						if ($element_require) {

							$element_require = $element_require.replace("&#039;", "'"); // replace single quotes
							$element_require = $element_require.replace("&quot;", '"'); // replace double quotes

							if (eval($element_require)) {
								jQuery(this).removeClass('sd-require-hide');
							} else {
								jQuery(this).addClass('sd-require-hide');
							}
						}
					});
				}

				/**
				 * Initialise widgets from the widgets screen.
				 */
				function sd_init_widgets($selector) {
					jQuery(".sd-show-advanced").each(function (index) {
						sd_init_widget(this, $selector);
					});
				}

				/**
				 * Initialise a individual widget.
				 */
				function sd_init_widget($this, $selector) {
					console.log($selector);

					if (!$selector) {
						$selector = 'form';
					}
					// only run once.
					if (jQuery($this).data('sd-widget-enabled')) {
						return;
					} else {
						jQuery($this).data('sd-widget-enabled', true);
					}

					var $button = '<button title="<?php _e('Advanced Settings');?>" class="button button-primary right sd-advanced-button" onclick="sd_toggle_advanced(this);return false;"><i class="fas fa-sliders-h" aria-hidden="true"></i></button>';
					var form = jQuery($this).parents('' + $selector + '');

					if (jQuery($this).val() == '1' && jQuery(form).find('.sd-advanced-button').length == 0) {
						console.log('add advanced button');

						jQuery(form).find('.widget-control-save').after($button);
					} else {
						console.log('no advanced button');
						console.log(jQuery($this).val());
						console.log(jQuery(form).find('.sd-advanced-button').length);

					}

					// show hide on form change
					jQuery(form).change(function () {
						sd_show_hide(form);
					});

					// show hide on load
					sd_show_hide(form);
				}

				/**
				 * Init a customizer widget.
				 */
				function sd_init_customizer_widget(section) {
					if (section.expanded) {
						section.expanded.bind(function (isExpanding) {
							if (isExpanding) {
								// is it a SD widget?
								if (jQuery(section.container).find('.sd-show-advanced').length) {
									// init the widget
									sd_init_widget(jQuery(section.container).find('.sd-show-advanced'), ".form");
								}
							}
						});
					}
				}

				/**
				 * If on widgets screen.
				 */
				jQuery(function () {
					// if not in customizer.
					if (!wp.customize) {
						sd_init_widgets("form");
					}

					// init on widget added
					jQuery(document).on('widget-added', function (e, widget) {
						console.log('widget added');
						// is it a SD widget?
						if (jQuery(widget).find('.sd-show-advanced').length) {
							// init the widget
							sd_init_widget(jQuery(widget).find('.sd-show-advanced'), "form");
						}
					});

					// inint on widget updated
					jQuery(document).on('widget-updated', function (e, widget) {
						console.log('widget updated');

						// is it a SD widget?
						if (jQuery(widget).find('.sd-show-advanced').length) {
							// init the widget
							sd_init_widget(jQuery(widget).find('.sd-show-advanced'), "form");
						}
					});

				});


				/**
				 * We need to run this before jQuery is ready
				 */
				if (wp.customize) {
					wp.customize.bind('ready', function () {

						// init widgets on load
						wp.customize.control.each(function (section) {
							sd_init_customizer_widget(section);
						});

						// init widgets on add
						wp.customize.control.bind('add', function (section) {
							sd_init_customizer_widget(section);
						});

					});

				}
				<?php do_action( 'wp_super_duper_widget_js', $this ); ?>
			</script>
			<?php
			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
			return str_replace( array(
				'<script>',
				'</script>'
			), '', $output );
		}


		/**
		 * Set the name from the argument key.
		 *
		 * @param $options
		 *
		 * @return mixed
		 */
		private function add_name_from_key( $options, $arguments = false ) {
			if ( ! empty( $options['arguments'] ) ) {
				foreach ( $options['arguments'] as $key => $val ) {
					$options['arguments'][ $key ]['name'] = $key;
				}
			} elseif ( $arguments && is_array( $options ) && ! empty( $options ) ) {
				foreach ( $options as $key => $val ) {
					$options[ $key ]['name'] = $key;
				}
			}

			return $options;
		}

		/**
		 * Register the parent shortcode.
		 *
		 * @since 1.0.0
		 */
		public function register_shortcode() {
			add_shortcode( $this->base_id, array( $this, 'shortcode_output' ) );
			add_action( 'wp_ajax_super_duper_output_shortcode', array( __CLASS__, 'render_shortcode' ) );
		}

		/**
		 * Render the shortcode via ajax so we can return it to Gutenberg.
		 *
		 * @since 1.0.0
		 */
		public static function render_shortcode() {

			check_ajax_referer( 'super_duper_output_shortcode', '_ajax_nonce', true );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die();
			}

			// we might need the $post value here so lets set it.
			if ( isset( $_POST['post_id'] ) && $_POST['post_id'] ) {
				$post_obj = get_post( absint( $_POST['post_id'] ) );
				if ( ! empty( $post_obj ) && empty( $post ) ) {
					global $post;
					$post = $post_obj;
				}
			}

			if ( isset( $_POST['shortcode'] ) && $_POST['shortcode'] ) {
				$shortcode_name   = sanitize_title_with_dashes( $_POST['shortcode'] );
				$attributes_array = isset( $_POST['attributes'] ) && $_POST['attributes'] ? $_POST['attributes'] : array();
				$attributes       = '';
				if ( ! empty( $attributes_array ) ) {
					foreach ( $attributes_array as $key => $value ) {
						$attributes .= " " . sanitize_title_with_dashes( $key ) . "='" . wp_slash( $value ) . "' ";
					}
				}

				$shortcode = "[" . $shortcode_name . " " . $attributes . "]";

				echo do_shortcode( $shortcode );

			}
			wp_die();
		}

		/**
		 * Output the shortcode.
		 *
		 * @param array $args
		 * @param string $content
		 *
		 * @return string
		 */
		public function shortcode_output( $args = array(), $content = '' ) {
			$args = self::argument_values( $args );

			// add extra argument so we know its a output to gutenberg
			//$args
			$args = $this->string_to_bool( $args );


			$calss = isset( $this->options['widget_ops']['classname'] ) ? esc_attr( $this->options['widget_ops']['classname'] ) : '';

			$calss = apply_filters( 'wp_super_duper_div_classname', $calss, $args, $this );
			$calss = apply_filters( 'wp_super_duper_div_classname_' . $this->base_id, $calss, $args, $this );

			$attrs = apply_filters( 'wp_super_duper_div_attrs', '', $args, $this );
			$attrs = apply_filters( 'wp_super_duper_div_attrs_' . $this->base_id, '', $args, $this );

			$shortcode_args = array();
			$output         = '';
			$no_wrap        = isset( $this->options['no_wrap'] ) && $this->options['no_wrap'] ? true : false;
			$main_content   = $this->output( $args, $shortcode_args, $content );
			if ( $main_content && ! $no_wrap ) {
				// wrap the shortcode in a dive with the same class as the widget
				$output .= '<div class="' . $calss . '" ' . $attrs . '>';
				if ( ! empty( $args['title'] ) ) {
					// if its a shortcode and there is a title try to grab the title wrappers
					$shortcode_args = array( 'before_title' => '', 'after_title' => '' );
					if ( empty( $instance ) ) {
						global $wp_registered_sidebars;
						if ( ! empty( $wp_registered_sidebars ) ) {
							foreach ( $wp_registered_sidebars as $sidebar ) {
								if ( ! empty( $sidebar['before_title'] ) ) {
									$shortcode_args['before_title'] = $sidebar['before_title'];
									$shortcode_args['after_title']  = $sidebar['after_title'];
									break;
								}
							}
						}
					}
					$output .= $this->output_title( $shortcode_args, $args );
				}
				$output .= $main_content;
				$output .= '</div>';
			} elseif ( $main_content && $no_wrap ) {
				$output .= $main_content;
			}

			// if preview show a placeholder if empty
			if($this->is_preview() && $output==''){
				$output = $this->preview_placeholder_text("[{".$this->base_id."}]");
			}

			return $output;
		}

		/**
		 * Placeholder text to show if output is empty and we are on a preview/builder page.
		 *
		 * @param string $name
		 *
		 * @return string
		 */
		public function preview_placeholder_text($name = ''){
			return 	"<div style='background:#0185ba33;padding: 10px;'>".sprintf( __('Placeholder for: %s'), $name )."</div>";

		}

		/**
		 * Sometimes booleans values can be turned to strings, so we fix that.
		 *
		 * @param $options
		 *
		 * @return mixed
		 */
		public function string_to_bool( $options ) {
			// convert bool strings to booleans
			foreach ( $options as $key => $val ) {
				if ( $val == 'false' ) {
					$options[ $key ] = false;
				} elseif ( $val == 'true' ) {
					$options[ $key ] = true;
				}
			}

			return $options;
		}

		/**
		 * Get the argument values that are also filterable.
		 *
		 * @param $instance
		 *
		 * @return array
		 */
		public function argument_values( $instance ) {
			$argument_values = array();

			// set widget instance
			$this->instance = $instance;

			if ( empty( $this->arguments ) ) {
				$this->arguments = $this->get_arguments();
			}

			if ( ! empty( $this->arguments ) ) {
				foreach ( $this->arguments as $key => $args ) {
					// set the input name from the key
					$args['name'] = $key;
					//
					$argument_values[ $key ] = isset( $instance[ $key ] ) ? $instance[ $key ] : '';
					if ( $argument_values[ $key ] == '' && isset( $args['default'] ) ) {
						$argument_values[ $key ] = $args['default'];
					}
				}
			}

			return $argument_values;
		}

		/**
		 * Set arguments in super duper.
		 *
		 * @since 1.0.0
		 *
		 * @return array Set arguments.
		 */
		public function set_arguments() {
			return $this->arguments;
		}

		/**
		 * Get arguments in super duper.
		 *
		 * @since 1.0.0
		 *
		 * @return array Get arguments.
		 */
		public function get_arguments() {
			if ( empty( $this->arguments ) ) {
				$this->arguments = $this->set_arguments();
			}

			$this->arguments = apply_filters( 'wp_super_duper_arguments', $this->arguments, $this->options, $this->instance );
			$this->arguments = $this->add_name_from_key( $this->arguments, true );

			return $this->arguments;
		}

		/**
		 * This is the main output class for all 3 items, widget, shortcode and block, it is extended in the calling class.
		 *
		 * @param array $args
		 * @param array $widget_args
		 * @param string $content
		 */
		public function output( $args = array(), $widget_args = array(), $content = '' ) {

		}

		/**
		 * Add the dynamic block code inline when the wp-block in enqueued.
		 */
		public function register_block() {
			wp_add_inline_script( 'wp-blocks', $this->block() );
		}

		/**
		 * Check if we need to show advanced options.
		 *
		 * @return bool
		 */
		public function block_show_advanced() {

			$show      = false;
			$arguments = $this->arguments;

			if ( empty( $arguments ) ) {
				$arguments = $this->get_arguments();
			}

			if ( ! empty( $arguments ) ) {
				foreach ( $arguments as $argument ) {
					if ( isset( $argument['advanced'] ) && $argument['advanced'] ) {
						$show = true;
					}
				}
			}

			return $show;
		}


		/**
		 * Output the JS for building the dynamic Guntenberg block.
		 *
		 * @since 1.0.4 Added block_wrap property which will set the block wrapping output element ie: div, span, p or empty for no wrap.
		 * @return mixed
		 */
		public function block() {
			ob_start();
			?>
			<script>
				/**
				 * BLOCK: Basic
				 *
				 * Registering a basic block with Gutenberg.
				 * Simple block, renders and saves the same content without any interactivity.
				 *
				 * Styles:
				 *        editor.css — Editor styles for the block.
				 *        style.css  — Editor & Front end styles for the block.
				 */
				(function () {
					var __ = wp.i18n.__; // The __() for internationalization.
					var el = wp.element.createElement; // The wp.element.createElement() function to create elements.
					var editable = wp.blocks.Editable;
					var blocks = wp.blocks;
					var registerBlockType = wp.blocks.registerBlockType; // The registerBlockType() to register blocks.
					var is_fetching = false;
					var prev_attributes = [];

					/**
					 * Register Basic Block.
					 *
					 * Registers a new block provided a unique name and an object defining its
					 * behavior. Once registered, the block is made available as an option to any
					 * editor interface where blocks are implemented.
					 *
					 * @param  {string}   name     Block name.
					 * @param  {Object}   settings Block settings.
					 * @return {?WPBlock}          The block, if it has been successfully
					 *                             registered; otherwise `undefined`.
					 */
					registerBlockType('<?php echo str_replace( "_", "-", sanitize_title_with_dashes( $this->options['textdomain'] ) . '/' . sanitize_title_with_dashes( $this->options['class_name'] ) );  ?>', { // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
						title: '<?php echo $this->options['name'];?>', // Block title.
						description: '<?php echo esc_attr( $this->options['widget_ops']['description'] )?>', // Block title.
						icon: '<?php echo isset( $this->options['block-icon'] ) ? esc_attr( $this->options['block-icon'] ) : 'shield-alt';?>', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
						category: '<?php echo isset( $this->options['block-category'] ) ? esc_attr( $this->options['block-category'] ) : 'common';?>', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
						<?php if ( isset( $this->options['block-keywords'] ) ) {
						echo "keywords : " . $this->options['block-keywords'] . ",";
					}?>

						<?php

						$show_advanced = $this->block_show_advanced();

						$show_alignment = false;

						if ( ! empty( $this->arguments ) ) {
							echo "attributes : {";

							if ( $show_advanced ) {
								echo "show_advanced: {";
								echo "	type: 'boolean',";
								echo "  default: false,";
								echo "},";
							}

							// block wrap element
							if ( isset( $this->options['block-wrap'] ) ) { //@todo we should validate this?
								echo "block_wrap: {";
								echo "	type: 'string',";
								echo "  default: '" . esc_attr( $this->options['block-wrap'] ) . "',";
								echo "},";
							}


							foreach ( $this->arguments as $key => $args ) {

								// set if we should show alignment
								if ( $key == 'alignment' ) {
									$show_alignment = true;
								}

								$extra = '';

								if ( $args['type'] == 'checkbox' ) {
									$type    = 'boolean';
									$default = isset( $args['default'] ) && $args['default'] ? 'true' : 'false';
								} elseif ( $args['type'] == 'number' ) {
									$type    = 'number';
									$default = isset( $args['default'] ) ? "'" . $args['default'] . "'" : "''";
								} elseif ( $args['type'] == 'select' && ! empty( $args['multiple'] ) ) {
									$type = 'array';
									if ( is_array( $args['default'] ) ) {
										$default = isset( $args['default'] ) ? "['" . implode( "','", $args['default'] ) . "']" : "[]";
									} else {
										$default = isset( $args['default'] ) ? "'" . $args['default'] . "'" : "''";
									}
								} elseif ( $args['type'] == 'multiselect' ) {
									$type    = 'array';
									$default = isset( $args['default'] ) ? "'" . $args['default'] . "'" : "''";
								} else {
									$type    = 'string';
									$default = isset( $args['default'] ) ? "'" . $args['default'] . "'" : "''";
								}
								echo $key . " : {";
								echo "type : '$type',";
								echo "default : $default,";
								echo "},";
							}

							echo "content : {type : 'string',default: 'Please select the attributes in the block settings'},";

							echo "},";

						}

						?>

						// The "edit" property must be a valid function.
						edit: function (props) {

							var content = props.attributes.content;

							function onChangeContent() {

								if (!is_fetching && prev_attributes[props.id] != props.attributes) {

									//console.log(props);

									is_fetching = true;
									var data = {
										'action': 'super_duper_output_shortcode',
										'shortcode': '<?php echo $this->options['base_id'];?>',
										'attributes': props.attributes,
										'post_id': <?php global $post; if ( isset( $post->ID ) ) {
										echo $post->ID;
									}?>,
										'_ajax_nonce': '<?php echo wp_create_nonce( 'super_duper_output_shortcode' );?>'
									};

									jQuery.post(ajaxurl, data, function (response) {
										return response;
									}).then(function (env) {

										// if the content is empty then we place some placeholder text
										if (env == '') {
											env = "<div style='background:#0185ba33;padding: 10px;'>" + "<?php _e( 'Placeholder for: ' );?>" + props.name + "</div>";
										}

										props.setAttributes({content: env});
										is_fetching = false;
										prev_attributes[props.id] = props.attributes;
									});


								}

								return props.attributes.content;

							}

							return [

								el(wp.editor.BlockControls, {key: 'controls'},

									<?php if($show_alignment){?>
									el(
										wp.editor.AlignmentToolbar,
										{
											value: props.attributes.alignment,
											onChange: function (alignment) {
												props.setAttributes({alignment: alignment})
											}
										}
									)
									<?php }?>

								),

								el(wp.editor.InspectorControls, {key: 'inspector'},

									<?php

									if(! empty( $this->arguments )){

									if ( $show_advanced ) {
									?>
									el(
										wp.components.ToggleControl,
										{
											label: 'Show Advanced Settings?',
											checked: props.attributes.show_advanced,
											onChange: function (show_advanced) {
												props.setAttributes({show_advanced: !props.attributes.show_advanced})
											}
										}
									),
									<?php

									}

									foreach($this->arguments as $key => $args){
									$custom_attributes = ! empty( $args['custom_attributes'] ) ? $this->array_to_attributes( $args['custom_attributes'] ) : '';
									$options = '';
									$extra = '';
									$require = '';
									$onchange = "props.setAttributes({ $key: $key } )";
									$value = "props.attributes.$key";
									$text_type = array( 'text', 'password', 'number', 'email', 'tel', 'url', 'color' );
									if ( in_array( $args['type'], $text_type ) ) {
										$type = 'TextControl';
									} elseif ( $args['type'] == 'checkbox' ) {
										$type = 'CheckboxControl';
										$extra .= "checked: props.attributes.$key,";
										$onchange = "props.setAttributes({ $key: ! props.attributes.$key } )";
									} elseif ( $args['type'] == 'select' || $args['type'] == 'multiselect' ) {
										$type = 'SelectControl';
										if ( ! empty( $args['options'] ) ) {
											$options .= "options  : [";
											foreach ( $args['options'] as $option_val => $option_label ) {
												$options .= "{ value : '" . esc_attr( $option_val ) . "',     label : '" . esc_attr( $option_label ) . "'     },";
											}
											$options .= "],";
										}
										if ( isset( $args['multiple'] ) && $args['multiple'] ) { //@todo multiselect does not work at the moment: https://github.com/WordPress/gutenberg/issues/5550
											$extra .= ' multiple: true, ';
											//$onchange = "props.setAttributes({ $key: ['edit'] } )";
											//$value = "['edit', 'delete']";
										}
									} elseif ( $args['type'] == 'alignment' ) {
										$type = 'AlignmentToolbar'; // @todo this does not seem to work but cant find a example
									} else {
										continue;// if we have not implemented the control then don't break the JS.
									}

									// add show only if advanced
									if ( ! empty( $args['advanced'] ) ) {
										echo "props.attributes.show_advanced && ";
									}
									// add setting require if defined
									if ( ! empty( $args['element_require'] ) ) {
										echo $this->block_props_replace( $args['element_require'], true ) . " && ";
									}
									?>
									el(
										wp.components.<?php echo esc_attr( $type );?>,
										{
											label: '<?php echo esc_attr( $args['title'] );?>',
											help: '<?php if ( isset( $args['desc'] ) ) {
												echo esc_attr( $args['desc'] );
											}?>',
											value: <?php echo $value;?>,
											<?php if ( $type == 'TextControl' && $args['type'] != 'text' ) {
											echo "type: '" . esc_attr( $args['type'] ) . "',";
										}?>
											<?php if ( ! empty( $args['placeholder'] ) ) {
											echo "placeholder: '" . esc_attr( $args['placeholder'] ) . "',";
										}?>
											<?php echo $options;?>
											<?php echo $extra;?>
											<?php echo $custom_attributes;?>
											onChange: function ( <?php echo $key;?> ) {
												<?php echo $onchange;?>
											}
										}
									),
									<?php
									}
									}
									?>

								),

								<?php
								// If the user sets block-output array then build it
								if ( ! empty( $this->options['block-output'] ) ) {
								$this->block_element( $this->options['block-output'] );
							}else{
								// if no block-output is set then we try and get the shortcode html output via ajax.
								?>
								el('div', {
									dangerouslySetInnerHTML: {__html: onChangeContent()},
									className: props.className,
									style: {'min-height': '30px'}
								})
								<?php
								}
								?>
							]; // end return
						},

						// The "save" property must be specified and must be a valid function.
						save: function (props) {

							//console.log(props);


							var attr = props.attributes;
							var align = '';

							// build the shortcode.
							var content = "[<?php echo $this->options['base_id'];?>";
							<?php

							if(! empty( $this->arguments )){
							foreach($this->arguments as $key => $args){
							?>
							if (attr.hasOwnProperty("<?php echo esc_attr( $key );?>")) {
								content += " <?php echo esc_attr( $key );?>='" + attr.<?php echo esc_attr( $key );?>+ "' ";
							}
							<?php
							}
							}

							?>
							content += "]";


							// @todo should we add inline style here or just css classes?
							if (attr.alignment) {
								if (attr.alignment == 'left') {
									align = 'alignleft';
								}
								if (attr.alignment == 'center') {
									align = 'aligncenter';
								}
								if (attr.alignment == 'right') {
									align = 'alignright';
								}
							}

							//console.log(content);
							var block_wrap = 'div';
							if (attr.hasOwnProperty("block_wrap")) {
								block_wrap = attr.block_wrap;
							}
							return el(block_wrap, {dangerouslySetInnerHTML: {__html: content}, className: align});

						}
					});
				})();
			</script>
			<?php
			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */

			return str_replace( array(
				'<script>',
				'</script>'
			), '', $output );
		}

		/**
		 * Convert an array of attributes to block string.
		 *
		 * @todo there is prob a faster way to do this, also we could add some validation here.
		 *
		 * @param $custom_attributes
		 *
		 * @return string
		 */
		public function array_to_attributes( $custom_attributes, $html = false ) {
			$attributes = '';
			if ( ! empty( $custom_attributes ) ) {

				if ( $html ) {
					foreach ( $custom_attributes as $key => $val ) {
						$attributes .= " $key='$val' ";
					}
				} else {
					foreach ( $custom_attributes as $key => $val ) {
						$attributes .= "'$key': '$val',";
					}
				}
			}

			return $attributes;
		}

		/**
		 * A self looping function to create the output for JS block elements.
		 *
		 * This is what is output in the WP Editor visual view.
		 *
		 * @param $args
		 */
		public function block_element( $args ) {


			if ( ! empty( $args ) ) {
				foreach ( $args as $element => $new_args ) {

					if ( is_array( $new_args ) ) { // its an element


						if ( isset( $new_args['element'] ) ) {

							if ( isset( $new_args['element_require'] ) ) {
								echo str_replace( array(
										"'+",
										"+'"
									), '', $this->block_props_replace( $new_args['element_require'] ) ) . " &&  ";
								unset( $new_args['element_require'] );
							}

							echo "\n el( '" . $new_args['element'] . "', {";

							// get the attributes
							foreach ( $new_args as $new_key => $new_value ) {


								if ( $new_key == 'element' || $new_key == 'content' || $new_key == 'element_require' || $new_key == 'element_repeat' || is_array( $new_value ) ) {
									// do nothing
								} else {
									echo $this->block_element( array( $new_key => $new_value ) );
								}
							}

							echo "},";// end attributes

							// get the content
							$first_item = 0;
							foreach ( $new_args as $new_key => $new_value ) {
								if ( $new_key === 'content' || is_array( $new_value ) ) {

									if ( $new_key === 'content' ) {
										echo "'" . $this->block_props_replace( $new_value ) . "'";
									}

									if ( is_array( $new_value ) ) {

										if ( isset( $new_value['element_require'] ) ) {
											echo str_replace( array(
													"'+",
													"+'"
												), '', $this->block_props_replace( $new_value['element_require'] ) ) . " &&  ";
											unset( $new_value['element_require'] );
										}

										if ( isset( $new_value['element_repeat'] ) ) {
											$x = 1;
											while ( $x <= absint( $new_value['element_repeat'] ) ) {
												$this->block_element( array( '' => $new_value ) );
												$x ++;
											}
										} else {
											$this->block_element( array( '' => $new_value ) );
										}
									}
									$first_item ++;
								}
							}

							echo ")";// end content

							echo ", \n";

						}
					} else {

						if ( substr( $element, 0, 3 ) === "if_" ) {
							echo str_replace( "if_", "", $element ) . ": " . $this->block_props_replace( $new_args, true ) . ",";
						} elseif ( $element == 'style' ) {
							echo $element . ": " . $this->block_props_replace( $new_args ) . ",";
						} else {
							echo $element . ": '" . $this->block_props_replace( $new_args ) . "',";
						}

					}
				}
			}
		}

		/**
		 * Replace block attributes placeholders with the proper naming.
		 *
		 * @param $string
		 *
		 * @return mixed
		 */
		public function block_props_replace( $string, $no_wrap = false ) {

			if ( $no_wrap ) {
				$string = str_replace( array( "[%", "%]" ), array( "props.attributes.", "" ), $string );
			} else {
				$string = str_replace( array( "[%", "%]" ), array( "'+props.attributes.", "+'" ), $string );
			}

			return $string;
		}

		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {

			// get the filtered values
			$argument_values = $this->argument_values( $instance );
			$argument_values = $this->string_to_bool( $argument_values );
			$output          = $this->output( $argument_values, $args );

			if ( $output ) {
				// Before widget
				$before_widget = $args['before_widget'];
				$before_widget = apply_filters( 'wp_super_duper_before_widget', $before_widget, $args, $instance, $this );
				$before_widget = apply_filters( 'wp_super_duper_before_widget_' . $this->base_id, $before_widget, $args, $instance, $this );

				// After widget
				$after_widget = $args['after_widget'];
				$after_widget = apply_filters( 'wp_super_duper_after_widget', $after_widget, $args, $instance, $this );
				$after_widget = apply_filters( 'wp_super_duper_after_widget_' . $this->base_id, $after_widget, $args, $instance, $this );

				echo $before_widget;
				// elementor strips the widget wrapping div so we check for and add it back if needed
				if ( $this->is_elementor_widget_output() ) {
					echo ! empty( $this->options['widget_ops']['classname'] ) ? "<span class='" . esc_attr( $this->options['widget_ops']['classname'] ) . "'>" : '';
				}
				echo $this->output_title( $args, $instance );
				echo $output;
				if ( $this->is_elementor_widget_output() ) {
					echo ! empty( $this->options['widget_ops']['classname'] ) ? "</span>" : '';
				}
				echo $after_widget;
			}elseif($this->is_preview() && $output==''){// if preview show a placeholder if empty
				$output = $this->preview_placeholder_text("{{".$this->base_id."}}");
			}
		}

		/**
		 * Tests if the current output is inside a elementor container.
		 *
		 * @since 1.0.4
		 * @return bool
		 */
		public function is_elementor_widget_output() {
			$result = false;
			if ( defined( 'ELEMENTOR_VERSION' ) && isset( $this->number ) && $this->number == 'REPLACE_TO_ID' ) {
				$result = true;
			}

			return $result;
		}

		/**
		 * Tests if the current output is inside a elementor preview.
		 *
		 * @since 1.0.4
		 * @return bool
		 */
		public function is_elementor_preview() {
			$result = false;
			if ( isset( $_REQUEST['elementor-preview'] ) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) ) {
				$result = true;
			}

			return $result;
		}

		/**
		 * Tests if the current output is inside a Divi preview.
		 *
		 * @since 1.0.6
		 * @return bool
		 */
		public function is_divi_preview() {
			$result = false;
			if ( isset( $_REQUEST['et_fb'] ) || isset($_REQUEST['et_pb_preview']) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) ) {
				$result = true;
			}

			return $result;
		}

		/**
		 * General function to check if we are in a preview situation.
		 *
		 * @since 1.0.6
		 * @return bool
		 */
		public function is_preview(){
			$preview = false;
			if($this->is_divi_preview()){
				$preview = true;
			}elseif($this->is_elementor_preview()){
				$preview = true;
			}
			
			return $preview;
		}

		/**
		 * Output the super title.
		 *
		 * @param $args
		 * @param array $instance
		 *
		 * @return string
		 */
		public function output_title( $args, $instance = array() ) {
			$output = '';
			if ( ! empty( $instance['title'] ) ) {
				/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
				$title  = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
				$output = $args['before_title'] . $title . $args['after_title'];
			}

			return $output;
		}

		/**
		 * Outputs the options form inputs for the widget.
		 *
		 * @param array $instance The widget options.
		 */
		public function form( $instance ) {

			// set widget instance
			$this->instance = $instance;

			// set it as a SD widget
			echo $this->widget_advanced_toggle();

			echo "<p>" . esc_attr( $this->options['widget_ops']['description'] ) . "</p>";
			$arguments = $this->get_arguments();

			if ( is_array( $arguments ) ) {
				foreach ( $arguments as $key => $args ) {
					$this->widget_inputs( $args, $instance );
				}
			}
		}

		/**
		 * Get the hidden input that when added makes the advanced button show on widget settings.
		 *
		 * @return string
		 */
		public function widget_advanced_toggle() {

			$output = '';
			if ( $this->block_show_advanced() ) {
				$val = 1;
			} else {
				$val = 0;
			}

			$output .= "<input type='hidden'  class='sd-show-advanced' value='$val' />";

			return $output;
		}

		/**
		 * Convert require element.
		 *
		 * @since 1.0.0
		 *
		 * @param string $input Input element.
		 *
		 * @return string $output
		 */
		public function convert_element_require( $input ) {

			$input = str_replace( "'", '"', $input );// we only want double quotes

			$output = esc_attr( str_replace( array( "[%", "%]" ), array(
				"jQuery(form).find('[data-argument=\"",
				"\"]').find('input,select').val()"
			), $input ) );

			return $output;
		}

		/**
		 * Builds the inputs for the widget options.
		 *
		 * @param $args
		 * @param $instance
		 */
		public function widget_inputs( $args, $instance ) {

			$class             = "";
			$element_require   = "";
			$custom_attributes = "";

			// get value
			if ( isset( $instance[ $args['name'] ] ) ) {
				$value = $instance[ $args['name'] ];
			} elseif ( ! isset( $instance[ $args['name'] ] ) && ! empty( $args['default'] ) ) {
				$value = is_array( $args['default'] ) ? array_map( "esc_html", $args['default'] ) : esc_html( $args['default'] );
			} else {
				$value = '';
			}

			// get placeholder
			if ( ! empty( $args['placeholder'] ) ) {
				$placeholder = "placeholder='" . esc_html( $args['placeholder'] ) . "'";
			} else {
				$placeholder = '';
			}

			// get if advanced
			if ( isset( $args['advanced'] ) && $args['advanced'] ) {
				$class .= " sd-advanced-setting ";
			}

			// element_require
			if ( isset( $args['element_require'] ) && $args['element_require'] ) {
				$element_require = $args['element_require'];
			}

			// custom_attributes
			if ( isset( $args['custom_attributes'] ) && $args['custom_attributes'] ) {
				$custom_attributes = $this->array_to_attributes( $args['custom_attributes'], true );
			}

			// before wrapper
			?>
			<p class="sd-argument <?php echo esc_attr( $class ); ?>"
			   data-argument='<?php echo esc_attr( $args['name'] ); ?>'
			   data-element_require='<?php if ( $element_require ) {
				   echo $this->convert_element_require( $element_require );
			   } ?>'
			>
				<?php

				switch ( $args['type'] ) {
					//array('text','password','number','email','tel','url','color')
					case "text":
					case "password":
					case "number":
					case "email":
					case "tel":
					case "url":
					case "color":
						?>
						<label
							for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo esc_attr( $args['title'] ); ?><?php echo $this->widget_field_desc( $args ); ?></label>
						<input <?php echo $placeholder; ?> class="widefat"
							<?php echo $custom_attributes; ?>
							                               id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
							                               name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>"
							                               type="<?php echo esc_attr( $args['type'] ); ?>"
							                               value="<?php echo esc_attr( $value ); ?>">
						<?php

						break;
					case "select":
						$multiple = isset( $args['multiple'] ) && $args['multiple'] ? true : false;
						if ( $multiple ) {
							if ( empty( $value ) ) {
								$value = array();
							}
						}
						?>
						<label
							for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo esc_attr( $args['title'] ); ?><?php echo $this->widget_field_desc( $args ); ?></label>
						<select <?php echo $placeholder; ?> class="widefat"
							<?php echo $custom_attributes; ?>
							                                id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
							                                name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) );
							                                if ( $multiple ) {
								                                echo "[]";
							                                } ?>"
							<?php if ( $multiple ) {
								echo "multiple";
							} //@todo not implemented yet due to gutenberg not supporting it
							?>
						>
							<?php

							if ( ! empty( $args['options'] ) ) {
								foreach ( $args['options'] as $val => $label ) {
									if ( $multiple ) {
										$selected = in_array( $val, $value ) ? 'selected="selected"' : '';
									} else {
										$selected = selected( $value, $val, false );
									}
									echo "<option value='$val' " . $selected . ">$label</option>";
								}
							}
							?>
						</select>
						<?php
						break;
					case "checkbox":
						?>
						<input <?php echo $placeholder; ?>
							<?php checked( 1, $value, true ) ?>
							<?php echo $custom_attributes; ?>
							class="widefat" id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
							name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>" type="checkbox"
							value="1">
						<label
							for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo esc_attr( $args['title'] ); ?><?php echo $this->widget_field_desc( $args ); ?></label>
						<?php
						break;
					case "hidden":
						?>
						<input id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
						       name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>" type="hidden"
						       value="<?php echo esc_attr( $value ); ?>">
						<?php
						break;
					default:
						echo "No input type found!"; // @todo we need to add more input types.
				}

				// after wrapper
				?>
			</p>
			<?php

		}

		/**
		 * Get the widget input description html.
		 *
		 * @param $args
		 *
		 * @return string
		 * @todo, need to make its own tooltip script
		 */
		public function widget_field_desc( $args ) {

			$description = '';
			if ( isset( $args['desc'] ) && $args['desc'] ) {
				if ( isset( $args['desc_tip'] ) && $args['desc_tip'] ) {
					$description = $this->desc_tip( $args['desc'] );
				} else {
					$description = '<span class="description">' . wp_kses_post( $args['desc'] ) . '</span>';
				}
			}

			return $description;
		}

		/**
		 * Get the tool tip html.
		 *
		 * @param $tip
		 * @param bool $allow_html
		 *
		 * @return string
		 */
		function desc_tip( $tip, $allow_html = false ) {
			if ( $allow_html ) {
				$tip = $this->sanitize_tooltip( $tip );
			} else {
				$tip = esc_attr( $tip );
			}

			return '<span class="gd-help-tip dashicons dashicons-editor-help" title="' . $tip . '"></span>';
		}

		/**
		 * Sanitize a string destined to be a tooltip.
		 *
		 * @param string $var
		 *
		 * @return string
		 */
		public function sanitize_tooltip( $var ) {
			return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'small'  => array(),
				'span'   => array(),
				'ul'     => array(),
				'li'     => array(),
				'ol'     => array(),
				'p'      => array(),
			) ) );
		}

		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 *
		 * @return array
		 * @todo we should add some sanitation here.
		 */
		public function update( $new_instance, $old_instance ) {

			//save the widget
			$instance = array_merge( (array) $old_instance, (array) $new_instance );

			// set widget instance
			$this->instance = $instance;

			if ( empty( $this->arguments ) ) {
				$this->get_arguments();
			}

			// check for checkboxes
			if ( ! empty( $this->arguments ) ) {
				foreach ( $this->arguments as $argument ) {
					if ( isset( $argument['type'] ) && $argument['type'] == 'checkbox' && ! isset( $new_instance[ $argument['name'] ] ) ) {
						$instance[ $argument['name'] ] = '0';
					}
				}
			}

			return $instance;
		}

		/**
		 * Checks if the current call is a ajax call to get the block content.
		 *
		 * This can be used in your widget to return different content as the block content.
		 *
		 * @since 1.0.3
		 * @return bool
		 */
		public function is_block_content_call() {
			$result = false;
			if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'super_duper_output_shortcode' ) {
				$result = true;
			}

			return $result;
		}

	}

}