g = require "gulp"

g.task "clean", ->
  g.src ["vendor/bower/files", "web/{css,fonts,images,js}"], read: false
    .pipe g.p.clean()
