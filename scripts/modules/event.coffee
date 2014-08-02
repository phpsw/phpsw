phpsw.event =
  init: ->
    @el = $(".event")

    return unless @el.length

    @venue = @el.find(".event__venue")

    @map.init @

  map:
    init: (e) ->
      @lat = e.venue.data "latitude"
      @lng = e.venue.data "longitude"

      return unless @lat and @lng

      @el = $("<div>", class: "event__map")

      e.venue.prepend @el

      map = new GMaps div: @el.get(0), lat: @lat, lng: @lng, scrollwheel: false

      map.addMarker
        title: e.venue.find(".event__venue__name").text()
        lat: @lat
        lng: @lng
        infoWindow:
          content: e.venue.find(".event__venue__address").html()
