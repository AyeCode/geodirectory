jQuery(document).ready(function () {
	jQuery('a.b_sendtofriend').click(function (e) {
		e.preventDefault();
		jQuery('#basic-modal-content').modal({persist:true});
	});
	
	jQuery('a.b_send_inquiry' ).click(function (e) {
		e.preventDefault();
		jQuery('#basic-modal-content2').modal({persist:true});
	});
	
	jQuery('a.b_claim_listing').click(function (e) {
		e.preventDefault();
		jQuery('#basic-modal-content4').modal({persist:true});
	});
	
	jQuery('p.links a.a_image_sort').click(function (e) {
		e.preventDefault();
		jQuery('#basic-modal-content3').modal({persist:true});
	});
	
});


jQuery(document).ready(function(){
//global vars
	var enquiryfrm = jQuery("#send_to_frnd");
	var to_name = jQuery("#to_name");
	var to_nameInfo = jQuery("#to_nameInfo");
	var to_email = jQuery("#to_email");
	var to_emailInfo = jQuery("#to_emailInfo");
	var yourname = jQuery("#yourname");
	var yournameInfo = jQuery("#yournameInfo");
	var youremail = jQuery("#youremail");
	var youremailInfo = jQuery("#youremailInfo");
	var frnd_comments = jQuery("#frnd_comments");
	var frnd_commentsInfo = jQuery("#frnd_commentsInfo");
	
	var frnd_subject = jQuery("#frnd_subject");
	var frnd_subjectInfo = jQuery("#frnd_subjectInfo");

	//On blur
	to_name.blur(validate_to_name);
	to_email.blur(validate_to_email);
	yourname.blur(validate_yourname);
	youremail.blur(validate_youremail);
	frnd_comments.blur(validate_frnd_comments);
	frnd_subject.blur(validate_frnd_subject);

	//On key press
	to_name.keyup(validate_to_name);
	to_email.keyup(validate_to_email);
	yourname.keyup(validate_yourname);
	youremail.keyup(validate_youremail);
	frnd_comments.keyup(validate_frnd_comments);
	frnd_subject.keyup(validate_frnd_subject);

	//On Submitting
	enquiryfrm.submit(function(){
		if(validate_to_name() && validate_to_email() && validate_yourname() && validate_youremail() && validate_frnd_subject() && validate_frnd_comments())
		{
			function reset_send_email_agent_form()
			{
				document.getElementById('to_name').value = '';
				document.getElementById('to_email').value = '';
				document.getElementById('yourname').value = '';
				document.getElementById('youremail').value = '';	
				document.getElementById('frnd_subject').value = '';
				document.getElementById('frnd_comments').value = '';	
			}
			return true
		}
		else
		{
			return false;
		}
	});
	
	//validation functions
	function validate_to_name()
	{
		if(to_name.val() == '')
		{
			to_name.addClass("error");
			to_nameInfo.text("Please Enter To Name");
			to_nameInfo.addClass("message_error2");
			return false;
		}
		else{
			to_name.removeClass("error");
			to_nameInfo.text("");
			to_nameInfo.removeClass("message_error2");
			return true;
		}
	}
	function validate_to_email()
	{
		var isvalidemailflag = 0;
		if(to_email.val() == '')
		{
			isvalidemailflag = 1;
		}else
		if(to_email.val() != '')
		{
			var a = to_email.val();
			var filter =  /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			//if it's valid email
			if(filter.test(a)){
				isvalidemailflag = 0;
			}else{
				isvalidemailflag = 1;	
			}
		}
		if(isvalidemailflag)
		{
			to_email.addClass("error");
			to_emailInfo.text("Please Enter valid Email Address");
			to_emailInfo.addClass("message_error2");
			return false;
		}else
		{
			to_email.removeClass("error");
			to_emailInfo.text("");
			to_emailInfo.removeClass("message_error");
			return true;
		}
	}

	function validate_yourname()
	{
		if(yourname.val() == '')
		{
			yourname.addClass("error");
			yournameInfo.text("Please Enter Your Name");
			yournameInfo.addClass("message_error2");
			return false;
		}
		else{
			yourname.removeClass("error");
			yournameInfo.text("");
			yournameInfo.removeClass("message_error2");
			return true;
		}
	}

	function validate_youremail()
	{
		var isvalidemailflag = 0;
		if(youremail.val() == '')
		{
			isvalidemailflag = 1;
		}else
		if(youremail.val() != '')
		{
			var a = youremail.val();
			var filter =  /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			//if it's valid email
			if(filter.test(a)){
				isvalidemailflag = 0;
			}else{
				isvalidemailflag = 1;	
			}
		}
		if(isvalidemailflag)
		{
			youremail.addClass("error");
			youremailInfo.text("Please Enter valid Email Address");
			youremailInfo.addClass("message_error2");
			return false;
		}else
		{
			youremail.removeClass("error");
			youremailInfo.text("");
			youremailInfo.removeClass("message_error");
			return true;
		}
	}
	function validate_frnd_comments()

	{
		if(frnd_comments.val() == '')
		{
			frnd_comments.addClass("error");
			frnd_commentsInfo.text("Please Enter Comments");
			frnd_commentsInfo.addClass("message_error2");
			return false;
		}
		else{
			frnd_comments.removeClass("error");
			frnd_commentsInfo.text("");
			frnd_commentsInfo.removeClass("message_error2");
			return true;
		}
	}
	
	function validate_frnd_subject()
	{
		if(frnd_subject.val() == '')
		{
			frnd_subject.addClass("error");
			frnd_subjectInfo.text("Please Enter Subject");
			frnd_subjectInfo.addClass("message_error2");
			return false;
		}
		else{
			frnd_subject.removeClass("error");
			frnd_subjectInfo.text("");
			frnd_subjectInfo.removeClass("message_error2");
			return true;
		}
	}
});










