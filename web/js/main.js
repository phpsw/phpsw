$(function() {
    $('.event').each(function() {
        $(this).find('.event__description').expander({
            expandEffect: 'show',
            expandSpeed: 0,
            collapseEffect: 'hide',
            collapseSpeed: 0,
            slicePoint: $(this).hasClass('event--upcoming') ? 320 : 160
        });
    })

    $('section[id]').each(function() {
        $section = $(this);

        $section.find('.box:gt(2)').addClass('js-hide').first().after(
            $('<button>', {
                text: 'See more ' + $section.attr('id').replace('-', ' '),
                class: 'box'
            }).click(function() {
                $(this).siblings().removeClass('js-hide');
                $(this).remove();
            })
        );
    })

    $('.event--upcoming:first').each(function() {
        $venue = $(this).find('.event__venue');

        if ($venue.data('latitude') && $venue.data('longitude')) {
            $map = $('<div>', {class: 'event__map'});

            $venue.before($map);

            var map = new GMaps({
                div: $map.get(0),
                lat: $venue.data('latitude'),
                lng: $venue.data('longitude'),
                scrollwheel: false
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

    $(".box footer time").timeago();
})
