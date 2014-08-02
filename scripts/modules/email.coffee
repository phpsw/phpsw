phpsw.email =
  init: ->
    $(".email").each (i, el) =>
      $email = $(el)

      $email
        .attr "href", @dehumanize $email.attr("href")
        .text @dehumanize $email.text()

  dehumanize: (string) ->
    string
      .replace /\s?at\s?/, "@"
      .replace /\s?dot\s?/g, "."
      .replace /\%20/g, ''
      .replace />\s+</g, "><"