jQuery(document).ready(function(){

//global vars

	var enquiryfrm = jQuery("#agt_mail_agent");

	var yourname = jQuery("#agt_mail_name");

	var yournameInfo = jQuery("#span_agt_mail_name");

	var youremail = jQuery("#agt_mail_email");

	var youremailInfo = jQuery("#span_agt_mail_email");

	var frnd_comments = jQuery("#agt_mail_msg");

	var frnd_commentsInfo = jQuery("#span_agt_mail_msg");

	

	//On blur

	yourname.blur(validate_yourname);

	youremail.blur(validate_youremail);

	frnd_comments.blur(validate_frnd_comments_author);

	//On key press

	yourname.keyup(validate_yourname);

	youremail.keyup(validate_youremail);

	frnd_comments.keyup(validate_frnd_comments_author);

	

	//On Submitting

	enquiryfrm.submit(function(){

		if(validate_yourname() & validate_youremail() & validate_frnd_comments_author())

		{
			//hideform();
			return true
		}
		else
		{
			return false;
		}
	});



	//validation functions

	function validate_yourname()

	{

		if(yourname.val() == '')

		{

			yourname.addClass("error");

			yournameInfo.text("Please Enter Your Name");

			yournameInfo.addClass("message_error2");

			return false;

		}

		else{

			yourname.removeClass("error");

			yournameInfo.text("");

			yournameInfo.removeClass("message_error2");

			return true;

		}

	}

	function validate_youremail()

	{

		var isvalidemailflag = 0;

		if(youremail.val() == '')

		{

			isvalidemailflag = 1;

		}else

		if(youremail.val() != '')

		{

			var a = youremail.val();

			var filter =  /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

			//if it's valid email

			if(filter.test(a)){

				isvalidemailflag = 0;

			}else{

				isvalidemailflag = 1;	

			}

		}

		if(isvalidemailflag)

		{

			youremail.addClass("error");

			youremailInfo.text("Please Enter valid Email Address");

			youremailInfo.addClass("message_error2");

			return false;

		}else

		{

			youremail.removeClass("error");

			youremailInfo.text("");

			youremailInfo.removeClass("message_error");

			return true;

		}

		

	}

	

	function validate_frnd_comments_author()
	{				
		if(frnd_comments.val() == '')
		{
			frnd_comments.addClass("error");
			frnd_commentsInfo.text("Please Enter Comments");
			frnd_commentsInfo.addClass("message_error2");
			return false;
		}else{
			frnd_comments.removeClass("error");
			frnd_commentsInfo.text("");
			frnd_commentsInfo.removeClass("message_error2");
			return true;
		}

	}	
function reset_email_agent_form()
{
	document.getElementById('agt_mail_name').value = '';
	document.getElementById('agt_mail_email').value = '';
	document.getElementById('agt_mail_phone').value = '';
	document.getElementById('agt_mail_msg').value = '';	
}
});





