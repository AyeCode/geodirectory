<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// load via plugin just now @todo update this before release
if(!class_exists('WP_Super_Duper') && file_exists(dirname( __FILE__ ) . "/../../../wp-super-duper/wp-super-duper.php")) {
	include_once( dirname( __FILE__ ) . "/../../../wp-super-duper/wp-super-duper.php" );
	return;
}


if(!class_exists('WP_Super_Duper')) {


	/**
	 * A Class to be able to create a Widget, Shortcode or Block to be able to output content for WordPress.
	 *
	 * Should not be called direct but extended instead.
	 *
	 * Class WP_Super_Duper
	 * @ver 0.0.1
	 */
	class WP_Super_Duper extends WP_Widget {


		public $block_code;
		public $options;
		public $base_id;
		public $arguments;
		private $class_name;

		/**
		 * Take the array options and use them to build.
		 */
		public function __construct( $options ) {

			//print_r($options);exit;

			$this->base_id = $options['base_id'];
			// lets filter the options before we do anything
			$options       = apply_filters( "wp_super_duper_options_{$this->base_id}", $options );
			$this->options = $options;

			$this->base_id   = $options['base_id'];
			$this->arguments = $options['arguments'];

			//$base_id, $name, $widget_ops, $class_name = ''


			// init parent
			parent::__construct( $options['base_id'], $options['name'], $options['widget_ops'] );


			if ( isset( $options['class_name'] ) ) {
				// register widget
				$this->class_name = $options['class_name'];
				$this->register_widget();

				// register shortcode
				$this->register_shortcode();

				// register block
				//$this->register_block();
				add_action( 'admin_enqueue_scripts', array( $this, 'register_block' ) );
			}

		}

		/**
		 * Register the parent widget class
		 */
		public function register_widget() {
//		add_action( 'widgets_init', function () {
//			register_widget( $this->class_name );
//		} );
		}

		/**
		 * Register the parent shortcode
		 */
		public function register_shortcode() {
			add_shortcode( $this->base_id, array( $this, 'shortcode_output' ) );
		}

		/**
		 * Output the shortcode.
		 *
		 * @param array $args
		 * @param string $content
		 */
		public function shortcode_output( $args = array(), $content = '' ) {
			$args = self::argument_values( $args );

			return $this->output( $args, array(), $content );
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

			if ( ! empty( $this->arguments ) ) {
				foreach ( $this->arguments as $key => $args ) {
					$argument_values[ $key ] = isset( $instance[ $key ] ) ? $instance[ $key ] : '';
					if ( $argument_values[ $key ] == '' && isset( $args['default'] ) ) {
						$argument_values[ $key ] = $args['default'];
					}
				}
			}

			return $argument_values;
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
		 * Add the dyanmic block code inline when the wp-block in enqueued.
		 */
		public function register_block() {
			wp_add_inline_script( 'wp-blocks', $this->block() );
		}


		/**
		 * Output the JS for building the dynamic Guntenberg block.
		 *
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

						if ( ! empty( $this->arguments ) ) {
							echo "attributes : {";
							foreach ( $this->arguments as $key => $args ) {
								if ( $args['type'] == 'text' || $args['type'] == 'select' ) {
									$type    = 'string';
									$default = isset( $args['default'] ) ? "'" . $args['default'] . "'" : '';
								} elseif ( $args['type'] == 'checkbox' ) {
									$type    = 'boolean';
									$default = isset( $args['default'] ) ? $args['default'] : '';
								} else {
									$type    = 'string';
									$default = isset( $args['default'] ) && "'" . $args['default'] . "'" ? 'true' : 'false';
								}
								echo $key . " : {";
								echo "type : '$type',";
								echo "default : $default,";
								echo "},";
							}
							echo "},";

						}

						?>

						// The "edit" property must be a valid function.
						edit: function (props) {

							console.log(props);

							return [

								!!props.focus && el(blocks.BlockControls, {key: 'controls'},
									// @todo implement later if needed
								),

								!!props.focus && el(blocks.InspectorControls, {key: 'inspector'},

									<?php

									if(! empty( $this->arguments )){
									foreach($this->arguments as $key => $args){
									$options = '';
									if ( $args['type'] == 'text' ) {
										$type = 'TextControl';
									} elseif ( $args['type'] == 'checkbox' ) {
										$type = 'CheckboxControl';
									} elseif ( $args['type'] == 'select' ) {
										$type = 'SelectControl';
										if ( ! empty( $args['options'] ) ) {
											$options .= "options  : [";
											foreach ( $args['options'] as $option_val => $option_label ) {
												$options .= "{ value : '" . esc_attr( $option_val ) . "',     label : '" . esc_attr( $option_label ) . "'     },";
											}
											$options .= "],";
										}
									} elseif ( $args['type'] == 'checkbox' ) {
										$type = 'CheckboxControl';
									} else {
										continue;// if we have not implemented the control then don't break the JS.
									}
									?>
									el(
										blocks.InspectorControls.<?php echo esc_attr( $type );?>,
										{
											label: '<?php echo esc_attr( $args['title'] );?>',
											help: '<?php echo esc_attr( $args['desc'] );?>',
											value: props.attributes.<?php echo $key;?>,
											<?php echo $options;?>
											onChange: function ( <?php echo $key;?> ) {
												props.setAttributes({ <?php echo $key;?>: <?php echo $key;?> } )
											}
										}
									),
									<?php

									}

									}




									//$xxx = do_shortcode( "[gd_main_map width='100%' height='425px' maptype='ROADMAP' zoom='0' ]" );
									?>

								),

								//el( 'div', { dangerouslySetInnerHTML: { __html: <?php // echo json_encode($xxx);?>} } )

								// @todo implement the output
//							el( 'img', {
//								//src: 'http://localhost/wp-content/uploads/2018/01/a15-11.jpg',
//								src: 'http://localhost/wp-content/plugins/geodirectory-v2/assets/images/block-placeholder-map.png', // @todo we need to reference this locally
//								alt: 'xxx',
//								width: props.attributes.width,
//								height: props.attributes.height,
//							} )

								<?php
								if ( ! empty( $this->options['block-output'] ) ) {
									$this->block_element( $this->options['block-output'] );
								}
								?>

							]; // end return


							// Creates a <p class='wp-block-gb-basic-01'></p>.
//						return el(
//							'p', // Tag type.
//							{className: props.className}, // The class="wp-block-gb-basic-01" : The class name is generated using the block's name prefixed with wp-block-, replacing the / namespace separator with a single -.
//							'Hello World! — from the editor (01 Basic Block).' // Content inside the tag.
//						);
						},

						// The "save" property must be specified and must be a valid function.
						save: function (props) {

							var attr = props.attributes;
							var content = "[<?php echo $this->options['base_id'];?>";
							<?php

							if(! empty( $this->arguments )){
							foreach($this->arguments as $key => $args){
							?>
							if (attr.<?php echo esc_attr( $key );?>) {
								content += " <?php echo esc_attr( $key );?>='" + attr.<?php echo esc_attr( $key );?>+ "' ";
							}
							<?php
							}
							}

							?>
							content += "]";


							console.log(content);
							return el('div', {dangerouslySetInnerHTML: {__html: content}});

						}
					});
				})();
			</script>
			<?php
			$output = ob_get_clean();

			/*
			 * We only add the <script> tags for code higlighting, so we strip them from the output.
			 */

			return str_replace( array(
				'<script>',
				'</script>'
			), '', $output );
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

					// its an element
					if ( substr( $element, 0, 9 ) === "element::" ) {

						$content = '';
						echo "el( '" . str_replace( "element::", "", $element ) . "', {";
						if ( isset( $new_args['content'] ) ) {
							$content = $new_args['content'];
							unset($new_args['content']);
						}
						echo $this->block_element( $new_args );

						// check for content
						if ($content) {
							echo "},";
							echo "'".$this->block_props_replace($content)."'";
							echo "),";

							//echo esc_attr( $new_args['content'] );
						}else{
							echo "}),";
						}

					} // its not an element or property
					else {
						echo $element . ": '".$this->block_props_replace($new_args)."',";
					}

				}
			}
		}

		public function block_props_replace($string){

			$string = str_replace( array("[%","%]"), array("'+props.attributes.","+'"), $string );
			return $string;
		}

		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			// outputs the content of the widget

			// get the filtered values
			$argument_values = $this->argument_values( $instance );

			echo $args['before_widget'];
			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			}
			echo $this->output( $argument_values, $args );
			echo $args['after_widget'];

		}

		/**
		 * Outputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			if ( is_array( $this->arguments ) ) {
				foreach ( $this->arguments as $args ) {
					$this->widget_inputs( $args, $instance );
				}
			}
		}

		/**
		 * Builds the inputs for the widget options.
		 *
		 * @param $args
		 * @param $instance
		 */
		public function widget_inputs( $args, $instance ) {


			if ( isset( $instance[ $args['name'] ] ) ) {
				$value = $instance[ $args['name'] ];
			} elseif ( ! empty( $args['default'] ) ) {
				$value = esc_html( $args['default'] );
			} else {
				$value = '';
			}

			if ( ! empty( $args['placeholder'] ) ) {
				$placeholder = "placeholder='" . esc_html( $args['placeholder'] ) . "'";
			} else {
				$placeholder = '';
			}

			switch ( $args['type'] ) {
				case "text":
					?>
					<p>
						<label
							for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo esc_attr( $args['title'] ); ?><?php echo $this->widget_field_desc( $args ); ?></label>
						<input <?php echo $placeholder; ?> class="widefat"
						                                   id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
						                                   name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>"
						                                   type="text" value="<?php echo esc_attr( $value ); ?>">
					</p>
					<?php

					break;
				case "select":
					?>
					<p>
						<label
							for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo esc_attr( $args['title'] ); ?><?php echo $this->widget_field_desc( $args ); ?></label>
						<select <?php echo $placeholder; ?> class="widefat"
						                                    id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
						                                    name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>">
							<?php
							if ( ! empty( $args['options'] ) ) {
								foreach ( $args['options'] as $val => $label ) {
									echo "<option value='$val' " . selected( $value, $val ) . ">$label</option>";
								}
							}
							?>
						</select>
					</p>
					<?php
					break;
				case "checkbox":
					?>
					<p>
						<input <?php echo $placeholder; ?>
							<?php checked( 1, $value, true ) ?>
							class="widefat" id="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"
							name="<?php echo esc_attr( $this->get_field_name( $args['name'] ) ); ?>" type="checkbox"
							value="1">
						<label
							for="<?php echo esc_attr( $this->get_field_id( $args['name'] ) ); ?>"><?php echo esc_attr( $args['title'] ); ?><?php echo $this->widget_field_desc( $args ); ?></label>
					</p>
					<?php
					break;
				default:
					echo "No input type found!";
			}

		}

		//@todo, need to make its own tooltip script
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


		function desc_tip( $tip, $allow_html = false ) {
			if ( $allow_html ) {
				$tip = geodir_sanitize_tooltip( $tip );
			} else {
				$tip = esc_attr( $tip );
			}

			return '<span class="gd-help-tip dashicons dashicons-editor-help" title="' . $tip . '"></span>';
		}

		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 *
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {
			//save the widget
			$instance = array_merge( (array) $old_instance, (array) $new_instance );

			return $instance;
		}

	}

}
