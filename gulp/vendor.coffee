g = module.parent.exports

g.task "vendor", -> g.start "vendor-css", "vendor-js"

g.task "vendor-css", ["bower"], ->
  g.src "web/vendor/**/*.css"
    .pipe g.css()
    .pipe g.p.concat "vendor.css"
    .pipe g.dest "web/css"
    .pipe g.reload()

g.task "vendor-js", ["bower"], ->
  g.src [
      "web/vendor/jquery/**/*.js",
      "web/vendor/jquery-*/**/*.js",
      "web/vendor/bootstrap/**/*.js",
      "web/vendor/**/*.js"
  ]
    .pipe g.js()
    .pipe g.p.concat "vendor.js"
    .pipe g.dest "web/js"
    .pipe g.reload()
