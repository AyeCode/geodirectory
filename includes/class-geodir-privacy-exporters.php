<?php
/**
 * Personal data exporters.
 *
 * @since 1.2.26
 * @package GeoDirectory
 */

defined( 'ABSPATH' ) || exit;

/**
 * GeoDir_Privacy_Exporters Class.
 */
class GeoDir_Privacy_Exporters {

	/**
	 * Finds and exports data which could be used to identify a person from GeoDirectory data associated with an email address.
	 *
	 * Posts are exported in blocks of 10 to avoid timeouts.
	 *
	 * @since 1.6.26
	 * @param string $email_address The user email address.
	 * @param int    $page  Page.
	 * @return array An array of personal data in name value pairs
	 */
	public static function post_data_exporter( $email_address, $page ) {
		$post_type 		= GeoDir_Privacy::exporter_post_type();

		$done           = false;
		$page           = (int) $page;
		$data_to_export = array();

		$posts 			= self::posts_by_author( $email_address, $post_type, $page );

		if ( 0 < count( $posts ) ) {
			foreach ( $posts as $post_ID ) {
				$gd_post = geodir_get_post_info( $post_ID );
				if ( empty( $gd_post ) ) {
					continue;
				}

				$data_to_export[] = array(
					'group_id'          => 'geodirectory-post-' . $post_type,
					'group_label'       => wp_sprintf( __( 'GeoDirectory: %s', 'geodirectory' ), geodir_get_post_type_plural_label( $post_type, false, true ) ),
					'group_description' => wp_sprintf( __( 'User&#8217;s %s data for GeoDirectory.', 'geodirectory' ), geodir_get_post_type_plural_label( $post_type, false, true ) ),
					'item_id'           => 'post-' . $post_ID,
					'data'              => self::get_post_personal_data( $gd_post ),
				);
			}
			$done = 10 > count( $posts );
		} else {
			$done = true;
		}

		return array(
			'data' => $data_to_export,
			'done' => $done,
		);
	}

	/**
	 * Get personal data (key/value pairs) for an post object.
	 *
	 * @since 1.6.26
	 * @param WP_Post $gd_post The post object.
	 * @return array
	 */
	protected static function get_post_personal_data( $gd_post ) {
		$personal_data = array();
		$personal_data[] = array(
			'name'  => __( 'Post ID', 'geodirectory' ),
			'value' => $gd_post->ID,
		);
		$personal_data[] = array(
			'name'  => __( 'Post Date', 'geodirectory' ),
			'value' => $gd_post->post_date,
		);
		if ( ! empty( $gd_post->post_modified ) && $gd_post->post_modified != '0000-00-00 00:00:00' ) {
			$personal_data[] = array(
				'name'  => __( 'Post Modified Date', 'geodirectory' ),
				'value' => $gd_post->post_modified,
			);
		}
		$personal_data[] = array(
			'name'  => __( 'Post Status', 'geodirectory' ),
			'value' => geodir_get_post_status_name( $gd_post->post_status ),
		);
		if ( ! empty( $gd_post->submit_ip ) ) {
			$personal_data[] = array(
				'name'  => __( 'Submit IP', 'geodirectory' ),
				'value' => $gd_post->submit_ip,
			);
		}
		$personal_data[] = array(
			'name'  => __( 'Post URL', 'geodirectory' ),
			'value' => get_permalink( $gd_post->ID ),
		);

		/**
		 * Allow extensions to register their own personal data for this post for the export.
		 *
		 * @since 1.6.26
		 * @param array    $personal_data Array of name value pairs to expose in the export.
		 * @param object   $gd_post The post object.
		 */
		return apply_filters( 'geodir_privacy_export_post_personal_data', $personal_data, $gd_post );
	}

	/**
	 * Export post ratings.
	 *
	 * @since 1.6.26
	 * @param array    $personal_data Array of name value pairs to expose in the export.
	 * @param WP_Post $gd_post The post object.
	 */
	public static function export_post_rating( $personal_data, $gd_post ) {
		// Post Rating
		$post_rating = geodir_get_post_rating( $gd_post->ID );
		if ( $post_rating > 0 ) {
			$post_rating = ( is_float( $post_rating) || ( strpos( $post_rating, ".", 1 ) == 1 && strlen( $post_rating ) > 3 ) ) ? number_format( $post_rating, 1, '.', '' ) : $post_rating;
			$personal_data[] = array(
				'name'  => __( 'Post Rating', 'geodirectory' ),
				'value' => $post_rating . ' / 5',
			);
		}

		// Post Reviews
		$post_reviews = geodir_get_review_count_total( $gd_post->ID );
		if ( $post_reviews > 0 ) {
			$personal_data[] = array(
				'name'  => __( 'Post Reviews', 'geodirectory' ),
				'value' => $post_reviews,
			);
		}

		return $personal_data;
	}

