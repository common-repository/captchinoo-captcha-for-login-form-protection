
	jQuery(window).ready(function($) {
        $('.captcha-holder').iconCaptcha({
            theme: ['light', 'dark'], // Select the theme(s) of the Captcha(s). Available: light, dark
            fontFamily: '', // Change the font family of the captcha. Leaving it blank will add the default font to the end of the <body> tag.
            clickDelay: 500, // The delay during which the user can't select an image.
            invalidResetDelay: 3000, // After how many milliseconds the captcha should reset after a wrong icon selection.
            requestIconsDelay: 1500, // How long should the script wait before requesting the hashes and icons? (to prevent a high(er) CPU usage during a DDoS attack)
            loadingAnimationDelay: 1500, // How long the fake loading animation should play.
            hoverDetection: true, // Enable or disable the cursor hover detection.
			showCredits: 'hide', // Show, hide or disable the credits element. Valid values: 'show', 'hide', 'disabled' (please leave it enabled).
            enableLoadingAnimation: true, // Enable of disable the fake loading animation. Doesn't actually do anything other than look nice.
            validationPath: captcha_link_class.captcha_link, // The path to the Captcha validation file.
            validationPaths: captcha_link_class.captcha_links, // The path to the Captcha validation file.
            messages: { // You can put whatever message you want in the captcha.
                header: captcha_link_class.header,
                    correct: {
                        top: captcha_link_class.correct_top,
                        bottom: captcha_link_class.correct_bottom
                    },
                    incorrect: {
                        top: captcha_link_class.incorrect_top,
                        bottom: captcha_link_class.incorrect_bottom,
                    }
                }
            })
            .bind('init.iconCaptcha', function(e, id) { // You can bind to custom events, in case you want to execute some custom code.
                $("#wp-submit").attr("disabled", true);
            }).bind('selected.iconCaptcha', function(e, id) {
               
            }).bind('refreshed.iconCaptcha', function(e, id) {
                
            }).bind('success.iconCaptcha', function(e, id) {
                $("#wp-submit").attr("disabled", false);
				
            }).bind('error.iconCaptcha', function(e, id) {

            });
        });