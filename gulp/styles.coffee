g = module.parent.exports

g.task "styles", ->
  g.src "styles/application.scss"
    .pipe g.css()
    .pipe g.p.concat "main.css"
    .pipe g.dest "web/css"
    .pipe g.reload()

  return
