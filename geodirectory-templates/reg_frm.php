<?php
/**
 * Template for the register for box on the register/signin page
 *
 * You can make most changes via hooks or see the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 */
if (isset($_GET['redirect_to']) && $_GET['redirect_to'] != '') {
    $redirect_to = $_GET['redirect_to'];
} else {
    //echo $_SERVER['HTTP_HOST'] ;
    $redirect_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (strpos($redirect_to, $_SERVER['HTTP_HOST']) === false) {
        $redirect_to = home_url();
    }
}
?>
<div id="sign_up">
    <div class="login_content">
        <?php echo stripslashes(get_option('ptthemes_reg_page_content')); ?>
    </div>
    <div class="registration_form_box">
        <h4>
            <?php

            /**
             * Filter the `REGISTRATION_NOW_TEXT` title text on the register form template.
             *
             * @since 1.0.0
             */
            echo apply_filters('geodir_registration_page_title', REGISTRATION_NOW_TEXT);

            ?>
        </h4>
        <?php
        global $geodir_signup_error;
        if ($geodir_signup_error != '') {
            echo '<p class="error_msg">' . $geodir_signup_error . '</p>';
            unset($geodir_signup_error);
        } else {
            if (isset($_REQUEST['emsg']) && $_REQUEST['emsg'] == 1) {
                echo '<p class="error_msg">' . EMAIL_USERNAME_EXIST_MSG . '</p>';
            } else if (isset($_REQUEST['emsg']) && $_REQUEST['emsg'] == 'regnewusr') {
                echo '<p class="error_msg">' . REGISTRATION_DESABLED_MSG . '</p>';
            }
        }
        ?>

        <form name="cus_registerform" id="cus_registerform" method="post">
            <input type="hidden" name="action" value="register"/>
            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirect_to); ?>"/>

            <div class="form_row clearfix">
                <input placeholder='<?php echo EMAIL_TEXT; ?>' type="text" name="user_email" id="user_email"
                       class="textfield" value="<?php global $user_email;
                if (!isset($user_email)) {
                    $user_email = '';
                }
                echo esc_attr(stripslashes($user_email)); ?>" size="25"/>

                <?php if (!get_option('geodir_allow_cpass')) { ?>
				<div id="reg_passmail">
                    <?php echo REGISTRATION_MESSAGE; ?>
                </div>
				<?php } ?>
                <span id="user_emailInfo"></span>
            </div>

            <div class="row_spacer_registration clearfix">
                <div class="form_row clearfix">
                    <input placeholder='<?php echo FIRST_NAME_TEXT; ?>' type="text" name="user_fname" id="user_fname"
                           class="textfield" value="<?php if (isset($user_fname)) {
                        echo esc_attr(stripslashes($user_fname));
                    } ?>" size="25"/>
                    <span id="user_fnameInfo"></span>
                </div>
            </div>

            <?php if (get_option('geodir_allow_cpass')) { ?>

                <div class="row_spacer_registration clearfix">
                    <div class="form_row clearfix">
                        <input placeholder='<?php echo PASSWORD_TEXT; ?>' type="password" name="user_pass"
                               id="user_pass1" class="textfield input-text" value="" size="25"/>
                        <span id="user_passInfo1"></span>
                    </div>
                </div>

                <div class="row_spacer_registration clearfix">
                    <div class="form_row clearfix">
                        <input placeholder='<?php echo CONFIRM_PASSWORD_TEXT; ?>' type="password" name="user_pass2"
                               id="user_pass2" class="textfield input-text" value="" size="25"/>
                        <span id="user_passInfo2"></span>
                    </div>
                </div>

            <?php } ?>

            <?php
            /**
             * Called just before the register new user button on the register form template.
             *
             * Also used by other plugins to add social connect buttons.
             *
             * @since 1.0.0
             */
            do_action('social_connect_form'); ?>
            <input type="submit" name="registernow" value="<?php echo REGISTER_NOW_TEXT; ?>" class="geodir_button"/>
        </form>
    </div>

</div>