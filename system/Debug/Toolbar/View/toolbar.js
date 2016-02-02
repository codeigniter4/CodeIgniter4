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
        document.getElementsByTagName("body")[0].style.marginTop = this.toolbar.offsetHeight+"px !important";

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

        if (tab == undefined) return true;

        // Hide all tabs
        var tabs = document.querySelectorAll('#debug-bar .tab');

        for (var i=0; i < tabs.length; i++)
        {
            tabs[i].style.display = 'none';
        }

        // Show the selected tab
        document.getElementById(tab).style.display = 'block';
    }

    //--------------------------------------------------------------------

};
