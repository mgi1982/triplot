var map = null;
var markers = [];
var slideshow_stop = false;

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

function renderMarkers(date) {
    
    var infowindow = new google.maps.InfoWindow({
        content: ''
    });

    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }

    markers = [];
    
    for(var index in fotos) {
        if (typeof(date) === 'undefined' || date === '' || fotos[index].date == date) {
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

function slideshow() {
    if (slideshow_stop) {
        return;
    }
    
    var timeout = 2000;
    var day0 = $('#timeline').children()[0];
    var nextDay = $('.day.selected').next();
    
    if (nextDay.length == 0) {
       $('#timeline').find('li.day').removeClass('selected');
       renderMarkers($(day0).attr('data')); 
       $(day0).addClass('selected'); 
    } else {
       $('#timeline').find('li.day').removeClass('selected');
       renderMarkers($(nextDay).attr('data')); 
       $(nextDay).addClass('selected'); 
    }
    setTimeout(slideshow, timeout); 
}
    
$(document).ready(function(){
    initialize();
    $('#timeline').find('.day').click(function(){
       $('#timeline').find('li.day').removeClass('selected');
       renderMarkers($(this).attr('data')); 
       $(this).addClass('selected');
    });
    $('#timeline').find('.slideshow').click(function(){
       if ($(this).text() == 'Stop') {
           $(this).text('Start').removeClass('playing');
          slideshow_stop = true; 
          $('#timeline').find('li.day').removeClass('selected');
          renderMarkers();
       } else {
          $(this).text('Stop').addClass('playing');
          slideshow_stop = false; 
          slideshow();
       }
    });
});
