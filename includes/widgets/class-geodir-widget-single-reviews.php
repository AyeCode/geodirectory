<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Single_Reviews.
 *
 * @since 2.0.0.63
 */
class GeoDir_Widget_Single_Reviews extends WP_Super_Duper {

	/**
	 * Register the advanced search widget with WordPress.
	 *
	 * @since 2.0.0.63
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'admin-site',
			'block-category' => 'geodirectory',
			'block-keywords' => "['geo','reviews','comments']",
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_single_reviews', // this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Single Reviews', 'geodirectory' ), // the name of the widget.
			'widget_ops'     => array(
				'classname'    => 'geodir-single-reviews-container ' . geodir_bsui_class(), // widget class
				'description'  => esc_html__( 'Shows the comment/reviews area for a single post. (this will remove any further instances of the comments section on the page)', 'geodirectory' ), // widget description
				'geodirectory' => true,
			),
		);

		parent::__construct( $options );
	}

	/**
	 * Set the arguments later.
	 *
	 * @return array
	 */
	public function set_arguments() {
		$design_style = geodir_design_style();

		$arguments = array();

		$arguments['tabs_notice'] = array(
			'type'     => 'notice',
			'desc'     => __( 'Only ONE instance of reviews will show, if set to show in GD Tabs block, this block may not show.', 'geodirectory' ),
			'status'   => 'warning',
			'group'    => __( 'Title', 'geodirectory' ),
		);

		$arguments['title'] = array(
			'title'    => __( 'Title:', 'geodirectory' ),
			'desc'     => __( 'The widget title.', 'geodirectory' ),
			'type'     => 'text',
			'default'  => '',
			'desc_tip' => true,
			'group'    => __( 'Title', 'geodirectory' ),
			'advanced' => false,
		);

		if ( $design_style ) {
			// title styles
			$arguments = $arguments + geodir_get_sd_title_inputs();
		}

		$arguments['template'] = array(
			'type'      => 'select',
			'title'     => __( 'Review Style', 'geodirectory' ),
			'options'   => array(
				''      => __( 'Default', 'geodirectory' ),
				'clean' => __( 'Clean', 'geodirectory' )
			),
			'default'  => '',
			'desc_tip' => false,
			'advanced' => false,
			'group'    => __( 'Style', 'geodirectory' ),
		);

		return $arguments;
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
		global $post,$gd_review_template;

		$gd_review_template = ! empty( $args['template'] ) && in_array( $args['template'], array( 'clean' ), true ) ? esc_attr( $args['template'] ) : '';

		ob_start();

		if ( geodir_is_page( 'single' ) ) {
			do_action( 'geodir_single_reviews_widget_content_before' );

			comments_template();

			do_action( 'geodir_single_reviews_widget_content_after' );
		} elseif ( $this->is_preview() ) {
			echo $this->get_preview( $gd_review_template );
		}

		return ob_get_clean();
	}

