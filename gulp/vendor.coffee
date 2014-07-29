g = module.parent.exports

g.task "vendor", -> g.start "vendor-css", "vendor-js"

g.task "vendor-css", ["bower"], ->
  g.src ["styles/vendor.scss", "web/vendor/**/*.css"]
    .pipe g.css()
    .pipe g.p.concat "vendor.css"
    .pipe g.dest "web/css"
    .pipe g.reload()

g.task "vendor-js", ["bower"], ->
  g.src [
      "web/vendor/jquery/**/*.js",
      "web/vendor/jquery-*/**/*.js",
      "web/vendor/**/bootstrap/**/affix.js",
      "web/vendor/**/bootstrap/**/alert.js",
      "web/vendor/**/bootstrap/**/button.js",
      # "web/vendor/**/bootstrap/**/carousel.js",
      "web/vendor/**/bootstrap/**/collapse.js",
      "web/vendor/**/bootstrap/**/dropdown.js",
      "web/vendor/**/bootstrap/**/tab.js",
      "web/vendor/**/bootstrap/**/transition.js",
      "web/vendor/**/bootstrap/**/scrollspy.js",
      # "web/vendor/**/bootstrap/**/modal.js",
      "web/vendor/**/bootstrap/**/tooltip.js",
      "web/vendor/**/bootstrap/**/popover.js",
      "web/vendor/**/*.js"
  ]
    .pipe g.js()
    .pipe g.p.concat "vendor.js"
    .pipe g.dest "web/js"
    .pipe g.reload()
