<?php
/**
 * Email Footer Template
 *
 * This template is loaded after the main email content.
 *
 * @package GeoDirectory
 * @since 3.0.0
 *
 * @var string $email_name    Email type identifier
 * @var array  $email_vars    Email template variables
 * @var bool   $plain_text    Whether this is plain text email
 * @var string $footer_text   Processed footer text HTML
 * @var bool   $sent_to_admin Whether sent to admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
														</div>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<?php if ( ! empty( $footer_text ) ) { ?>
			<tr>
				<td align="center" valign="middle" id="template_footer">
					<table border="0" cellpadding="10" cellspacing="0" width="100%">
						<tr>
							<td colspan="2" valign="middle" id="footer_text">
								<?php echo $footer_text; // Already escaped in service ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<?php } ?>
		</table>
	</div>
</body>
</html>
