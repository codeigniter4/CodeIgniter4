// Google Map Customization
(function(){

	var map,
		el = '#gmap',
		$el = $(el),
		lat = $el.data('lat'),
		long = $el.data('long');

	map = new GMaps({
		el: el,
		lat: lat,
		lng: long,
		scrollwheel:false,
		zoom: 16,
		zoomControl: true,
		panControl : false,
		streetViewControl : false,
		mapTypeControl: false,
		overviewMapControl: false,
		clickable: false
	});

	var image = '/assets/images/map-icon.png';
	map.addMarker({
		lat: lat,
		lng: long,
		// icon: image,
		animation: google.maps.Animation.DROP,
		verticalAlign: 'bottom',
		horizontalAlign: 'center',
		backgroundColor: '#ffffff'
	});

	/*
	map.addStyle({
		styledMapName:"Styled Map",
	 styles: [{"stylers":[{"visibility":"simplified"},{"saturation":10},{"weight":0.8},{"lightness":15}]}],
	 mapTypeId: "map_style"
	});

	 map.setStyle("map_style");*/
}());