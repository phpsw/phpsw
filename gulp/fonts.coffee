g = require "gulp"

g.task "fonts", ["bower"], ->
  g.src ["fonts/**/*.{#{g.types.fonts}}", "vendor/bower/files/**/*.{#{g.types.fonts}}"]
    .pipe g.p.flatten()
    .pipe g.dest "web/fonts"
    .pipe g.reload()
