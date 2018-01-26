<?php
// don't load directly
if ( !defined('ABSPATH') )
    die('-1');

if ( !isset( $email_vars ) ) {
    global $email_vars;
}
$email_footer = apply_filters( 'geodir_email_footer_text', geodir_get_option( 'email_footer_text' ) );
$email_footer = $email_footer ? wpautop( wp_kses_post( wptexturize( $email_footer ) ) ) : '';
?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
				<?php if ( $email_footer ) { ?>
				<tr>
                    <td align="center" valign="middle" id="template_footer">
						<!-- Footer -->
						<table border="0" cellpadding="10" cellspacing="0" width="100%">
							<tr>
								<td colspan="2" valign="middle" id="footer_text">
									<?php echo $email_footer; ?>
								</td>
							</tr>
						</table>
						<!-- End Footer -->
					</td>
                </tr>
				<?php } ?>
            </table>
        </div>
    </body>
 </html>