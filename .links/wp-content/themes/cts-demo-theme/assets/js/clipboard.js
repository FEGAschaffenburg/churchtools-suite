/**
 * Clipboard functionality for code blocks
 * Provides copy-to-clipboard for demo shortcodes
 *
 * @package CTS_Demo_Theme
 * @since 1.0.0
 */

(function() {
	'use strict';

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		initCopyButtons();
	}

	/**
	 * Initialize all copy buttons
	 */
	function initCopyButtons() {
		const copyButtons = document.querySelectorAll('.copy-button');
		
		if (!copyButtons.length) {
			return;
		}

		copyButtons.forEach(button => {
			button.addEventListener('click', handleCopyClick);
		});
	}

	/**
	 * Handle copy button click
	 * @param {Event} e Click event
	 */
	function handleCopyClick(e) {
		e.preventDefault();
		
		const button = e.currentTarget;
		const codeBlock = button.closest('.code-block').querySelector('code');
		
		if (!codeBlock) {
			showError(button, 'Code nicht gefunden');
			return;
		}

		const code = codeBlock.textContent || codeBlock.innerText;
		
		// Try modern Clipboard API first
		if (navigator.clipboard && navigator.clipboard.writeText) {
			copyToClipboardModern(code, button);
		} else {
			// Fallback for older browsers
			copyToClipboardFallback(code, button);
		}
	}

	/**
	 * Copy using modern Clipboard API
	 * @param {string} text Text to copy
	 * @param {HTMLElement} button Copy button element
	 */
	function copyToClipboardModern(text, button) {
		navigator.clipboard.writeText(text)
			.then(() => {
				showSuccess(button);
			})
			.catch(err => {
				console.error('Copy failed:', err);
				showError(button, 'Kopieren fehlgeschlagen');
			});
	}

	/**
	 * Copy using fallback method (execCommand)
	 * @param {string} text Text to copy
	 * @param {HTMLElement} button Copy button element
	 */
	function copyToClipboardFallback(text, button) {
		const textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.opacity = '0';
		textarea.style.pointerEvents = 'none';
		
		document.body.appendChild(textarea);
		textarea.select();
		
		try {
			const successful = document.execCommand('copy');
			
			if (successful) {
				showSuccess(button);
			} else {
				showError(button, 'Kopieren fehlgeschlagen');
			}
		} catch (err) {
			console.error('Copy failed:', err);
			showError(button, 'Kopieren fehlgeschlagen');
		} finally {
			document.body.removeChild(textarea);
		}
	}

	/**
	 * Show success state
	 * @param {HTMLElement} button Copy button element
	 */
	function showSuccess(button) {
		const originalText = button.textContent;
		
		button.classList.add('copied');
		button.textContent = '✓ Kopiert!';
		button.disabled = true;
		
		setTimeout(() => {
			button.classList.remove('copied');
			button.textContent = originalText;
			button.disabled = false;
		}, 2000);
	}

	/**
	 * Show error state
	 * @param {HTMLElement} button Copy button element
	 * @param {string} message Error message
	 */
	function showError(button, message) {
		const originalText = button.textContent;
		
		button.classList.add('error');
		button.textContent = '✗ ' + message;
		
		setTimeout(() => {
			button.classList.remove('error');
			button.textContent = originalText;
		}, 2000);
	}

	/**
	 * Utility: Select all text in code block on triple-click
	 */
	document.addEventListener('click', function(e) {
		if (e.detail === 3) { // Triple click
			const codeBlock = e.target.closest('pre code');
			
			if (codeBlock) {
				selectText(codeBlock);
			}
		}
	});

	/**
	 * Select text in element
	 * @param {HTMLElement} element Element to select
	 */
	function selectText(element) {
		if (document.selection) {
			const range = document.body.createTextRange();
			range.moveToElementText(element);
			range.select();
		} else if (window.getSelection) {
			const range = document.createRange();
			range.selectNode(element);
			window.getSelection().removeAllRanges();
			window.getSelection().addRange(range);
		}
	}

})();
