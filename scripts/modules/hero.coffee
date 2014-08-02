phpsw.hero =
  init: ->
    @el = $(".hero")

    @present = !!@el.length
    @absent = !@present

    return if Modernizr.touch or @absent

    @image = @el.find(".hero__image")
    @overlay = @el.find(".hero__overlay")

    unless @subscribed
      $(window).on "resize scroll", =>
        if @present
          y = $(window).scrollTop()

          @animate y unless y < 0 or y > @el.offset().top + @el.height()

      @subscribed = true

  animate: (y) ->
    @image.css transform: "translateY(#{Math.round y / 10}px)"
    @overlay.css background: "rgba(0, 0, 0, #{Math.round(y / 20) / 100})"

  subscribed: false
