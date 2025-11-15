/*
 * Add Carbon Ads underneath navigation to support CodeIgniter Foundation
 */
if (window.location.hostname === 'codeigniter.com') {
    window.onload = function () {
        // Create a HTML DOM Element Object
        var carbon = document.createElement('script');
        carbon.async = true;
        carbon.type = 'text/javascript';
        carbon.src = 'https://cdn.carbonads.com/carbon.js?serve=CE7I62QW&placement=codeignitercom';
        carbon.id = '_carbonads_js';

        // Append Carbon Ads to .wy-menu
        document.querySelector('.wy-menu').appendChild(carbon);
    }
}
