document.addEventListener('DOMContentLoaded', loadDoc, false);

function loadDoc() {
    var filename = document.getElementById("debugbarscript").getAttribute("data-filename");
    var url = document.getElementById("debugbarscript").getAttribute("data-url");
    
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var x = document.body.innerHTML;
            document.body.innerHTML = x + this.responseText;
            eval(document.getElementById("toolbar_js").innerHTML);
            ciDebugBar.init();
        }
    };
    
    xhttp.open("GET", url + "?debug_toolbar_file="+ filename, true);
    xhttp.send();
}
