var map = null;
var markers = [];
    
function initialize() {
    var mapOptions = {
        center: new google.maps.LatLng(40.7, -73.8),
        zoom: 10,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("mapa"),
        mapOptions);
    renderMarkers();
}

function renderMarkers() {
    
    var infowindow = new google.maps.InfoWindow({
        content: ''
    });
    
    for(var index in fotos) {
        var latLng = new google.maps.LatLng(fotos[index].latitude, fotos[index].longitude);
        var marker = new google.maps.Marker({
            position: latLng,
            map: map
        });

        marker.html = '<img class="info_picture" src="/bundles/triplottriplot/pictures/' + fotos[index].file + '" />';
    
        
        markers.push(marker);
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.setContent(this.html);
            infowindow.open(map, this);
        });
    }
}
    
function AutoCenter() {
    //  Create a new viewpoint bound
    var bounds = new google.maps.LatLngBounds();
    //  Go through each...
    $.each(markers, function (index, marker) {
        bounds.extend(marker.position);
    });
    //  Fit these bounds to the map
    map.fitBounds(bounds);
}
    
$(document).ready(function(){
    initialize();
});
