	jQuery(document).ready(function($) {
		$( '#slider_filled' ).sliderCaptcha({
			type: "filled",
			textFeedbackAnimation: 'swipe_overlap',
			hintText: captcha_link_class.hintText,
			hintTextSize: '12',
			hintTextAfterUnlock: captcha_link_class.hintTextAfterUnlock,
			
			
			
			events: {
				afterUnlock: function () {
					
					
				},
				beforeUnlock: function () {
					
				},
				beforeSubmit: function () {
					
				},
				noSubmit: function() {
					
				},				
				submitAfterUnlock: 0,
				validateOnServer: 0,
				validateOnServerParamName: "my_form_param_name"
			}
		});
		
		$( "#slider_filled" ).on( "dragstop", function( event, ui ) {
			
			if (event.originalEvent === undefined) {
				jQuery('#ondrage').val(" ");
			} else {
				jQuery('#ondrage').val(captcha_link_class.ondrage_number);
			}
		} );
		
	});
	