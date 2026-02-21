/*
 * Bootstrap for standalone Debug Toolbar pages (?debugbar_time=...).
 */

if (! document.getElementById('debugbar_loader')) {
    if (typeof loadDoc !== 'function') {
        window.loadDoc = function (time) {
            if (isNaN(time)) {
                return;
            }

            window.location.href = ciSiteURL + '?debugbar_time=' + time;
        };
    }

    (function () {
        function ensureToolbarContainer(icon, toolbar) {
            let toolbarContainer = document.getElementById('toolbarContainer');

            if (toolbarContainer) {
                return;
            }

            toolbarContainer = document.createElement('div');
            toolbarContainer.setAttribute('id', 'toolbarContainer');

            if (icon) {
                toolbarContainer.appendChild(icon);
            }

            if (toolbar) {
                toolbarContainer.appendChild(toolbar);
            }

            document.body.appendChild(toolbarContainer);
        }

        function initStandaloneToolbar() {
            if (typeof ciDebugBar !== 'object') {
                return;
            }

            const icon = document.getElementById('debug-icon');
            const toolbar = document.getElementById('debug-bar');

            if (! toolbar || ! icon) {
                return;
            }

            const currentTime = new URLSearchParams(window.location.search).get('debugbar_time');

            if (currentTime && ! isNaN(currentTime)) {
                if (! localStorage.getItem('debugbar-time')) {
                    localStorage.setItem('debugbar-time', currentTime);
                }
                localStorage.setItem('debugbar-time-new', currentTime);
            }

            ensureToolbarContainer(icon, toolbar);
            ciDebugBar.init();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initStandaloneToolbar, false);
        } else {
            initStandaloneToolbar();
        }
    })();
}
