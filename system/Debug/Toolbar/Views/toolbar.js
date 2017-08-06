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
        var tab = this.getAttribute('data-tab');

        // Check our current state.
        var state = document.getElementById(tab).style.display;

        if (tab == undefined) return true;

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
            document.getElementById(tab).style.display = 'block';
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
