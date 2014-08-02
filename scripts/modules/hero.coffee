phpsw.hero =
  init: ->
    @el = $(".hero")

    return if Modernizr.touch or not @el.length

    @image = @el.find(".hero__image")
    @overlay = @el.find(".hero__overlay")

    $(window).on "resize scroll", =>
      y = $(window).scrollTop()

      @animate y unless y < 0 or y > @el.offset().top + @el.height()

  animate: (y) ->
    @image.css transform: "translateY(#{Math.round y / 10}px)"
    @overlay.css background: "rgba(0, 0, 0, #{Math.round(y / 20) / 100})"
