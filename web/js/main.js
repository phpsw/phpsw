$(function() {
    $('.event--past:gt(2)').addClass('js-hide').first().after(
        $('<button>', {text: 'See more past events', class: 'box'}).click(function() {
            $(this).siblings('.event--past').removeClass('js-hide');
            $(this).remove();
        })
    );

    $('.event--upcoming:first').each(function() {
        $venue = $(this).find('.event__venue');

        if ($venue.data('latitude') && $venue.data('longitude')) {
            $map = $('<div>', {class: 'event__map'});

            $venue.before($map);

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
