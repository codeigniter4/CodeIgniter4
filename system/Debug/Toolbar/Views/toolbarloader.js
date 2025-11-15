document.addEventListener('DOMContentLoaded', loadDoc, false);

function loadDoc(time) {
    if (isNaN(time)) {
        time = document.getElementById("debugbar_loader").getAttribute("data-time");
        localStorage.setItem('debugbar-time', time);
    }

    localStorage.setItem('debugbar-time-new', time);

    let url = '{url}';
    let xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            let toolbar = document.getElementById("toolbarContainer");

            if (! toolbar) {
                toolbar = document.createElement('div');
                toolbar.setAttribute('id', 'toolbarContainer');
                document.body.appendChild(toolbar);
            }

            let responseText = this.responseText;
            let dynamicStyle = document.getElementById('debugbar_dynamic_style');
            let dynamicScript = document.getElementById('debugbar_dynamic_script');

            // get the first style block, copy contents to dynamic_style, then remove here
            let start = responseText.indexOf('>', responseText.indexOf('<style')) + 1;
            let end = responseText.indexOf('</style>', start);
            dynamicStyle.innerHTML = responseText.substr(start, end - start);
            responseText = responseText.substr(end + 8);

            // get the first script after the first style, copy contents to dynamic_script, then remove here
            start = responseText.indexOf('>', responseText.indexOf('<script')) + 1;
            end = responseText.indexOf('\<\/script>', start);
            dynamicScript.innerHTML = responseText.substr(start, end - start);
            responseText = responseText.substr(end + 9);

            // check for last style block, append contents to dynamic_style, then remove here
            start = responseText.indexOf('>', responseText.indexOf('<style')) + 1;
            end = responseText.indexOf('</style>', start);
            dynamicStyle.innerHTML += responseText.substr(start, end - start);
            responseText = responseText.substr(0, start - 8);

            toolbar.innerHTML = responseText;

            if (typeof ciDebugBar === 'object') {
                ciDebugBar.init();
            }
        } else if (this.readyState === 4 && this.status === 404) {
            console.log('CodeIgniter DebugBar: File "WRITEPATH/debugbar/debugbar_' + time + '" not found.');
        }
    };

    xhttp.open("GET", url + "?debugbar_time=" + time, true);
    xhttp.send();
}

window.oldXHR = window.ActiveXObject
    ? new ActiveXObject('Microsoft.XMLHTTP')
    : window.XMLHttpRequest;

function newXHR() {
    const realXHR = new window.oldXHR();

    realXHR.addEventListener("readystatechange", function() {
        // Only success responses and URLs that do not contains "debugbar_time" are tracked
        if (realXHR.readyState === 4 && realXHR.status.toString()[0] === '2' && realXHR.responseURL.indexOf('debugbar_time') === -1) {
            if (realXHR.getAllResponseHeaders().indexOf("Debugbar-Time") >= 0) {
                let debugbarTime = realXHR.getResponseHeader('Debugbar-Time');

                if (debugbarTime) {
                    let h2 = document.querySelector('#ci-history > h2');

                    if (h2) {
                        h2.innerHTML = 'History <small>You have new debug data.</small> <button id="ci-history-update">Update</button>';
                        document.querySelector('a[data-tab="ci-history"] > span > .badge').className += ' active';
                        document.getElementById('ci-history-update').addEventListener('click', function () {
                            loadDoc(debugbarTime);
                        }, false)
                    }
                }
            }
        }
    }, false);
    return realXHR;
}

window.XMLHttpRequest = newXHR;
