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
    },

    //--------------------------------------------------------------------

    createListeners : function()
    {

    },

    //--------------------------------------------------------------------

};
