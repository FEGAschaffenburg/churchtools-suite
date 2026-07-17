document.addEventListener('DOMContentLoaded', () => {
	const containers = document.querySelectorAll('.view-features');
	if (!containers.length) {
		return;
	}

	containers.forEach((container) => {
		container.classList.add('is-accordion');

		const items = container.querySelectorAll('.feature-item');
		items.forEach((item) => {
			const titleEl = item.querySelector('strong');
			if (!titleEl) {
				return;
			}

			const button = document.createElement('button');
			button.type = 'button';
			button.className = 'feature-toggle';
			button.setAttribute('aria-expanded', 'false');
			button.textContent = titleEl.textContent.trim();

			const body = document.createElement('div');
			body.className = 'feature-body';

			let nextNode = titleEl.nextSibling;
			while (nextNode) {
				const current = nextNode;
				nextNode = nextNode.nextSibling;
				body.appendChild(current);
			}

			titleEl.remove();
			item.prepend(button);
			item.appendChild(body);

			button.addEventListener('click', () => {
				const isOpen = item.classList.toggle('is-open');
				button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		});
	});
});
