g = require "gulp"

g.task "vendor", -> g.start "vendor-css", "vendor-images", "vendor-js"

g.task "vendor-css", ["bower"], ->
  g.src ["styles/vendor.scss", "vendor/bower/files/**/*.css"]
    .pipe g.css()
    .pipe g.p.tap (file, t) ->
      file.contents = new Buffer(file.contents.toString()
        .replace "blank.gif",               "/images/blank.gif"
        .replace "fancybox_loading.gif",    "/images/fancybox_loading.gif"
        .replace "fancybox_loading@2x.gif", "/images/fancybox_loading@2x.gif"
        .replace "fancybox_overlay.png",    "/images/fancybox_overlay.png"
        .replace "fancybox_sprite.png",     "/images/fancybox_sprite.png"
        .replace "fancybox_sprite@2x.png",  "/images/fancybox_sprite@2x.png"
        .replace /..\/fonts\//g, "/fonts/"  # fix font awesome paths
      )
    .pipe g.p.concat "vendor.css"
    .pipe g.dest "web/css"
    .pipe g.reload()

g.task "vendor-images", ["bower"], ->
  deferred = g.q.defer()

  g.src "vendor/bower/files/**/*.{gif,jpg,jpeg,png,svg}"
    .pipe g.p.if g.e isnt 'dev', g.images(deferred), deferred.resolve()
    .pipe g.p.flatten()
    .pipe g.dest "web/images"
    .pipe g.reload()

  deferred.promise

g.task "vendor-js", ["bower"], ->
  g.src [
      "vendor/bower/files/modernizr/**/*.js",
      "vendor/bower/files/jquery/**/*.js",
      "vendor/bower/files/jquery{.,-}*/**/*.js",
      "vendor/bower/files/**/bootstrap/**/affix.js",
      "vendor/bower/files/**/bootstrap/**/alert.js",
      "vendor/bower/files/**/bootstrap/**/button.js",
      # "vendor/bower/files/**/bootstrap/**/carousel.js",
      "vendor/bower/files/**/bootstrap/**/collapse.js",
      "vendor/bower/files/**/bootstrap/**/dropdown.js",
      "vendor/bower/files/**/bootstrap/**/tab.js",
      "vendor/bower/files/**/bootstrap/**/transition.js",
      # "vendor/bower/files/**/bootstrap/**/scrollspy.js",
      # "vendor/bower/files/**/bootstrap/**/modal.js",
      "vendor/bower/files/**/bootstrap/**/tooltip.js",
      "vendor/bower/files/**/bootstrap/**/popover.js",
      "vendor/bower/files/**/*.js"
  ]
    .pipe g.js()
    .pipe g.p.concat "vendor.js"
    .pipe g.dest "web/js"
    .pipe g.reload()
