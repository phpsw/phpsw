email =
  parse: ->
    # Convert human readable email strings into mailto: hyperlinks
    $(".email").each ->
        $(this)
            .attr "href", email.dehumanize $(this).attr("href")
            .text email.dehumanize $(this).text()

  dehumanize: (string) ->
    # Convert a human readable email string into an email address
    string.replace(/\s?at\s?/g, "@").replace(/\s?dot\s?/g, ".").replace(/\%20/g, '').replace />\s+</g, "><"

$ -> email.parse()
