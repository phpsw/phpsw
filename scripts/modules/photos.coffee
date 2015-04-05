phpsw.photos =
  init: ->
    @photos = $(".photos")
    @list = @photos.find(".list--photos")

    @hoot()
    @fancy()

    if @list.length then @on() else @off()

  on: ->
    @photos.on "mouseenter", @x.mouseenter
    @photos.on "mouseleave", @x.mouseleave
    $(window).on "resize", @x.resize

  off: ->
    @photos.off "mouseenter", @x.mouseenter
    @photos.off "mouseleave", @x.mouseleave
    $(window).off "resize", @x.resize

  config: singleItem: true

  delay: (ms, func) -> setTimeout func, ms

  hoot: ->
    if @list.length
      items = @list.find("li")
      count = Math.floor(@list.innerWidth() / items.outerWidth(true)) * 2

      @owl = @list.data("owlCarousel")

      if count != @list.find(".owl-panel:first li").length
        @list.html(items)

        for i in [0..items.length] by count
          items.slice(i, i + count).wrapAll('<div class="owl-panel"></div>')

        if not @owl then @list.owlCarousel(@config) and @hoot() else @owl.reinit(@config)

  mouseenter: -> $(window).on  "scroll", @x.scroll
  mouseleave: -> $(window).off "scroll", @x.scroll

  scroll: ->
    x = $(document).scrollLeft()

    if x != 0
      if x > 0 then @owl.next() else if x < 0 then @owl.prev()

      $(window).off("scroll", @x.scroll)

      @delay 500, => $(window).off("scroll", @x.scroll).on("scroll", @x.scroll)

  fancy: -> $(".fancybox").fancybox()

  x:
    resize:     -> phpsw.photos.hoot()
    mouseenter: -> phpsw.photos.mouseenter()
    mouseleave: -> phpsw.photos.mouseleave()
    scroll:     -> phpsw.photos.scroll()