	/**
	 * Export post custom fields.
	 *
	 * @since 1.6.26
	 * @param array    $personal_data Array of name value pairs to expose in the export.
	 * @param WP_Post $gd_post The post object.
	 */
	public static function export_post_custom_fields( $personal_data, $gd_post ) {
		$package_id = ! empty( $gd_post->package_id ) ? $gd_post->package_id : '';
		$custom_fields = geodir_post_custom_fields( $package_id, 'all', $gd_post->post_type );

		foreach ( $custom_fields as $key => $field ) {
			if ( empty( $field['name'] ) || $field['field_type'] == 'fieldset' ) {
				continue;
			}
			$field 	= stripslashes_deep( $field );
			$extra_fields = ! empty( $field['extra_fields'] ) ? maybe_unserialize( $field['extra_fields'] ) : array();
			$option_values = ! empty( $field['option_values'] ) ? maybe_unserialize( $field['option_values'] ) : array();
			
			$name = ! empty( $field['frontend_title'] ) ? $field['frontend_title'] : $field['label'];
			$value = isset( $gd_post->{$field['name']} ) ? $gd_post->{$field['name']} : '';

			switch ( $field['field_type'] ) {
				case 'address':
					if ( ! empty( $gd_post->street ) ) {
						$personal_data[] = array(
							'name'  => __( 'Post Address', 'geodirectory' ),
							'value' => $gd_post->street,
						);
					}
					if ( ! empty( $gd_post->city ) ) {
						$personal_data[] = array(
							'name'  => __( 'Post City', 'geodirectory' ),
							'value' => $gd_post->city,
						);
					}
					if ( ! empty( $gd_post->region ) ) {
						$personal_data[] = array(
							'name'  => __( 'Post Region', 'geodirectory' ),
							'value' => $gd_post->region,
						);
					}
					if ( ! empty( $gd_post->country ) ) {
						$personal_data[] = array(
							'name'  => __( 'Post Country', 'geodirectory' ),
							'value' => $gd_post->country,
						);
					}
					if ( ! empty( $gd_post->zip ) ) {
						$personal_data[] = array(
							'name'  => __( 'Post Zip', 'geodirectory' ),
							'value' => $gd_post->zip,
						);
					}
					if ( ! empty( $gd_post->latitude ) ) {
						$personal_data[] = array(
							'name'  => __( 'Post Latitude', 'geodirectory' ),
							'value' => $gd_post->latitude,
						);
					}
					if ( ! empty( $gd_post->longitude ) ) {
						$personal_data[] = array(
							'name'  => __( 'Post Longitude', 'geodirectory' ),
							'value' => $gd_post->longitude,
						);
					}
					$value = '';
					break;
				case 'business_hours':
					break;
				case 'categories':
				case 'tags':
					if ( $value != '' ) {
						$terms = explode( ',', $value );
						if ( ! empty( $terms ) ) {
							if ( $field['field_type'] == 'tags' ) {
								$taxonomy = $gd_post->post_type . '_tags';
								$term_by = 'name';
							} else {
								$taxonomy = $gd_post->post_type . 'category';
								$term_by = 'id';
							}
							$values = array();
							$terms = array_unique( $terms );
							foreach ( $terms as $term ) {
								$term = trim( $term );
								if ( $term != '' ) {
									$term = get_term_by( $term_by, $term, $taxonomy );
									if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
										$values[] = $term->name;
									}
								}
							}
							$value = ! empty( $values ) ? implode( ', ', $values ) : '';
						}
					}
					break;
				case 'checkbox':
					if ( (int)$value == 1 ) {
						$value = __( 'Yes', 'geodirectory' );
					} else {
						$value = '';
					}
					break;
				case 'file':
					$value = '';
					$attachments = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $field['name'] );
					if ( ! empty( $attachments ) ) {
						$upload_dir = wp_upload_dir();
						$files = array();
						foreach ( $attachments as $key => $attachment ) {
							if ( ! empty( $attachment->file ) ) {
								$files[] = $upload_dir['baseurl'] . $attachment->file;
							}
						}
						if ( ! empty( $files ) ) {
							$value = self::parse_files_value( $files );
						}
					}
					break;
				case 'images':
					$post_images = geodir_get_images( $gd_post->ID );
					$featured_image = '';
					if ( ! empty( $post_images ) ) {
						$images = array();
						foreach ( $post_images as $key => $post_image ) {
							if ( $image_src = geodir_get_image_src( $post_image , '' ) ) {
								$images[] = $image_src;
							}
						}
						if ( ! empty( $images ) ) {
							$featured_image = $images[0];
							$value = self::parse_files_value( $images );
						}
					}
					if ( ! empty( $featured_image ) ) {
						$personal_data[] = array(
							'name'  => __( 'Featured Image', 'geodirectory' ),
							'value' => $featured_image,
						);
					}
					break;
				case 'multiselect':
					$field_values = explode( ',', trim( $value, "," ) );
					if ( is_array( $field_values ) ) {
						$field_values = array_map( 'trim', $field_values );
					}
					$values = array();
					if ( ! empty( $option_values ) ) {
						$option_values = geodir_string_values_to_options( $option_values, true );
						if ( ! empty( $option_values ) ) {
							foreach ( $option_values as $option_value ) {
								if ( isset( $option_value['value'] ) && in_array( $option_value['value'], $field_values ) ) {
									$values[] = __( $option_value['label'], 'geodirectory' );
								}
							}
						}
					}
					$value = ! empty( $values ) ? implode( ', ', $values ) : $value;
					break;
				case 'radio':
					if ( $value == 'f' || $value == '0') {
						$value = __( 'No', 'geodirectory' );
					} else if ( $value == 't' || $value == '1') {
						$value = __( 'Yes', 'geodirectory' );
					} else {
						if ( !empty( $option_values ) ) {
							$option_values = geodir_string_values_to_options( $option_values, true );
							if ( ! empty( $option_values ) ) {
								foreach ( $option_values as $option_value ) {
									if ( isset( $option_value['value'] ) && $option_value['value'] == $value ) {
										$value = __( $option_value['label'], 'geodirectory' );
									}
								}
							}
						}
					}
					break;
				case 'select':
					if ( !empty( $option_values ) ) {
						$option_values = geodir_string_values_to_options( $option_values, true );
						if ( ! empty( $option_values ) ) {
							foreach ( $option_values as $option_value ) {
								if ( isset( $option_value['value'] ) && $option_value['value'] == $value ) {
									$value = __( $option_value['label'], 'geodirectory' );
								}
							}
						}
					}
					break;
			}

