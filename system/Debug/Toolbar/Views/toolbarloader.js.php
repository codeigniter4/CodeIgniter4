<?php if (ENVIRONMENT !== 'testing') : ?>
document.addEventListener('DOMContentLoaded', loadDoc, false);

function loadDoc(time) {
	if (isNaN(time)) {
		time = document.getElementById("debugbar_loader").getAttribute("data-time");
		localStorage.setItem('debugbar-time', time);
	}

	localStorage.setItem('debugbar-time-new', time);

	var url = "<?= rtrim(site_url(), '/') ?>";

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState === 4 && this.status === 200) {
			var toolbar = document.getElementById("toolbarContainer");
			if (!toolbar) {
				toolbar = document.createElement('div');
				toolbar.setAttribute('id', 'toolbarContainer');
				document.body.appendChild(toolbar);
			}

			// copy for easier manipulation
			let responseText = this.responseText;

			// get csp blocked parts
			// the style block is the first and starts at 0
			{
				let PosBeg = responseText.indexOf( '>', responseText.indexOf( '<style' ) ) + 1;
				let PosEnd = responseText.indexOf( '</style>', PosBeg );
				document.getElementById( 'debugbar_dynamic_style' ).innerHTML = responseText.substr( PosBeg, PosEnd - PosBeg );
				responseText = responseText.substr( PosEnd + 8 );
			}
			// the script block starts right after style blocks ended
			{
				let PosBeg = responseText.indexOf( '>', responseText.indexOf( '<script' ) ) + 1;
				let PosEnd = responseText.indexOf( '</script>' );
				document.getElementById( 'debugbar_dynamic_script' ).innerHTML = responseText.substr( PosBeg, PosEnd - PosBeg );
				responseText = responseText.substr( PosEnd + 9 );
			}
			// check for last style block
			{
				let PosBeg = responseText.indexOf( '>', responseText.lastIndexOf( '<style' ) ) + 1;
				let PosEnd = responseText.indexOf( '</style>', PosBeg );
				document.getElementById( 'debugbar_dynamic_style' ).innerHTML += responseText.substr( PosBeg, PosEnd - PosBeg );
				responseText = responseText.substr( 0, PosBeg + 8 );
			}

			toolbar.innerHTML = responseText;
			if (typeof ciDebugBar === 'object') {
				ciDebugBar.init();
			}
		} else if (this.readyState === 4 && this.status === 404) {
			console.log('CodeIgniter DebugBar: File "WRITEPATH/debugbar/debugbar_' + time + '" not found.');
		}
	};

	xhttp.open("GET", url + "/?debugbar_time=" + time, true);
	xhttp.send();
}

// Track all AJAX requests
if (window.ActiveXObject) {
	var oldXHR = new ActiveXObject('Microsoft.XMLHTTP');
} else {
	var oldXHR = window.XMLHttpRequest;
}

function newXHR() {
	var realXHR = new oldXHR();
	realXHR.addEventListener("readystatechange", function() {
		// Only success responses and URLs that do not contains "debugbar_time" are tracked
		if (realXHR.readyState === 4 && realXHR.status.toString()[0] === '2' && realXHR.responseURL.indexOf('debugbar_time') === -1) {
			var debugbarTime = realXHR.getResponseHeader('Debugbar-Time');
			if (debugbarTime) {
				var h2 = document.querySelector('#ci-history > h2');
				h2.innerHTML = 'History <small>You have new debug data.</small> <button onclick="loadDoc(' + debugbarTime + ')">Update</button>';
				var badge = document.querySelector('a[data-tab="ci-history"] > span > .badge');
				badge.className += ' active';
			}
		}
	}, false);
	return realXHR;
}

window.XMLHttpRequest = newXHR;
<?php endif; ?>
