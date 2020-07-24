/*
 * Functionality for the CodeIgniter Debug Toolbar.
 */

var ciDebugBar = {

	toolbarContainer : null,
	toolbar : null,
	icon : null,

	//--------------------------------------------------------------------

	init : function () {
		this.toolbarContainer = document.getElementById('toolbarContainer');
		this.toolbar = document.getElementById('debug-bar');
		this.icon    = document.getElementById('debug-icon');

		ciDebugBar.createListeners();
		ciDebugBar.setToolbarState();
		ciDebugBar.setToolbarPosition();
		ciDebugBar.setToolbarTheme();
		ciDebugBar.toggleViewsHints();
		ciDebugBar.routerLink();

		document.getElementById('debug-bar-link').addEventListener('click', ciDebugBar.toggleToolbar, true);
		document.getElementById('debug-icon-link').addEventListener('click', ciDebugBar.toggleToolbar, true);

		// Allows to highlight the row of the current history request
		var btn = document.querySelector('button[data-time="' + localStorage.getItem('debugbar-time') + '"]');
		ciDebugBar.addClass(btn.parentNode.parentNode, 'current');

		historyLoad = document.getElementsByClassName('ci-history-load');

		for (var i = 0; i < historyLoad.length; i++)
		{
			historyLoad[i].addEventListener('click', function () {
				loadDoc(this.getAttribute('data-time'));
			}, true);
		}

		// Display the active Tab on page load
		var tab = ciDebugBar.readCookie('debug-bar-tab');
		if (document.getElementById(tab))
		{
			var el           = document.getElementById(tab);
			el.style.display = 'block';
			ciDebugBar.addClass(el, 'active');
			tab = document.querySelector('[data-tab=' + tab + ']');
			if (tab)
			{
				ciDebugBar.addClass(tab.parentNode, 'active');
			}
		}
	},

	//--------------------------------------------------------------------

	createListeners : function () {
		var buttons = [].slice.call(document.querySelectorAll('#debug-bar .ci-label a'));

		for (var i = 0; i < buttons.length; i++)
		{
			buttons[i].addEventListener('click', ciDebugBar.showTab, true);
		}
	},

	//--------------------------------------------------------------------

	showTab: function () {
		// Get the target tab, if any
		var tab = document.getElementById(this.getAttribute('data-tab'));

		// If the label have not a tab stops here
		if (! tab)
		{
			return;
		}

		// Remove debug-bar-tab cookie
		ciDebugBar.createCookie('debug-bar-tab', '', -1);

		// Check our current state.
		var state = tab.style.display;

		// Hide all tabs
		var tabs = document.querySelectorAll('#debug-bar .tab');

		for (var i = 0; i < tabs.length; i++)
		{
			tabs[i].style.display = 'none';
		}

		// Mark all labels as inactive
		var labels = document.querySelectorAll('#debug-bar .ci-label');

		for (var i = 0; i < labels.length; i++)
		{
			ciDebugBar.removeClass(labels[i], 'active');
		}

		// Show/hide the selected tab
		if (state != 'block')
		{
			tab.style.display = 'block';
			ciDebugBar.addClass(this.parentNode, 'active');
			// Create debug-bar-tab cookie to persistent state
			ciDebugBar.createCookie('debug-bar-tab', this.getAttribute('data-tab'), 365);
		}
	},

	//--------------------------------------------------------------------

	addClass : function (el, className) {
		if (el.classList)
		{
			el.classList.add(className);
		}
		else
		{
			el.className += ' ' + className;
		}
	},

	//--------------------------------------------------------------------

	removeClass : function (el, className) {
		if (el.classList)
		{
			el.classList.remove(className);
		}
		else
		{
			el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
		}
	},

	//--------------------------------------------------------------------

	/**
	 * Toggle display of a data table
	 *
	 * @param obj
	 */
	toggleDataTable : function (obj) {
		if (typeof obj == 'string')
		{
			obj = document.getElementById(obj + '_table');
		}

		if (obj)
		{
			obj.style.display = obj.style.display == 'none' ? 'block' : 'none';
		}
	},

	//--------------------------------------------------------------------

	/**
	 *   Toggle tool bar from full to icon and icon to full
	 */
	toggleToolbar : function () {
		var open = ciDebugBar.toolbar.style.display != 'none';

		ciDebugBar.icon.style.display    = open == true ? 'inline-block' : 'none';
		ciDebugBar.toolbar.style.display = open == false ? 'inline-block' : 'none';

		// Remember it for other page loads on this site
		ciDebugBar.createCookie('debug-bar-state', '', -1);
		ciDebugBar.createCookie('debug-bar-state', open == true ? 'minimized' : 'open' , 365);
	},

	//--------------------------------------------------------------------

	/**
	 * Sets the initial state of the toolbar (open or minimized) when
	 * the page is first loaded to allow it to remember the state between refreshes.
	 */
	setToolbarState: function () {
		var open = ciDebugBar.readCookie('debug-bar-state');

		ciDebugBar.icon.style.display    = open != 'open' ? 'inline-block' : 'none';
		ciDebugBar.toolbar.style.display = open == 'open' ? 'inline-block' : 'none';
	},

	//--------------------------------------------------------------------

	toggleViewsHints: function () {
		// Avoid toggle hints on history requests that are not the initial
		if (localStorage.getItem('debugbar-time') != localStorage.getItem('debugbar-time-new'))
		{
			var a  = document.querySelector('a[data-tab="ci-views"]');
			a.href = '#';
			return;
		}

		var nodeList       = []; // [ Element, NewElement( 1 )/OldElement( 0 ) ]
		var sortedComments = [];
		var comments       = [];

		var getComments = function () {
			var nodes        = [];
			var result       = [];
			var xpathResults = document.evaluate( "//comment()[starts-with(., ' DEBUG-VIEW')]", document, null, XPathResult.ANY_TYPE, null);
			var nextNode     = xpathResults.iterateNext();
			while ( nextNode )
			{
				nodes.push( nextNode );
				nextNode = xpathResults.iterateNext();
			}

			// sort comment by opening and closing tags
			for (var i = 0; i < nodes.length; ++i)
			{
				// get file path + name to use as key
				var path = nodes[i].nodeValue.substring( 18, nodes[i].nodeValue.length - 1 );

				if ( nodes[i].nodeValue[12] === 'S' ) // simple check for start comment
				{
					// create new entry
					result[path] = [ nodes[i], null ];
				}
				else if (result[path])
				{
					// add to existing entry
					result[path][1] = nodes[i];
				}
			}

			return result;
		};

		// find node that has TargetNode as parentNode
		var getParentNode = function ( node, targetNode ) {
			if ( node.parentNode === null )
			{
				return null;
			}

			if ( node.parentNode !== targetNode )
			{
				return getParentNode( node.parentNode, targetNode );
			}

			return node;
		};

		// define invalid & outer ( also invalid ) elements
		const INVALID_ELEMENTS = [ 'NOSCRIPT', 'SCRIPT', 'STYLE' ];
		const OUTER_ELEMENTS   = [ 'HTML', 'BODY', 'HEAD' ];

		var getValidElementInner = function ( node, reverse ) {
			// handle invalid tags
			if ( OUTER_ELEMENTS.indexOf( node.nodeName ) !== -1 )
			{
				for (var i = 0; i < document.body.children.length; ++i)
				{
					var index   = reverse ? document.body.children.length - ( i + 1 ) : i;
					var element = document.body.children[index];

					// skip invalid tags
					if ( INVALID_ELEMENTS.indexOf( element.nodeName ) !== -1 )
					{
						continue;
					}

					return [ element, reverse ];
				}

				return null;
			}

			// get to next valid element
			while ( node !== null && INVALID_ELEMENTS.indexOf( node.nodeName ) !== -1 )
			{
				node = reverse ? node.previousElementSibling : node.nextElementSibling;
			}

			// return non array if we couldnt find something
			if ( node === null )
			{
				return null;
			}

			return [ node, reverse ];
		};

		// get next valid element ( to be safe to add divs )
		// @return [ element, skip element ] or null if we couldnt find a valid place
		var getValidElement = function ( nodeElement ) {
			if (nodeElement)
			{
				if ( nodeElement.nextElementSibling !== null )
				{
					return getValidElementInner( nodeElement.nextElementSibling, false )
						|| getValidElementInner( nodeElement.previousElementSibling, true );
				}
				if ( nodeElement.previousElementSibling !== null )
				{
					return getValidElementInner( nodeElement.previousElementSibling, true );
				}
			}

			// something went wrong! -> element is not in DOM
			return null;
		};

		function showHints()
		{
			// Had AJAX? Reset view blocks
			sortedComments = getComments();

			for (var key in sortedComments)
			{
				var startElement = getValidElement( sortedComments[key][0] );
				var endElement   = getValidElement( sortedComments[key][1] );

				// skip if we couldnt get a valid element
				if ( startElement === null || endElement === null )
				{
					continue;
				}

				// find element which has same parent as startelement
				var jointParent = getParentNode( endElement[0], startElement[0].parentNode );
				if ( jointParent === null )
				{
					// find element which has same parent as endelement
					jointParent = getParentNode( startElement[0], endElement[0].parentNode );
					if ( jointParent === null )
					{
						// both tries failed
						continue;
					}
					else
					{
						startElement[0] = jointParent;
					}
				}
				else
				{
					endElement[0] = jointParent;
				}

				var debugDiv   = document.createElement( 'div' ); // holder
				var debugPath  = document.createElement( 'div' ); // path
				var childArray = startElement[0].parentNode.childNodes; // target child array
				var parent     = startElement[0].parentNode;
				var start, end;

				// setup container
				debugDiv.classList.add( 'debug-view' );
				debugDiv.classList.add( 'show-view' );
				debugPath.classList.add( 'debug-view-path' );
				debugPath.innerText = key;
				debugDiv.appendChild( debugPath );

				// calc distance between them
				// start
				for (var i = 0; i < childArray.length; ++i)
				{
					// check for comment ( start & end ) -> if its before valid start element
					if ( childArray[i] === sortedComments[key][1] ||
						childArray[i] === sortedComments[key][0] ||
						childArray[i] === startElement[0] )
					{
						start = i;
						if ( childArray[i] === sortedComments[key][0] )
						{
							start++; // increase to skip the start comment
						}
						break;
					}
				}
				// adjust if we want to skip the start element
				if ( startElement[1] )
				{
					start++;
				}

				// end
				for (var i = start; i < childArray.length; ++i)
				{
					if ( childArray[i] === endElement[0] )
					{
						end = i;
						// dont break to check for end comment after end valid element
					}
					else if ( childArray[i] === sortedComments[key][1] )
					{
						// if we found the end comment, we can break
						end = i;
						break;
					}
				}

				// move elements
				var number = end - start;
				if ( endElement[1] )
				{
					number++;
				}
				for (var i = 0; i < number; ++i)
				{
					if ( INVALID_ELEMENTS.indexOf( childArray[start] ) !== -1 )
					{
						// skip invalid childs that can cause problems if moved
						start++;
						continue;
					}
					debugDiv.appendChild( childArray[start] );
				}

				// add container to DOM
				nodeList.push( parent.insertBefore( debugDiv, childArray[start] ) );
			}

			ciDebugBar.createCookie('debug-view', 'show', 365);
			ciDebugBar.addClass(btn, 'active');
		}

		function hideHints()
		{
			for (var i = 0; i < nodeList.length; ++i)
			{
				var index;

				// find index
				for (var j = 0; j < nodeList[i].parentNode.childNodes.length; ++j)
				{
					if ( nodeList[i].parentNode.childNodes[j] === nodeList[i] )
					{
						index = j;
						break;
					}
				}

				// move child back
				while ( nodeList[i].childNodes.length !== 1 )
				{
					nodeList[i].parentNode.insertBefore( nodeList[i].childNodes[1], nodeList[i].parentNode.childNodes[index].nextSibling  );
					index++;
				}

				nodeList[i].parentNode.removeChild( nodeList[i] );
			}
			nodeList.length = 0;

			ciDebugBar.createCookie('debug-view', '', -1);
			ciDebugBar.removeClass(btn, 'active');
		}

		var btn = document.querySelector('[data-tab=ci-views]');

		// If the Views Collector is inactive stops here
		if (! btn)
		{
			return;
		}

		btn.parentNode.onclick = function () {
			if (ciDebugBar.readCookie('debug-view'))
			{
				hideHints();
			}
			else
			{
				showHints();
			}
		};

		// Determine Hints state on page load
		if (ciDebugBar.readCookie('debug-view'))
		{
			showHints();
		}
	},

	//--------------------------------------------------------------------

	setToolbarPosition: function () {
		var btnPosition = document.getElementById('toolbar-position');

		if (ciDebugBar.readCookie('debug-bar-position') === 'top')
		{
			ciDebugBar.addClass(ciDebugBar.icon, 'fixed-top');
			ciDebugBar.addClass(ciDebugBar.toolbar, 'fixed-top');
		}

		btnPosition.addEventListener('click', function () {
			var position = ciDebugBar.readCookie('debug-bar-position');

			ciDebugBar.createCookie('debug-bar-position', '', -1);

			if (!position || position === 'bottom')
			{
				ciDebugBar.createCookie('debug-bar-position', 'top', 365);
				ciDebugBar.addClass(ciDebugBar.icon, 'fixed-top');
				ciDebugBar.addClass(ciDebugBar.toolbar, 'fixed-top');
			}
			else
			{
				ciDebugBar.createCookie('debug-bar-position', 'bottom', 365);
				ciDebugBar.removeClass(ciDebugBar.icon, 'fixed-top');
				ciDebugBar.removeClass(ciDebugBar.toolbar, 'fixed-top');
			}
		}, true);
	},

	//--------------------------------------------------------------------

	setToolbarTheme: function () {
		var btnTheme = document.getElementById('toolbar-theme');
		var isDarkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;
	  var isLightMode = window.matchMedia("(prefers-color-scheme: light)").matches;

		// If a cookie is set with a value, we force the color scheme
		if (ciDebugBar.readCookie('debug-bar-theme') === 'dark')
		{
			ciDebugBar.removeClass(ciDebugBar.toolbarContainer, 'light');
			ciDebugBar.addClass(ciDebugBar.toolbarContainer, 'dark');
		}
		else if (ciDebugBar.readCookie('debug-bar-theme') === 'light')
		{
			ciDebugBar.removeClass(ciDebugBar.toolbarContainer, 'dark');
			ciDebugBar.addClass(ciDebugBar.toolbarContainer, 'light');
		}

		btnTheme.addEventListener('click', function () {
			var theme = ciDebugBar.readCookie('debug-bar-theme');

			if (!theme && window.matchMedia("(prefers-color-scheme: dark)").matches)
			{
				// If there is no cookie, and "prefers-color-scheme" is set to "dark"
				// It means that the user wants to switch to light mode
				ciDebugBar.createCookie('debug-bar-theme', 'light', 365);
				ciDebugBar.removeClass(ciDebugBar.toolbarContainer, 'dark');
				ciDebugBar.addClass(ciDebugBar.toolbarContainer, 'light');
			}
			else
			{
				if (theme === 'dark')
				{
					ciDebugBar.createCookie('debug-bar-theme', 'light', 365);
					ciDebugBar.removeClass(ciDebugBar.toolbarContainer, 'dark');
					ciDebugBar.addClass(ciDebugBar.toolbarContainer, 'light');
				}
				else
				{
					// In any other cases: if there is no cookie, or the cookie is set to
					// "light", or the "prefers-color-scheme" is "light"...
					ciDebugBar.createCookie('debug-bar-theme', 'dark', 365);
					ciDebugBar.removeClass(ciDebugBar.toolbarContainer, 'light');
					ciDebugBar.addClass(ciDebugBar.toolbarContainer, 'dark');
				}
			}
		}, true);
	},

	//--------------------------------------------------------------------

	/**
	 * Helper to create a cookie.
	 *
	 * @param name
	 * @param value
	 * @param days
	 */
	createCookie : function (name,value,days) {
		if (days)
		{
			var date = new Date();

			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));

			var expires = "; expires=" + date.toGMTString();
		}
		else
		{
			var expires = "";
		}

		document.cookie = name + "=" + value + expires + "; path=/";
	},

	//--------------------------------------------------------------------

	readCookie : function (name) {
		var nameEQ = name + "=";
		var ca     = document.cookie.split(';');

		for (var i = 0; i < ca.length; i++)
		{
			var c = ca[i];
			while (c.charAt(0) == ' ')
			{
				c = c.substring(1,c.length);
			}
			if (c.indexOf(nameEQ) == 0)
			{
				return c.substring(nameEQ.length,c.length);
			}
		}
		return null;
	},

	//--------------------------------------------------------------------

	trimSlash: function(text) {
		return text.replace(/^\/|\/$/g, '');
	},

	routerLink: function() {
		var row, _location;
		var rowGet = document.querySelectorAll('#debug-bar td[data-debugbar-route="GET"]');
		var patt = /\((?:[^)(]+|\((?:[^)(]+|\([^)(]*\))*\))*\)/;

		for (var i = 0; i < rowGet.length; i++) {
			row = rowGet[i];
			if (!/\/\(.+?\)/.test(rowGet[i].innerText)) {
				row.style = 'cursor: pointer;';
				row.setAttribute('title', location.origin + '/' + ciDebugBar.trimSlash(row.innerText));
				row.addEventListener('click', function(ev) {
					_location = location.origin + '/' + ciDebugBar.trimSlash(ev.target.innerText);
					var redirectWindow = window.open(_location, '_blank');
					redirectWindow.location;
				});
			}
			else {
				row.innerHTML = '<div>' + row.innerText + '</div>'
					+ '<form data-debugbar-route-tpl="' + ciDebugBar.trimSlash(row.innerText.replace(patt, '?')) + '">'
					+ row.innerText.replace(patt, '<input type="text" placeholder="$1">')
					+ '<input type="submit" value="Go" style="margin-left: 4px;">'
					+ '</form>';
			}
		}

		rowGet = document.querySelectorAll('#debug-bar td[data-debugbar-route="GET"] form');
		for (var i = 0; i < rowGet.length; i++) {
			row = rowGet[i];

			row.addEventListener('submit', function(event) {
				event.preventDefault()
				var inputArray = [], t = 0;
				var input = event.target.querySelectorAll('input[type=text]'); 
				var tpl = event.target.getAttribute('data-debugbar-route-tpl');
				
				for (var n = 0; n < input.length; n++) {
					if (input[n].value.length > 0) inputArray.push(input[n].value);
				}

				if (inputArray.length > 0) {
					_location = location.origin + '/' + tpl.replace(/\?/g, function() {return inputArray[t++]});
					var redirectWindow = window.open(_location, '_blank');
					redirectWindow.location;
				}
			})
		}

	}

};