			$value = apply_filters( 'geodir_privacy_export_post_personal_data_field_value', $value, $field, $gd_post );

			if ( ! empty( $name ) && $value !== '' ) {
				$personal_data[] = array(
					'name'  => __( $name, 'geodirectory' ),
					'value' => $value,
				);
			}

			if ( $field['field_type'] == 'categories' && ! empty( $gd_post->default_category ) ) {
				$term = get_term_by( 'id', (int)$gd_post->default_category, $gd_post->post_type . 'category' );
				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$personal_data[] = array(
						'name'  => __( 'Default Category', 'geodirectory' ),
						'value' => $term->name,
					);
				}
			}

			/**
			 * Filter export post data for each field.
			 *
			 * @since 1.6.26
			 * @param array    $personal_data Array of name value pairs to expose in the export.
			 * @param object   $gd_post The post object.
			 */
			$personal_data = apply_filters( 'geodir_privacy_export_post_field_personal_data', $personal_data, $field, $gd_post );
		}

		return $personal_data;
	}

	public static function posts_by_author( $email_address, $post_type, $page ) {
		if ( empty( $email_address ) || empty( $post_type ) || empty( $page ) ) {
			return array();
		}

		$user = get_user_by( 'email', $email_address );
		if ( empty( $user ) ) {
			return array();
		}

		$statuses = array_keys( get_post_statuses() );
		$skip_statuses = geodir_imex_export_skip_statuses();
		if ( ! empty( $skip_statuses ) ) {
			$statuses = array_diff( $statuses, $skip_statuses );
		}

		$query_args    = array(
			'post_type'			=> $post_type,
			'post_status'		=> $statuses,
			'fields'			=> 'ids',
			'author'   			=> $user->ID,
			'posts_per_page'	=> 10,
			'paged'     		=> $page,
			'orderby'  			=> 'ID',
			'order'	   			=> 'ASC'
		);

		$query_args = apply_filters( 'geodir_privacy_post_data_exporter_post_query', $query_args, $post_type, $email_address, $page );

		$posts = get_posts( $query_args );

		return apply_filters( 'geodir_privacy_post_data_exporter_posts', $posts, $query_args, $post_type, $email_address, $page );
	}

	public static function review_data_exporter( $response, $exporter_index, $email_address, $page, $request_id, $send_as_email, $exporter_key ) {
		global $wpdb;

		$exporter_key = GeoDir_Privacy::personal_data_exporter_key();

		if ( $exporter_key == 'wordpress-comments' && ! empty( $response['data'] ) ) {
			foreach ( $response['data'] as $key => $item ) {
				$comment_id = str_replace( 'comment-', '', $item['item_id'] );
				$data = $item['data'];

				$review = GeoDir_Comments::get_review( $comment_id );
				if ( ! empty( $review ) ) {
					if ( ! empty( $review->rating ) ) {
						$data[] = array(
							'name'  => __( 'Rating (Overall)', 'geodirectory' ),
							'value' => geodir_sanitize_float( $review->rating ) . ' / 5',
						);
					}
					if ( ! empty( $review->city ) ) {
						$data[] = array(
							'name'  => __( 'Review City', 'geodirectory' ),
							'value' => $review->city,
						);
					}
					if ( ! empty( $review->region ) ) {
						$data[] = array(
							'name'  => __( 'Review Region', 'geodirectory' ),
							'value' => $review->region,
						);
					}
					if ( ! empty( $review->country ) ) {
						$data[] = array(
							'name'  => __( 'Review Country', 'geodirectory' ),
							'value' => $review->country,
						);
					}
					if ( ! empty( $review->latitude ) ) {
						$data[] = array(
							'name'  => __( 'Review Latitude', 'geodirectory' ),
							'value' => $review->latitude,
						);
					}
					if ( ! empty( $review->longitude ) ) {
						$data[] = array(
							'name'  => __( 'Review Longitude', 'geodirectory' ),
							'value' => $review->longitude,
						);
					}

					$data = apply_filters( 'geodir_privacy_export_review_data', $data, $review, $email_address );

					if ( ! empty( $data ) ) {
						$response['data'][ $key ]['data'] = $data;
					}
				}
			}
		}
		return $response;
	}

	/**
	 * Finds and exports data which could be used to identify a person from GeoDirectory data associated with an email address.
	 *
	 * @since 1.6.26
	 * @param string $email_address The user email address.
	 * @param int    $page  Page.
	 * @return array An array of personal data in name value pairs
	 */
	public static function favorites_data_exporter( $email_address, $page ) {
		$done           = true;
		$page           = (int) $page;
		$data_to_export = array();

		$items 			= GeoDir_Privacy::favorites_by_user( $email_address, $page );

		if ( 0 < count( $items ) ) {
			foreach ( $items as $item ) {
				$gd_post = geodir_get_post_info( $item );
				if ( empty( $gd_post ) ) {
					continue;
				}

				$data_to_export[] = array(
					'group_id'          => 'geodirectory-post-favorites',
					'group_label'       => __( 'GeoDirectory: Favorite Listings', 'geodirectory' ),
					'group_description' => __( 'User&#8217;s favorite listings data for GeoDirectory.', 'geodirectory' ),
					'item_id'           => 'gd-favorite-' . $gd_post->ID,
					'data'              => array(
						array(
							'name'  => __( 'Post ID', 'geodirectory' ),
							'value' => $gd_post->ID,
						),
						array(
							'name'  => __( 'Post Title', 'geodirectory' ),
							'value' => $gd_post->post_title,
						),
						array(
							'name'  => __( 'Post URL', 'geodirectory' ),
							'value' => get_permalink( $gd_post->ID ),
						)
					),
				);
			}
		}

		return array(
			'data' => $data_to_export,
			'done' => $done,
		);
	}

	public static function parse_files_value( $files ) {
		if ( empty( $files ) ) {
			return '';
		}

		if ( ! is_array( $files ) ) {
			return $files;
		}

		if ( count( $files ) == 1 ) {
			return $files[0];
		}

		$links = array();
		foreach ( $files as $file ) {
			if ( false === strpos( $file, ' ' ) && ( 0 === strpos( $file, 'http://' ) || 0 === strpos( $file, 'https://' ) ) ) {
				$file = '<a href="' . esc_url( $file ) . '">' . esc_html( $file ) . '</a>';
			}
			$links[] = $file;
		}
		$links = ! empty( $links ) ? implode( ' <br> ', $links ) : '';

		return $links;
	}
}
