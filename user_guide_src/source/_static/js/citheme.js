/*
 * Add classes to the body of each page of the documentation.
 *
 * From: [...]/outgoing/api_responses.html
 * Into: ci-outgoing ci-api-responses
 */
window.onload = function() {
	const regexUrl   = new RegExp(/([a-z0-9_.-]+)\/([a-z0-9_.-]+)\.html/);
	const currentUrl = window.location.href;

	let urlMatch;

	if ((urlMatch = regexUrl.exec(currentUrl)) !== null) {
		if (urlMatch.length === 3) {
			let parent  = sanitizeClass(urlMatch[1]);
			let current = sanitizeClass(urlMatch[2]);

			if (parent === 'html' || parent.length === 0) {
				parent = 'userguide';
			}

			addClass(parent);
			addClass(current);
		}
	}
}

/**
 * Sanitize the string - removing invalid characters
 *
 * @param {string} value Value to be sanitized
 *
 * @return {string}
 */
sanitizeClass = function(value) {
	return value.replace(/_/g, '-').replace(/[^a-z0-9-]/g, '');
}

/**
 * Add class to the document body
 *
 * @param {string} value  Value to be added
 * @param {string} prefix Prefix to be added to all classnames
 *
 * @return {void}
 */
addClass = function(value, prefix) {
	prefix = prefix || 'ci-';

	if(value.length > 0) {
		document.body.classList.add(prefix + value);
	}
}
