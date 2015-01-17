g = require "gulp"

g.task "views", ->
  g.src "views/*/**", read: false
    .pipe g.reload()
