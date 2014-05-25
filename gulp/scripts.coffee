g = module.parent.exports

g.task "scripts", ->
  g.src "scripts/**/*.coffee"
    .pipe g.p.coffee()
    .on 'error', g.p.util.log
    .pipe g.js()
    .pipe g.p.concat "main.js"
    .pipe g.dest "web/js"
    .pipe g.reload()
