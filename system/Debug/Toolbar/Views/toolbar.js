/*
 * Functionality for the CodeIgniter Debug Toolbar.
 */

var ciDebugBar = {

	toolbar : null,
	icon : null,

	//--------------------------------------------------------------------

	init : function()
	{
		this.toolbar = document.getElementById('debug-bar');
		this.icon = document.getElementById('debug-icon');

		ciDebugBar.createListeners();
		ciDebugBar.setToolbarState();
		ciDebugBar.setToolbarPosition();
		ciDebugBar.toogleViewsHints();
	},

	//--------------------------------------------------------------------

	createListeners : function()
	{
		var buttons = [].slice.call(document.querySelectorAll('#debug-bar .ci-label a'));

		for (var i=0; i < buttons.length; i++)
		{
			buttons[i].addEventListener('click', ciDebugBar.showTab, true);
		}
	},

	//--------------------------------------------------------------------

	showTab: function()
	{
		// Get the target tab, if any
		var tab = document.getElementById(this.getAttribute('data-tab'));

		// If the label have not a tab stops here
		if (! tab) {
			return;
		}

		// Check our current state.
		var state = tab.style.display;

		// Hide all tabs
		var tabs = document.querySelectorAll('#debug-bar .tab');

		for (var i=0; i < tabs.length; i++)
		{
			tabs[i].style.display = 'none';
		}

		// Mark all labels as inactive
		var labels = document.querySelectorAll('#debug-bar .ci-label');

		for (var i=0; i < labels.length; i++)
		{
			ciDebugBar.removeClass(labels[i], 'active');
		}

		// Show/hide the selected tab
		if (state != 'block')
		{
			tab.style.display = 'block';
			ciDebugBar.addClass(this.parentNode, 'active');
		}
	},

	//--------------------------------------------------------------------

	addClass : function(el, className)
	{
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

	removeClass : function(el, className)
	{
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
	 * @param obj
	 */
	toggleDataTable : function(obj)
	{
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
	toggleToolbar : function()
	{
		var open = ciDebugBar.toolbar.style.display != 'none';

		ciDebugBar.icon.style.display = open == true ? 'inline-block' : 'none';
		ciDebugBar.toolbar.style.display  = open == false ? 'inline-block' : 'none';

		// Remember it for other page loads on this site
		ciDebugBar.createCookie('debug-bar-state', '', -1);
		ciDebugBar.createCookie('debug-bar-state', open == true ? 'minimized' : 'open' , 365);
	},

	//--------------------------------------------------------------------

	/**
	 * Sets the initial state of the toolbar (open or minimized) when
	 * the page is first loaded to allow it to remember the state between refreshes.
	 */
	setToolbarState: function()
	{
		var open = ciDebugBar.readCookie('debug-bar-state');

		ciDebugBar.icon.style.display = open != 'open' ? 'inline-block' : 'none';
		ciDebugBar.toolbar.style.display  = open == 'open' ? 'inline-block' : 'none';
	},

	//--------------------------------------------------------------------

	toogleViewsHints: function()
	{
		var NodeList 		= []; // [ Element, NewElement( 1 )/OldElement( 0 ) ]
		var sortedComments 	= [];

		// get all comments | iterate over all nodes
		// @return array of comment nodes
		var GetComments = function( parentElement )
		{
			var comments = [];
			var childs = parentElement.childNodes;
			for( var i = 0; i < childs.length; ++i )
			{
				if( childs[i].nodeType === Node.COMMENT_NODE &&
					childs[i].nodeValue.startsWith( ' DEBUG-VIEW' ) )
				{
					comments.push( childs[i] );
					continue;
				}

				if( childs[i].childNodes.length > 0 )
				{
					var childComments = GetComments( childs[i] );
					comments.push.apply( comments, childComments );
				}
			}
			return comments;
		};

		// find node that has TargetNode as parentNode
		var GetParentNode = function( Node, TargetNode )
		{
			if( Node.parentNode === null )
			{
				return null;
			}

			if( Node.parentNode !== TargetNode )
			{
				return GetParentNode( Node.parentNode, TargetNode );
			}

			return Node;
		};

		// define invalid & outer ( also invalid ) elements
		var InvalidElements = [ 'NOSCRIPT', 'SCRIPT', 'STYLE' ];
		var OuterElements = [ 'HTML', 'BODY', 'HEAD' ];

		var GetValidElementInner = function( Node, reverse )
		{
			// handle invalid tags
			if( OuterElements.indexOf( Node.nodeName ) !== -1 )
			{
				for( var i = 0; i < document.body.children.length; ++i )
				{
					var Index = reverse ? document.body.children.length - ( i + 1 ) : i;
					var Element = document.body.children[Index];

					// skip invalid tags
					if( InvalidElements.indexOf( Element.nodeName ) !== -1 ) continue;

					return [ Element, reverse ];
				}

				return null;
			}

			// get to next valid element
			while( Node !== null && InvalidElements.indexOf( Node.nodeName ) !== -1 )
			{
				Node = reverse ? Node.previousElementSibling : Node.nextElementSibling;
			}

			// return non array if we couldnt find something
			if( Node === null ) return null;

			return [ Node, reverse ];
		};

		// get next valid element ( to be safe to add divs )
		// @return [ element, skip element ] or null if we couldnt find a valid place
		var GetValidElement = function( NodeElement )
		{
			if( NodeElement.nextElementSibling !== null )
			{
				return GetValidElementInner( NodeElement.nextElementSibling, false )
					|| GetValidElementInner( NodeElement.previousElementSibling, true );
			}
			if( NodeElement.previousElementSibling !== null )
			{
				return GetValidElementInner( NodeElement.previousElementSibling, true );
			}

			// something went wrong! -> element is not in DOM
			return null;
		};

		var comments = GetComments( document );
		// sort comment by opening and closing tags
		for( var i = 0; i < comments.length; ++i )
		{
			// get file path + name to use as key
			var Path = comments[i].nodeValue.substring( 18, comments[i].nodeValue.length - 1 );

			if( comments[i].nodeValue[12] === 'S' ) // simple check for start comment
			{
				// create new entry
				sortedComments[Path] = [ comments[i], null ];
			}
			else
			{
				// add to existing entry
				sortedComments[Path][1] = comments[i];
			}
		}
		comments.length = 0;

		var btn = document.querySelector('[data-tab=ci-views]');

		// If the Views Collector is inactive stops here
		if (! btn)
		{
			return;
		}

		btn = btn.parentNode;

		btn.onclick = function() {
			// Had AJAX? Reset view blocks
			//comments = GetComments( document ); // TODO : remove comment/enable line

			if (ciDebugBar.readCookie('debug-view'))
			{
				for( var i = 0; i < NodeList.length; ++i )
				{
					var index;

					// find index
					for( var j = 0; j < NodeList[i].parentNode.childNodes.length; ++j )
					{
						if( NodeList[i].parentNode.childNodes[j] === NodeList[i] )
						{
							index = j;
							break;
						}
					}

					// move child back
					while( NodeList[i].childNodes.length !== 1 )
					{
						NodeList[i].parentNode.insertBefore( NodeList[i].childNodes[1], NodeList[i].parentNode.childNodes[index].nextSibling  );
					}

					NodeList[i].parentNode.removeChild( NodeList[i] );
				}
				NodeList.length = 0;

				ciDebugBar.createCookie('debug-view', '', -1);
				ciDebugBar.removeClass(btn, 'active');
			}
			else
			{
				for( var key in sortedComments )
				{
					var StartElement 	= GetValidElement( sortedComments[key][0] );
					var EndElement 		= GetValidElement( sortedComments[key][1] );

					// skip if we couldnt get a valid element
					if( StartElement === null || EndElement === null ) continue;

					// find element which has same parent as startelement
					var JointParent = GetParentNode( EndElement[0], StartElement[0].parentNode );
					if( JointParent === null )
					{
						// find element which has same parent as endelement
						JointParent = GetParentNode( StartElement[0], EndElement[0].parentNode );
						if( JointParent === null )
						{
							// both tries failed
							continue;
						}
						else StartElement[0] = JointParent;
					}
					else EndElement[0] = JointParent;

					var DebugDiv 		= document.createElement( 'div' ); // holder
					var DebugPath		= document.createElement( 'div' ); // path
					var ChildArray 		= StartElement[0].parentNode.childNodes; // target child array
					var Parent			= StartElement[0].parentNode;
					var Start, End;

					// setup container
					DebugDiv.classList.add( 'debug-view' );
					DebugDiv.classList.add( 'show-view' );
					DebugPath.classList.add( 'debug-view-path' );
					DebugPath.innerText = key;
					DebugDiv.appendChild( DebugPath );

					// calc distance between them
					// Start
					for( var i = 0; i < ChildArray.length; ++i )
					{
						// check for comment ( start & end ) -> if its before valid start element
						if( ChildArray[i] === sortedComments[key][1] ||
							ChildArray[i] === sortedComments[key][0] ||
							ChildArray[i] === StartElement[0] )
						{
							Start = i;
							if( ChildArray[i] === sortedComments[key][0] ) Start++; // increase to skip the start comment
							break;
						}
					}
					// adjust if we want to skip the start element
					if( StartElement[1] ) Start++;

					// End
					for( var i = Start; i < ChildArray.length; ++i )
					{
						if( ChildArray[i] === EndElement[0] )
						{
							End = i;
							// dont break to check for end comment after end valid element
						}
						else if( ChildArray[i] === sortedComments[key][1] )
						{
							// if we found the end comment, we can break
							End = i;
							break;
						}
					}

					// move elements
					var Number = End - Start;
					if( EndElement[1] ) Number++;
					for( var i = 0; i < Number; ++i )
					{
						if( InvalidElements.indexOf( ChildArray[Start] ) !== -1 )
						{
							// skip invalid childs that can cause problems if moved
							Start++;
							continue;
						}
						DebugDiv.appendChild( ChildArray[Start] );
					}

					// add container to DOM
					NodeList.push( Parent.insertBefore( DebugDiv, ChildArray[Start] ) );
				}

				ciDebugBar.createCookie('debug-view', 'show', 365);
				ciDebugBar.addClass(btn, 'active');
			}
		};
	},

	//--------------------------------------------------------------------

	setToolbarPosition: function ()
	{
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

	/**
	 * Helper to create a cookie.
	 *
	 * @param name
	 * @param value
	 * @param days
	 */
	createCookie : function(name,value,days)
	{
		if (days)
		{
			var date = new Date();

			date.setTime(date.getTime()+(days*24*60*60*1000));

			var expires = "; expires="+date.toGMTString();
		}
		else
		{
			var expires = "";
		}

		document.cookie = name+"="+value+expires+"; path=/";
	},

	//--------------------------------------------------------------------

	readCookie : function(name)
	{
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');

		for(var i=0;i < ca.length;i++)
		{
			var c = ca[i];
			while (c.charAt(0)==' ')
			{
				c = c.substring(1,c.length);
			}
			if (c.indexOf(nameEQ) == 0)
			{
				return c.substring(nameEQ.length,c.length);
			}
		}
		return null;
	}
};
