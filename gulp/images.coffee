g = module.parent.exports

g.task "images", ->
  deferred = g.q.defer()

  g.src "images/**/*.png"
    .pipe g.p.imagemin().on 'end', -> deferred.resolve()
    .pipe g.dest "web/images"
    .pipe g.reload()

  g.src "images/**/*.svg"
    .pipe g.dest "web/images"
    .pipe g.p.tap (file, t) ->
      file.contents = new Buffer file.contents.toString().replace(
        /svg class="([^"]+)"/,
        'svg class="$1 {%- if size is defined %} $1--{{ size }} {%- endif %}"'
      )
    .pipe g.p.rename extname: ".svg.twig"
    .pipe g.dest "views"
    .pipe g.reload()

  deferred.promise
