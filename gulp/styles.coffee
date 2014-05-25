g = module.parent.exports

g.task "styles", ->
  g.src "styles/*.scss"
    .pipe g.p.sass style: "expanded", errLogToConsole: g.e == 'dev'
    .pipe g.css()
    .pipe g.p.autoprefixer "last 2 version", "safari 5", "ie 8", "ie 9", "opera 12.1", "ios 6", "android 4"
    .pipe g.p.concat "main.css"
    .pipe g.dest "web/css"
    .pipe g.reload()
