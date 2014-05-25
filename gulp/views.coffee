g = module.parent.exports

g.task "views", ->
  g.src "views/*/**", read: false
    .pipe g.reload()
