jQuery(function($) {
	const mainToggle = $('#ct_posts_sync_enabled');
	if (mainToggle.length) {
		const relatedControls = $('.cts-posts-sync-field, [name="ct_posts_only_my_groups"], [name="ct_posts_include_comments"], [name="ct_posts_include_linkings"], [name="ct_posts_include_reactions"], button[name="ct_posts_sync_groups_now"]');

		function syncReportsFieldsState() {
			const enabled = mainToggle.is(':checked');
			relatedControls.each(function() {
				const field = $(this);
				if (field.is('button')) {
					field.prop('disabled', !enabled);
					return;
				}
				field.prop('disabled', !enabled);
			});
		}

		mainToggle.on('change', syncReportsFieldsState);
		syncReportsFieldsState();
	}

	const runSyncButton = $('#cts-posts-overview-run-sync[data-action="run-sync"]');
	if (!runSyncButton.length) {
		return;
	}

	const resultBox = $('#cts-posts-overview-result');

	function showResult(success, message) {
		resultBox
			.removeClass('notice-success notice-error')
			.addClass('notice ' + (success ? 'notice-success' : 'notice-error'))
			.html('<p>' + message + '</p>')
			.show();
	}

	runSyncButton.on('click', function(e) {
		e.preventDefault();

		$.post(ajaxurl, {
			action: 'cts_posts_sync_run_now',
			nonce: (window.ctsPostsSyncAdmin && window.ctsPostsSyncAdmin.nonce) ? window.ctsPostsSyncAdmin.nonce : ''
		})
			.done(function(response) {
				if (response && response.success) {
					const message = response.data && response.data.message ? response.data.message : 'OK';
					showResult(true, message);
					setTimeout(function() { location.reload(); }, 1200);
					return;
				}

				const fallback = window.ctsPostsSyncAdmin && window.ctsPostsSyncAdmin.messages ? window.ctsPostsSyncAdmin.messages.syncError : 'Fehler beim Synchronisieren.';
				const message = (response && response.data && response.data.message) ? response.data.message : fallback;
				showResult(false, message);
			})
			.fail(function() {
				const fallback = window.ctsPostsSyncAdmin && window.ctsPostsSyncAdmin.messages ? window.ctsPostsSyncAdmin.messages.networkError : 'Netzwerkfehler beim Synchronisieren.';
				showResult(false, fallback);
			});
	});
});
