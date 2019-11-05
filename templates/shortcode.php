<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>

<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=<?= esc_attr($google_api_key); ?>&sensor=false&libraries=geometry&language=<?= substr( get_bloginfo ( 'language' ), 0, 2 ); ?>"></script>
<script type="text/javascript" src="<?= esc_url(get_template_directory_uri()); ?>/js/google-maps/maplabel-compiled.min.js"></script>
<script type="text/javascript" src="<?= esc_url(get_template_directory_uri()); ?>/js/google-maps/markerclusterer.min.js"></script>

<div id="custom-map" style="height: 600px;"></div>
    
<?php if(!empty($pois_json)): ?>
<script type="text/javascript">
	var mapPoints = <?= $pois_json; ?>;
	var zoom = 4; // map default zoom
	var markers = []; // all map markers

	jQuery(function(){
		var mapOptions = {
			center: new google.maps.LatLng(45.0,17.0),
			zoom: zoom,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		var map = new google.maps.Map(document.getElementById("custom-map"), mapOptions);
		var mcOptions = {gridSize: 50, maxZoom: 10};

		for(var i in mapPoints) {

			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(mapPoints[i].lat,mapPoints[i].lng),
				map: map,
				icon: '<?= $plugin_url; ?>icons/map/' + mapPoints[i].icon + '.png',
				title: mapPoints[i].title,
				url: mapPoints[i].link,
				labelClass: "map_labels"
			});

			// dobawqme event listener za klik warhu marker, koito wodi do negowoto url
			google.maps.event.addListener(marker, 'click', function() {
				window.open(this.url, "_blank");
			//	window.focus();
			});

			markers.push(marker);
		}

		var mc = new MarkerClusterer(map, markers, mcOptions);
	});
</script>
<?php endif; ?>