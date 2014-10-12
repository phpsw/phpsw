g = module.parent.exports

g.task "bower", ->
  g.src g.b(), base: "vendor/bower"
    .pipe g.dest "vendor/bower/files"
