g = require "gulp"

g.task "bower", ->
  g.src g.b(), base: "vendor/bower"
    .pipe g.dest "vendor/bower/files"
