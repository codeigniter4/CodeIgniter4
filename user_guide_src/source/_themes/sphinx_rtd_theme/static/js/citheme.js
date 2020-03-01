/*
 * Add a unique ID to the body of each page of the documentation
 */

  // Clean the url by removing...

    //  End slashes
    var cleanSlash = window.location.href.replace(/\/$/, '');
    //  ".html" extensions
    var cleanExtension = cleanSlash.replace(/\.html$/, '');

  // Get the last segment of the url
  var lastSegment = cleanExtension.substr(cleanExtension.lastIndexOf('/') + 1);

  // Set the ID
  window.onload = function(){
    document.body.id = 'page-' + lastSegment;
  };
