g = module.parent.exports

g.task "images", ->
  deferred = g.q.defer()

  community = g.p.filter "**/community/*"
  sponsors = g.p.filter "**/sponsors/*"

  g.src "images/**/*.{gif,jpg,jpeg,png}"
    .pipe community
    .pipe g.p.gm (gm) ->
      gm
        .background 'transparent'
        .gravity 'Center'
        .resize 80, 60
        .extent 80, 60
        .transparent 'white'
        .setFormat 'png'
    .pipe community.restore()

    .pipe g.p.imagemin()
      .on 'end', -> deferred.resolve()
      .on 'error', g.p.util.log

    .pipe g.dest "web/images"
    .pipe g.reload()

  g.src "images/**/*.svg"
    .pipe g.dest "web/images"
    .pipe g.p.tap (file, t) ->
      file.contents = new Buffer(file.contents.toString()
        .replace '<?xml version="1.0"?>\n', ''
        .replace(
          /svg class="([^"]+)"/,
          'svg class="$1 {%- if size is defined %} $1--{{ size }} {%- endif %}"'
        )
      )
    .pipe g.p.rename extname: ".svg.twig"
    .pipe g.dest "views"
    .pipe g.reload()

  deferred.promise
