
document.addEventListener('DOMContentLoaded', loadDoc, false );

function loadDoc() {
    var time = document.getElementById("debugbar_loader").getAttribute("data-time");
    var url = "<?php echo rtrim(site_url(), '/') ?>";

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
        	var toolbar = document.createElement( 'div' );
        	toolbar.setAttribute( 'id', 'toolbarContainer' );
        	toolbar.innerHTML = this.responseText;
            document.body.appendChild( toolbar );
            eval(document.getElementById("toolbar_js").innerHTML);
            if (typeof ciDebugBar === 'object') {
                ciDebugBar.init();
            }
        }
    };

    xhttp.open("GET", url + "?debugbar_time=" + time, true);
    xhttp.send();
}
