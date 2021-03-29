/*
 * Add classes to the body of each page of the documentation.
 *
 * From: [...]/outgoing/api_responses.html
 * Into: ci-outgoing ci-api-responses
 */
window.onload = function() {
	// Regular expression for finding chapter and subject in the current url
	const regexUrl = new RegExp(/\/([a-z0-9_.-]+)\/([a-z0-9_.-]+)\.html/);

	// Get the current url
	const currentUrl = window.location.href;

	// Get the document body
	const documentBody = document.body;

	// Placeholder for documentation index
	var index = null;

	if ((index = regexUrl.exec(currentUrl)) !== null)
	{
		// Sanitize the documentation chapter and subject
		var chapter = sanitizeClass(index[1]);
		var subject = sanitizeClass(index[2]);

		// Documentation are generated into an html-folder for developers.
		// This aren't a valid chapter. We are on documentation index.
		if (chapter === 'html')
		{
			index = null;
		}
		// Add chapter and subject className(s) to the document body
		else
		{
			addClass(documentBody, chapter);
			addClass(documentBody, subject);
		}
	}

	// No chapter and subject found. We are on documentation index.
	if (index === null)
	{
		addClass(documentBody, 'documentation');
		addClass(documentBody, 'index');
	}
}

/**
 * Sanitize the string - removing invalid characters
 *
 * @param {string} className className to be sanitized
 *
 * @return {string}
 */
sanitizeClass = function(className) {
	return className.replace(/_/g, '-').replace(/[^a-z0-9-]/g, '');
}

/**
 * Add class to HTML DOM Element Object
 *
 * @param {object} el         The HTML DOM Element Object
 * @param {string} className  className to be added
 * @param {string} namePrefix namePrefix to be added to className
 *
 * @return {void}
 */
addClass = function(el, className, namePrefix) {
	namePrefix = namePrefix || 'ci-';

	if (el.classList && className.length > 0)
	{
		el.classList.add(namePrefix + className);
	}
}
