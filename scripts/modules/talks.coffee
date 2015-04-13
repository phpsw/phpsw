phpsw.talks =
  init: ->
    @el = $(".talks")

    return unless @el.length

    @el.find('[data-toggle="slides"]').each ->
      $talk = $(this).closest(".talk")
      $talk__details = $talk.find(".talk__details")
      $talk__slides = $($(this).attr("data-target"))

      $(this).data "details", $talk__details
      $(this).data "slides", $talk__slides

      $talk__slides.detach().find("iframe").each -> $(this).get(0).stop()

    @el.find('[data-toggle="slides"]').click ->
      $talk__details = $(this).data "details"
      $talk__slides  = $(this).data "slides"

      $talk__slides.insertAfter($talk__details) unless $talk__slides.parent().length

      $talk__slides.toggleClass "in"
