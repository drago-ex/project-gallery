const modeName = 'gallery-copy-mode';


/**
 * Copies the path of a gallery image (the data-gallery-copy attribute of its button) to the clipboard, or its
 * Texy markup [* path *], as the switch of the gallery is set; the browser remembers the choice. One listener
 * on the document, so images added by a snippet need no initialization.
 */
export default class GalleryCopy {
	initialize(naja) {
		const apply = () => {
			const mode = readMode();
			for (const input of document.querySelectorAll(`[name="${modeName}"]`)) {
				input.checked = input.value === mode;
			}
		};

		// on the page load and when a snippet brings the switch (e.g. the page opened by AJAX)
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', apply);
		} else {
			apply();
		}

		naja.snippetHandler.addEventListener('afterUpdate', apply);

		document.addEventListener('change', (event) => {
			if (event.target.name === modeName) {
				saveMode(event.target.value);
			}
		});

		document.addEventListener('click', async (event) => {
			const button = event.target.closest('[data-gallery-copy]');
			if (!button) {
				return;
			}

			const path = button.dataset.galleryCopy;
			const mode = document.querySelector(`[name="${modeName}"]:checked`)?.value ?? 'texy';
			const text = mode === 'path' ? path : `[* ${path} *]`;
			try {
				await navigator.clipboard.writeText(text);
			} catch {
				// the clipboard is missing or refused outside HTTPS and localhost: let the user copy it by hand
				window.prompt('', text);
				return;
			}

			const icon = button.querySelector('i');
			icon.className = 'fa-solid fa-check';
			setTimeout(() => icon.className = 'fa-regular fa-copy', 1500);
		});
	}
}


// the storage can be missing or blocked (private mode, blocked site data): the switch then starts at Texy
function readMode() {
	try {
		return localStorage.getItem(modeName) ?? 'texy';
	} catch {
		return 'texy';
	}
}


function saveMode(mode) {
	try {
		localStorage.setItem(modeName, mode);
	} catch {
		// not remembered, the switch still works on this page
	}
}
