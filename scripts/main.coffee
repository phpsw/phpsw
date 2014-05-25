$ ->
  $(".event").each ->
    $(this).find(".event__description").expander
      expandEffect: "show"
      expandSpeed: 0
      collapseEffect: "hide"
      collapseSpeed: 0
      slicePoint: (if $(this).hasClass("event--upcoming") then 320 else 160)

  $("section[id]").each ->
    $section = $(this)

    $section.find(".box:gt(2)").addClass("js-hide").first().after(
      $("<button>",
        text: "See more " + $section.attr("id").replace("-", " ")
        class: "box"
      ).click(->
        $(this).siblings().removeClass "js-hide"
        $(this).remove()
      )
    )

  $(".event--upcoming").each ->
    $venue = $(this).find(".event__venue")
    if $venue.data("latitude") and $venue.data("longitude")
      $map = $("<div>", class: "event__map")

      $venue.before $map

      map = new GMaps(
        div: $map.get(0)
        lat: $venue.data("latitude")
        lng: $venue.data("longitude")
        scrollwheel: false
      )

      map.addMarker
        title: $venue.find(".event__venue__name").text()
        lat: $venue.data("latitude")
        lng: $venue.data("longitude")
        infoWindow:
          content: $venue.html()

  $(".box footer time").timeago()
