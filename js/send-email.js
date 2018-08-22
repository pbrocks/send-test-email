// Calls the modal-select-location.php
jQuery(document).ready(function($) {
	$('#send_email_submit').click(function(event) {
		event.preventDefault();
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: {
				// Variables defined from form 
		      	serial: $('#send-email-form').serialize(),
		      	post_type: $('#post_type').val(),
		      	send_emails: $('input[name=send-emails]').val(),
		      	send_to_email   : $('input[name=send_to_email]').val(),
		      	userid: $('#userid').val(),
		      	action: 'send_email_get_results',
		      	send_email_nonce: send_email_object.send_email_nonce,

		      	// Admin stuff
				script_name   : 'send-emails.js',
		      	nonce  : send_email_object.send_email_nonce,
			},
			success:function( data ) {
				$('#send_email_results').html(data);
				$('#send_email_loading').hide();
				$('#send_email_submit').attr('disabled', false);
				// console.log(data);
			},
			error: function( jqXHR, textStatus, errorThrown ){
				console.log(errorThrown);
			}
		});
	});
});