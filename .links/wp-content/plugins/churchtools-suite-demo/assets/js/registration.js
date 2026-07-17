/**
 * Demo Registration Form JavaScript
 *
 * @package ChurchTools_Suite_Demo
 * @since   1.0.0
 */

(function($) {
	'use strict';
	
	$(document).ready(function() {
		console.log('[CTS Demo Registration] Script loaded');
		
		const $form = $('#cts-demo-registration-form');
		const $submitBtn = $form.find('.cts-submit-btn');
		const $btnText = $submitBtn.find('.btn-text');
		const $btnSpinner = $submitBtn.find('.btn-spinner');
		const $message = $form.find('.cts-form-message');
		
		console.log('[CTS Demo Registration] Form found:', $form.length > 0);
		console.log('[CTS Demo Registration] AJAX URL:', ctsDemo.ajaxUrl);
		
		$form.on('submit', function(e) {
			console.log('[CTS Demo Registration] Form submitted');
			e.preventDefault();
			
			// Clear previous message
			$message.hide().removeClass('success error');
			
			// Validate privacy checkbox
			if (!$('#cts-demo-privacy').is(':checked')) {
				showMessage('error', 'Bitte akzeptieren Sie die Datenschutzerklärung.');
				return;
			}
			
			// Disable submit button
			$submitBtn.prop('disabled', true);
			$btnText.hide();
			$btnSpinner.show();
			
			// Prepare data
			const formData = {
				action: 'cts_demo_register',
				nonce: ctsDemo.nonce,
				email: $('#cts-demo-email').val(),
				first_name: $('#cts-demo-first-name').val(),
				last_name: $('#cts-demo-last-name').val(),
				password: $('#cts-demo-password').val(),
				password_confirm: $('#cts-demo-password-confirm').val(),
				company: $('#cts-demo-company').val(),
				purpose: $('#cts-demo-purpose').val(),
				privacy_accepted: $('#cts-demo-privacy').is(':checked') ? 1 : 0
			};
			
			// Submit via AJAX
			console.log('[CTS Demo Registration] Sending AJAX request...');
			console.log('[CTS Demo Registration] Form data:', formData);
			
			$.ajax({
				url: ctsDemo.ajaxUrl,
				method: 'POST',
				data: formData,
				beforeSend: function() {
					console.log('[CTS Demo Registration] AJAX beforeSend');
				},
				success: function(response) {
					console.log('[CTS Demo Registration] AJAX success - Raw response:', response);
					console.log('[CTS Demo Registration] Response type:', typeof response);
					
					if (response.success) {
						showMessage('success', response.data.message);
						$form[0].reset();
						
						// Auto-redirect to dashboard after successful registration
						if (response.data.redirect) {
							console.log('Redirecting to:', response.data.redirect);
							
							// Show countdown
							let countdown = 3;
							const countdownInterval = setInterval(function() {
								countdown--;
								if (countdown > 0) {
									showMessage('success', response.data.message + ' Weiterleitung in ' + countdown + '...');
								} else {
									clearInterval(countdownInterval);
									showMessage('success', 'Weiterleitung...');
									console.log('Executing redirect now');
									window.location.href = response.data.redirect;
								}
							}, 1000);
						}
						
						// Scroll to message
						$('html, body').animate({
							scrollTop: $message.offset().top - 100
						}, 500);
					} else {
						showMessage('error', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					console.error('[CTS Demo Registration] AJAX error');
					console.error('[CTS Demo Registration] Status:', status);
					console.error('[CTS Demo Registration] Error:', error);
					console.error('[CTS Demo Registration] XHR:', xhr);
					console.error('[CTS Demo Registration] Response text:', xhr.responseText);
					
					showMessage('error', 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
					console.error('AJAX Error:', error);
				},
				complete: function() {
					// Re-enable submit button
					$submitBtn.prop('disabled', false);
					$btnText.show();
					$btnSpinner.hide();
				}
			});
		});
		
		/**
		 * Show message
		 *
		 * @param {string} type - 'success' or 'error'
		 * @param {string} text - Message text
		 */
		function showMessage(type, text) {
			$message
				.removeClass('success error')
				.addClass(type)
				.html(text)
				.fadeIn(300);
		}
	});
	
})(jQuery);
