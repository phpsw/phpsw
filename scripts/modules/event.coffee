phpsw.event =
  init: ->
    @el = $(".event")

    return unless @el.length

    @location = @el.find(".event__location")

    @map.init @

  map:
    init: (e) ->
      @lat = e.location.data "latitude"
      @lng = e.location.data "longitude"

      return unless @lat and @lng

      @el = $("<div>", class: "event__map")

      e.location.prepend @el

      map = new GMaps div: @el.get(0), lat: @lat, lng: @lng, scrollwheel: false

      map.addMarker
        title: e.location.find(".event__location__venue__name").text()
        lat: @lat
        lng: @lng
        infoWindow:
          content: e.location.html()
