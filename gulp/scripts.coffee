g = require "gulp"

g.task "scripts", ->
  g.src "scripts/application.coffee"
    .pipe g.p.include()
    .pipe g.p.coffee().on("error", g.p.util.log)
    .pipe g.js()
    .pipe g.p.concat "main.js"
    .pipe g.dest "web/js"
    .pipe g.reload()

  return
