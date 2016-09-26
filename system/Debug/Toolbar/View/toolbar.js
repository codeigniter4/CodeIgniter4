/*
 * Functionality for the CodeIgniter Debug Toolbar.
 */

var ciDebugBar = {

    toolbar : null,

    //--------------------------------------------------------------------

    init : function()
    {
        this.toolbar = document.getElementById('debug-bar');

        // Pad the body to make room for the toolbar.
        //document.getElementsByTagName("html")[0].style.paddingTop = this.toolbar.offsetHeight+"px !important";

        ciDebugBar.createListeners();
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
        var ciIcon = document.getElementById('debug-icon');
        var ciBar = document.getElementById('debug-bar');
        ciIcon.style.display = ciIcon.style.display == 'none' ? 'inline-block' : 'none';
        ciBar.style.display  = ciBar.style.display == 'none' ? 'inline-block' : 'none';
    }
};
