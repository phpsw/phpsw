$(function() {
    $('.event--upcoming:first').each(function() {
        $venue = $(this).find('.event__venue');

        if ($venue.data('latitude') && $venue.data('longitude')) {
            $map = $('<div>', {class: 'event__map'});

            $venue.after($map);

            var map = new GMaps({
                div: $map.get(0),
                lat: $venue.data('latitude'),
                lng: $venue.data('longitude')
            });

            map.addMarker({
                title: $venue.find('.event__venue__name').text(),
                lat: $venue.data('latitude'),
                lng: $venue.data('longitude'),
                infoWindow: {
                    content: $venue.html()
                }
            });
        }
    })
})
