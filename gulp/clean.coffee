g = module.parent.exports

g.task "clean", ->
  g.src ["vendor/bower/files", "web/{css,fonts,images,js}"], read: false
    .pipe g.p.clean()
