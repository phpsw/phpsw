phpsw.email =
  init: ->
    $(".email").filter(':not(.dehumanized)').each (i, el) =>
      $(el)
        .addClass 'dehumanized'
        .attr "href", @dehumanize $(el).attr("href")
        .html @dehumanize $(el).html()

  dehumanize: (string) ->
    string
      .replace /\s?at\s?/, "@"
      .replace /\s?dot\s?/g, "."
      .replace /\%20/g, ''
      .replace />\s+</g, "><"
