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
        .resize 160, 120
        .extent 160, 120
        .transparent 'white'
        .setFormat 'png'
    .pipe community.restore()

    .pipe sponsors
    .pipe g.p.gm (gm) ->
      gm
        .background 'transparent'
        .gravity 'Center'
        .resize 400, 300
        .extent 400, 300
        .transparent 'white'
        .setFormat 'png'
    .pipe sponsors.restore()

    .pipe g.p.imagemin()
      .on 'end', -> deferred.resolve()
      .on 'error', g.p.util.log

    .pipe g.dest "web/images"
    .pipe g.reload()

  g.src "images/**/*.svg"
    .pipe g.dest "web/images"
    .pipe g.p.tap (file, t) ->
      file.contents = new Buffer(file.contents.toString()
        .replace(
          /class="([^"]+)"/,
          'class="$1 {%- if modifier is defined %} $1--{{ modifier }} {%- endif %}"'
        )
      )
    .pipe g.p.rename extname: ".svg.twig"
    .pipe g.dest "views"
    .pipe g.reload()

  deferred.promise
