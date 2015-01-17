g = require "gulp"

g.task "watch", ->
  g.s.listen 35729, (err) ->
    return console.log(err) if err

    g.watch "bower.json", ["vendor"]
    g.watch ["fonts/**/*", "vendor/bower/files/**/fonts/*"], ["fonts"]
    g.watch "images/**/*", ["images"]
    g.watch "scripts/**/*.coffee", ["scripts"]
    g.watch "styles/**/*.scss", ["styles"]
    g.watch "{app,config,views}/**/*", ["views"]
    g.watch "styles/_variables.scss", ["vendor-css"]