	public function get_preview( $template = '' ) {
		ob_start();
		?>
		<div class="commentlist-wrap">
			<div class="row gy-4 mb-5">
				<div class="col-sm-4">
					<div class="card border-0 rounded bg-transparent-primary bg-primary bg-opacity-10 mt-0 p-0">
						<div class="card-body text-center text-dark ">
							<div class="mb-1">Very Good</div>
							<div class="mb-1 display-5">3.8</div>
							<div class="mb-1">
								<div class="gd-rating-outer-wrap gd-rating-output-wrap   justify-content-between flex-nowrap w-100">
									<div class="gd-rating gd-rating-output gd-rating-type-font-awesome">
										<span class="gd-rating-wrap d-inline-flex position-relative " title="3 star rating"><span class="gd-rating-foreground position-absolute text-nowrap overflow-hidden" style="width:75%;  color:#ff9900; "><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span><span class="gd-rating-background" style="color:#afafaf;"><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span></span>
									</div>
								</div>
							</div>
							<span class="fs-xs">4 reviews</span>
						</div>
					</div>
				</div>
				<div class="col-sm-8">
					<div class="row row-cols-1 gy-3">
						<div class="col">
							<div class="d-flex align-items-center">
								<div class="pe-2 pr-2 text-nowrap text-center fs-sm" style="min-width:50px">5 <i class="fas fa-star text-gray" aria-hidden="true"></i></div>
								<div class="progress w-100" style="height:14px;">
									<div class="progress-bar bg-warning" role="progressbar" style="width:50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="d-flex align-items-center">
								<div class="pe-2 pr-2 text-nowrap text-center fs-sm" style="min-width:50px">4 <i class="fas fa-star text-gray" aria-hidden="true"></i></div>
								<div class="progress w-100" style="height:14px;">
									<div class="progress-bar bg-warning" role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="d-flex align-items-center">
								<div class="pe-2 pr-2 text-nowrap text-center fs-sm" style="min-width:50px">3 <i class="fas fa-star text-gray" aria-hidden="true"></i></div>
								<div class="progress w-100" style="height:14px;">
									<div class="progress-bar bg-warning" role="progressbar" style="width:25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="d-flex align-items-center">
								<div class="pe-2 pr-2 text-nowrap text-center fs-sm" style="min-width:50px">2 <i class="fas fa-star text-gray" aria-hidden="true"></i></div>
								<div class="progress w-100" style="height:14px;">
									<div class="progress-bar bg-warning" role="progressbar" style="width:25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
							</div>
						</div>
						<div class="col">
							<div class="d-flex align-items-center">
								<div class="pe-2 pr-2 text-nowrap text-center fs-sm" style="min-width:50px">1 <i class="fas fa-star text-gray" aria-hidden="true"></i></div>
								<div class="progress w-100" style="height:14px;">
									<div class="progress-bar bg-warning" role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<ul class="commentlist list-unstyled">
			<?php if ( '' === $template ) { ?>
				<li class="comment byuser comment-author-admin bypostauthor even thread-even depth-1 geodir-comment list-unstyled border-0  m-0 p-0" id="li-comment-68">
					<div id="comment-68" class="card mt-3 shadow-sm mw-100 p-0">
						<div class="card-header border-bottom toast-header px-2 py-1 border-bottom border-opacity-25">
							<a href="#" class="media-object float-start"> <img alt="" src="<?php echo esc_url( geodir_plugin_url() . '/assets/images/dummy-avatar-2.png' ); ?>" srcset="<?php echo esc_url( geodir_plugin_url() . '/assets/images/dummy-avatar-2.png' ); ?>" class="avatar avatar-44 photo comment_avatar rounded-circle position-relative" height="44" width="44" loading="lazy" decoding="async"> </a> <span class="media-heading pl-2 ps-2 mr-auto me-auto h4 m-0 align-items-center d-flex justify-content-center h5"><a href="#" class="text-reset">Joe Bloggs</a> </span>
							<div class="geodir-review-ratings c-pointer" data-bs-toggle="popover-html" data-bs-sanitize="false" data-bs-placement="top" data-bs-html="true" data-bs-content="" data-bs-trigger="hover focus">
								<div class="gd-rating-outer-wrap gd-rating-output-wrap d-flex d-flex justify-content-between flex-nowrap w-100">
									<div class="gd-rating gd-rating-output gd-rating-type-font-awesome">
										<span class="gd-rating-wrap d-inline-flex position-relative " title="5 star rating"><span class="gd-rating-foreground position-absolute text-nowrap overflow-hidden" style="width:100%;  color:#ff9900; "><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span><span class="gd-rating-background" style="color:#afafaf;"><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span></span>
									</div>
								</div>
							</div>
						</div><!-- .comment-meta -->
						<div class="comment-content comment card-body m-0">
							<div class="description"><p>Vestibulum porta ornare lorem nec fringilla. Pellentesque ornare ipsum id pellentesque convallis. Nulla nibh erat, consectetur at fringilla sit amet, tincidunt eu tellus. In quis pellentesque tortor, sit amet vulputate lacus. Curabitur ultrices dignissim ante, in feugiat purus ultrices eget.</p></div>
						</div><!-- .comment-content -->
						<div class="card-footer py-2 px-3 bg-white">
							<div class="row">
								<div class="col-5 align-items-center d-flex"><a class="hidden-xs-down text-muted " href="#"><time class="chip timeago" datetime="2022-12-13T15:10:04+00:00"><i class="far fa-clock"></i> 3 months ago</time></a></div>
								<div class="col-7 text-right text-end">
									<div class="comment-links">
										<span class="edit-link btn btn-link"><a class="comment-edit-link" href="">Edit</a></span> <span class="reply-link"><a rel="nofollow" class="comment-reply-link btn btn-sm btn-primary" href="" data-commentid="68" data-postid="107789" data-belowelement="comment-68" data-respondelement="respond" data-replyto="Reply to Stiofan O'Connor" aria-label="Reply to Stiofan O'Connor">Reply</a></span>
									</div>
								</div>
							</div>
						</div><!-- .reply -->
					</div><!-- #comment-## -->
				</li>
				<li class="comment byuser comment-author-admin bypostauthor even thread-even depth-1 geodir-comment list-unstyled border-0  m-0 p-0" id="li-comment-68">
					<div id="comment-68" class="card mt-3 shadow-sm mw-100 p-0">
						<div class="card-header border-bottom toast-header px-2 py-1 border-bottom border-opacity-25">
							<a href="#" class="media-object float-start"> <img alt="" src="<?php echo esc_url( geodir_plugin_url() . '/assets/images/dummy-avatar-1.png' ); ?>" srcset="<?php echo esc_url( geodir_plugin_url() . '/assets/images/dummy-avatar-1.png' ); ?>" class="avatar avatar-44 photo comment_avatar rounded-circle position-relative" height="44" width="44" loading="lazy" decoding="async"> </a> <span class="media-heading pl-2 ps-2 mr-auto me-auto h4 m-0 align-items-center d-flex justify-content-center h5"><a href="#" class="text-reset">Amanda Barrett</a> </span>
							<div class="geodir-review-ratings c-pointer" data-bs-toggle="popover-html" data-bs-sanitize="false" data-bs-placement="top" data-bs-html="true" data-bs-content="" data-bs-trigger="hover focus">
								<div class="gd-rating-outer-wrap gd-rating-output-wrap d-flex d-flex justify-content-between flex-nowrap w-100">
									<div class="gd-rating gd-rating-output gd-rating-type-font-awesome">
										<span class="gd-rating-wrap d-inline-flex position-relative " title="5 star rating"><span class="gd-rating-foreground position-absolute text-nowrap overflow-hidden" style="width:100%;  color:#ff9900; "><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span> <span class="gd-rating-background" style="color:#afafaf;"><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span></span>
									</div>
								</div>
							</div>
						</div><!-- .comment-meta -->
						<div class="comment-content comment card-body m-0">
							<div class="description"><p>In hac habitasse platea dictumst. Proin a leo auctor, commodo lectus vitae, rhoncus eros. Donec mauris leo, tincidunt in efficitur sed, imperdiet vitae justo. Aliquam feugiat bibendum ipsum, a elementum odio ullamcorper vel. Suspendisse eu varius lectus, sed ultricies augue. Donec id odio felis.</p>
							</div>
						</div><!-- .comment-content -->
						<div class="card-footer py-2 px-3 bg-white">
							<div class="row">
								<div class="col-5 align-items-center d-flex"><a class="hidden-xs-down text-muted " href="#"><time class="chip timeago" datetime="2022-12-13T15:10:04+00:00"><i class="far fa-clock"></i> 3 months ago</time></a></div>
								<div class="col-7 text-right text-end">
									<div class="comment-links">
										<span class="edit-link btn btn-link"><a class="comment-edit-link" href="#">Edit</a></span> <span class="reply-link"><a rel="nofollow" class="comment-reply-link btn btn-sm btn-primary" href="#" data-commentid="68" data-postid="107789" data-belowelement="comment-68" data-respondelement="respond" data-replyto="Reply to Stiofan O'Connor" aria-label="Reply to Stiofan O'Connor">Reply</a></span>
									</div>
								</div>
							</div>
						</div><!-- .reply -->
					</div><!-- #comment-## -->
				</li>
				<?php } else { ?>
				<li class="comment byuser comment-author-admin bypostauthor even thread-even depth-1 geodir-comment list-unstyled mb-4 pb-3 border-bottom fs-sm" id="li-comment-68">
					<div class="" id="comment-68">
						<div class="d-flex justify-content-between mb-3">
							<div class="d-flex align-items-center pe-2 pr-2">
								<a href="#profile link" class="media-object float-start"> <img alt="" src="<?php echo esc_url( geodir_plugin_url() . '/assets/images/dummy-avatar-2.png' ); ?>" srcset="<?php echo esc_url( geodir_plugin_url() . '/assets/images/dummy-avatar-2.png' ); ?>" class="avatar avatar-44 photo comment_avatar rounded-circle position-relative" height="44" width="44" loading="lazy" decoding="async"></a>
								<div class="ps-2 pl-2">
									<h6 class="fs-base mb-0 d-flex align-items-center"><a href="#" class="text-reset">Joe Bloggs</a> </h6>
									<div class="geodir-review-ratings c-pointer" data-bs-toggle="popover-html" data-bs-sanitize="false" data-bs-placement="top" data-bs-html="true" data-bs-content="" data-bs-trigger="hover focus">
										<div class="gd-rating-outer-wrap gd-rating-output-wrap d-flex d-flex justify-content-between flex-nowrap w-100">
											<div class="gd-rating gd-rating-output gd-rating-type-font-awesome">
												<span class="gd-rating-wrap d-inline-flex position-relative " title="5 star rating"><span class="gd-rating-foreground position-absolute text-nowrap overflow-hidden" style="width:100%;  color:#ff9900; "><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span><span class="gd-rating-background" style="color:#afafaf;"><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span></span>
											</div>
										</div>
									</div>
								</div>
							</div>
							<span class="text-muted fs-sm "><time class="chip timeago" datetime="2022-12-13T15:10:04+00:00"><i class="far fa-clock"></i> 2 days ago</time></span>
						</div>
						<div class="comment-content comment m-0 mb-n3">
							<div class="description"><p class="fs-sm">Vestibulum porta ornare lorem nec fringilla. Pellentesque ornare ipsum id pellentesque convallis. Nulla nibh erat, consectetur at fringilla sit amet, tincidunt eu tellus. In quis pellentesque tortor, sit amet vulputate lacus. Curabitur ultrices dignissim ante, in feugiat purus ultrices eget.</p></div>
						</div><!-- .comment-content -->
						<div class=" text-right text-end">
							<div class="comment-links d-inline-flex align-items-center text-end">
								<span class="edit-link"><a class="comment-edit-link btn btn-sm btn-link px-1" href="#">Edit</a></span> <span class="reply-link"><a rel="nofollow" class="comment-reply-link btn btn-sm btn-link px-1" href="#" data-commentid="68" data-postid="107789" data-belowelement="comment-68" data-respondelement="respond" data-replyto="Reply to Stiofan O'Connor" aria-label="Reply to Stiofan O'Connor">Reply</a></span>
							</div>
						</div>
					</div><!-- .reply -->
				</li><!-- #comment-## -->
				<li class="comment byuser comment-author-admin bypostauthor odd alt thread-odd thread-alt depth-1 geodir-comment list-unstyled mb-4 pb-3 border-bottom fs-sm" id="li-comment-69">
					<div class="" id="comment-69">
						<div class="d-flex justify-content-between mb-3">
							<div class="d-flex align-items-center pe-2 pr-2">
								<a href="#" class="media-object float-start"> <img alt="" src="<?php echo esc_url( geodir_plugin_url() . '/assets/images/dummy-avatar-1.png' ); ?>" srcset="<?php echo esc_url( geodir_plugin_url() . '/assets/images/dummy-avatar-1.png' ); ?>" class="avatar avatar-44 photo comment_avatar rounded-circle position-relative" height="44" width="44" loading="lazy" decoding="async"></a>
								<div class="ps-2 pl-2">
									<h6 class="fs-base mb-0 d-flex align-items-center"><a href="#" class="text-reset">Amanda Barrett</a> </h6>
									<div class="geodir-review-ratings c-pointer" data-bs-toggle="popover-html" data-bs-sanitize="false" data-bs-placement="top" data-bs-html="true" data-bs-content="" data-bs-trigger="hover focus">
										<div class="gd-rating-outer-wrap gd-rating-output-wrap d-flex d-flex justify-content-between flex-nowrap w-100">
											<div class="gd-rating gd-rating-output gd-rating-type-font-awesome">
												<span class="gd-rating-wrap d-inline-flex position-relative " title="5 star rating"><span class="gd-rating-foreground position-absolute text-nowrap overflow-hidden" style="width:100%;  color:#ff9900; "><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span><span class="gd-rating-background" style="color:#afafaf;"><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i><i class="fas fa-star fa-fw" aria-hidden="true"></i></span></span>
											</div>
										</div>
									</div>
								</div>
							</div>
							<span class="text-muted fs-sm "><time class="chip timeago" datetime="2022-12-13T15:10:32+00:00"><i class="far fa-clock"></i> 3 months ago</time></span>
						</div>
						<div class="comment-content comment m-0 mb-n3">
							<div class="description"><p class="fs-sm">In hac habitasse platea dictumst. Proin a leo auctor, commodo lectus vitae, rhoncus eros. Donec mauris leo, tincidunt in efficitur sed, imperdiet vitae justo. Aliquam feugiat bibendum ipsum, a elementum odio ullamcorper vel. Suspendisse eu varius lectus, sed ultricies augue. Donec id odio felis.</p></div>
						</div><!-- .comment-content -->
						<div class=" text-right text-end">
							<div class="comment-links d-inline-flex align-items-center text-end">
								<span class="edit-link"><a class="comment-edit-link btn btn-sm btn-link px-1" href="#">Edit</a></span> <span class="reply-link"><a rel="nofollow" class="comment-reply-link btn btn-sm btn-link px-1" href="#" data-commentid="69" data-postid="107789" data-belowelement="comment-69" data-respondelement="respond" data-replyto="Reply to Stiofan O'Connor" aria-label="Reply to Stiofan O'Connor">Reply</a></span>
							</div>
						</div>
					</div><!-- .reply -->
				</li>
				<?php } ?>
			</ul><!-- .commentlist -->
		</div>
		<?php
		return ob_get_clean();
	}
}
