
document.addEventListener('DOMContentLoaded', loadDoc, false );

function loadDoc(time) {
	if (isNaN(time)) {
		time = document.getElementById("debugbar_loader").getAttribute("data-time");
		localStorage.setItem('debugbar-time', time);
		localStorage.setItem('debugbar-view', true);
	}

	localStorage.setItem('debugbar-time-new', time);

    var url = "<?= rtrim(site_url(), '/') ?>";

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
        	var toolbar = document.getElementById("toolbarContainer");
        	if (! toolbar) {
        		toolbar = document.createElement('div');
	            toolbar.setAttribute('id', 'toolbarContainer');
	            toolbar.innerHTML = this.responseText;
	            document.body.appendChild(toolbar);
        	} else {
        		toolbar.innerHTML = this.responseText;
        	}
            eval(document.getElementById("toolbar_js").innerHTML);
            if (typeof ciDebugBar === 'object') {
                ciDebugBar.init();
            }
        }
    };

    xhttp.open("GET", url + "?debugbar_time=" + time, true);
    xhttp.send();
}

var oldXHR = window.XMLHttpRequest;

/**
 * Track all the AJAX requests
 */
function newXHR() {
    var realXHR = new oldXHR();
    realXHR.addEventListener("readystatechange", function() {
    	// Only success responses and URLs that do not contains "debugbar_time" are tracked
        if(realXHR.readyState == 4 && realXHR.status.toString()[0] == 2 && realXHR.responseURL.indexOf('debugbar_time') == -1 ) {
        	var debugbarTime = realXHR.getResponseHeader('Debugbar-Time');

        	if (debugbarTime) {
        		var h2 = document.querySelector('#ci-history > h2');
        		h2.innerHTML = 'History <small>You have new debug data.</small> <button onclick="loadDoc(' + debugbarTime + ')">Update</button>';
        		var badge = document.querySelector('[data-tab="ci-history"] > span > .badge');
        		badge.style.background = 'red';
        	}
        }
    }, false);
    return realXHR;
}
window.XMLHttpRequest = newXHR;
