/*
 * Bootstrap for standalone Debug Toolbar pages (?debugbar_time=...).
 */

if (! document.getElementById('debugbar_loader')) {
    if (typeof loadDoc !== 'function') {
        window.loadDoc = function (time) {
            if (isNaN(time)) {
                return;
            }

            localStorage.setItem('debugbar-time', time);
            localStorage.setItem('debugbar-time-new', time);
            window.location.href = ciSiteURL + '?debugbar_time=' + time;
        };
    }

    (function () {
        function initStandaloneToolbar() {
            if (typeof ciDebugBar !== 'object') {
                return;
            }

            if (! document.getElementById('debug-bar') || ! document.getElementById('debug-icon')) {
                return;
            }

            ciDebugBar.init();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initStandaloneToolbar, false);
        } else {
            initStandaloneToolbar();
        }
    })();
}
