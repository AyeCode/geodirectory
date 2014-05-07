<?php 
if(get_current_user_id()){wp_redirect( home_url(), 302 ); exit;}
get_header(); ?>
<script type="text/javascript" >
	<?php if ( $user_login ) { ?>
				setTimeout( function(){ try{
						d = document.getElementById('user_pass');
						d.value = '';
						d.focus();
					} catch(e){}
				}, 200);
	<?php } else { ?>
				try{document.getElementById('user_login').focus();}catch(e){}
	<?php } ?>
</script>
<script type="text/javascript" >
	<?php if ( $user_login ) { ?>
			setTimeout( function(){ try{
					d = document.getElementById('user_pass');
					d.value = '';
					d.focus();
				} catch(e){}
			}, 200);
	<?php } else { ?>
			try{document.getElementById('user_login').focus();}catch(e){}
	<?php } ?>
</script>

<div id="geodir_wrapper" class="geodir-login">
	

				<div class="clearfix geodir-common">       


					<div id="geodir_content" class="" role="main">
            
       
            <?php dynamic_sidebar('Reg/Login Top Section'); ?>
            
            <?php
            global $errors;
            if(isset($_REQUEST['msg']) && $_REQUEST['msg']=='claim')
                $errors->add('claim_login', LOGIN_CLAIM);
                
            if(!empty($errors)){
                foreach($errors as $errorsObj)
                {
                    foreach($errorsObj as $key=>$val)
                    {
                        for($i=0;$i<count($val);$i++)
                        {
                            echo "<div class=sucess_msg>".$val[$i].'</div>';	
                            $registration_error_msg = 1;
                        }
                    } 
                }
            }	
            
            if(isset($_REQUEST['page']) && $_REQUEST['page']=='login' && isset($_REQUEST['page1']) && $_REQUEST['page1']=='sign_in'){?>
            
                <div class="login_form">
                    <?php include (geodir_plugin_path() . "/geodirectory-templates/login_frm.php"); ?>
                </div> 
            
            <?php }elseif(isset($_REQUEST['page']) && $_REQUEST['page']=='login' && isset($_REQUEST['page1']) && $_REQUEST['page1']=='sign_up'){ ?>
            
                <div class="registration_form">
                    <?php include (geodir_plugin_path() . "/geodirectory-templates/reg_frm.php"); ?>
                </div>
                
            <?php }else { ?>
           
                <div class="login_form_l">
                    <?php include (geodir_plugin_path() . "/geodirectory-templates/login_frm.php");?>
                </div>
                <div class="registration_form_r">
                    <?php include (geodir_plugin_path() . "/geodirectory-templates/reg_frm.php");	?>
                </div>
            
            <?php }?>
       </div>
	</div>
</div>
<script type="text/javascript">
	try{document.getElementById('user_login').focus();}catch(e){}
</script>


	<?php if((isset($errors->errors['invalidcombo']) && $errors->errors['invalidcombo'] != '') || (isset($errors->errors['empty_username']) && $errors->errors['empty_username'] != '')) {?>
		<script type="text/javascript">document.getElementById('lostpassword_form').style.display = '';</script>
	<?php }   

get_footer();  