jQuery(document).ready(function(){
//global vars
	var enquiryfrm = jQuery("#claim_form");
	var full_name = jQuery("#full_name");
	var full_nameInfo = jQuery("#full_nameInfo");
	var user_number = jQuery("#user_number");
	var user_numberInfo = jQuery("#user_numberInfo");
	var user_position = jQuery("#user_position");
	var user_positionInfo = jQuery("#user_positionInfo");
	var user_comments = jQuery("#user_comments");
	var user_commentsInfo = jQuery("#user_commentsInfo");

	//On blur
	full_name.blur(validate_full_name);
	user_number.blur(validate_user_number);
	user_position.blur(validate_user_position);
	user_comments.blur(validate_user_comments);

	//On key press
	full_name.keyup(validate_full_name);
	user_number.keyup(validate_user_number);
	user_position.keyup(validate_user_position);
	user_comments.keyup(validate_user_comments);

	//On Submitting
	enquiryfrm.submit(function(){
		if(validate_full_name() & validate_user_number() & validate_user_position() & validate_user_comments())
		{
			function reset_send_email_agent_form()
			{
				document.getElementById('full_name').value = '';
				document.getElementById('user_number').value = '';
				document.getElementById('user_position').value = '';
				document.getElementById('user_comments').value = '';	
				
			}
			return true
		}
		else
		{
			return false;
		}
	});
	
	//validation functions
	function validate_full_name()
	{
		if(full_name.val() == '')
		{
			full_name.addClass("error");
			full_nameInfo.text("Please Enter Your Full Name");
			full_nameInfo.addClass("message_error2");
			return false;
		}
		else{
			full_name.removeClass("error");
			full_nameInfo.text("");
			full_nameInfo.removeClass("message_error2");
			return true;
		}
	}
	
	function validate_user_number()
	{
		if(user_number.val() == '')
		{
			user_number.addClass("error");
			user_numberInfo.text("Please Enter A Valid Contact Number");
			user_numberInfo.addClass("message_error2");
			return false;
		}
		else{
			user_number.removeClass("error");
			user_numberInfo.text("");
			user_numberInfo.removeClass("message_error2");
			return true;
		}
	}
	/*function validate_user_number()
	{
		var isvalidemailflag = 0;
		if(jQuery("#user_number").val() == '')
		{
			isvalidemailflag = 1;
		}else
		if(jQuery("#user_number").val() != '')
		{
			var a = jQuery("#user_number").val();
			var filter = /^(1\s*[-\/\.]?)?(\((\d{3})\)|(\d{3}))\s*[-\/\.]?\s*(\d{3})\s*[-\/\.]?\s*(\d{4})\s*(([xX]|[eE][xX][tT])\.?\s*(\d+))*$/; 
			//if it's valid email
			if(filter.test(a)){
				isvalidemailflag = 0;
			}else{
				isvalidemailflag = 1;	
			}
		}
		if(isvalidemailflag)
		{
			user_number.addClass("error");
			user_numberInfo.text("Please Enter valid Contact Number");
			user_numberInfo.addClass("message_error2");
			return false;
		}else
		{
			user_number.removeClass("error");
			user_numberInfo.text("");
			user_numberInfo.removeClass("message_error");
			return true;
		}
	} */

	function validate_user_position()
	{
		if(user_position.val() == '')
		{
			user_position.addClass("error");
			user_positionInfo.text("Please Enter Your Position In The Business");
			user_positionInfo.addClass("message_error2");
			return false;
		}
		else{
			user_position.removeClass("error");
			user_positionInfo.text("");
			user_positionInfo.removeClass("message_error2");
			return true;
		}
	}

	
	function validate_user_comments()
	{
		if(user_comments.val() == '')
		{
			user_comments.addClass("error");
			user_commentsInfo.text("Please Enter Comments");
			user_commentsInfo.addClass("message_error2");
			return false;
		}
		else{
			user_comments.removeClass("error");
			user_commentsInfo.text("");
			user_commentsInfo.removeClass("message_error2");
			return true;
		}
	}

});


