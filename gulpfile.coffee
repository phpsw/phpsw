g = module.exports = require("gulp")
g.e = process.env.NODE_ENV || "dev"
g.p = require("gulp-load-plugins")()
g.q = require("q")
g.s = require("tiny-lr")()

lazy   = require("lazypipe")
rev    = (d) -> d.getFullYear() + z(d.getMonth() + 1) + z(d.getDate()) + z(d.getHours()) + z(d.getMinutes())
z      = (x) -> ("0" + x).slice -2

g.types =
  fonts: ["eot", "svg", "ttf", "woff"]
  images: ["gif", "jpg", "jpeg", "png"]

g.css = lazy()
  .pipe g.p.sass, style: "expanded", errLogToConsole: g.e == 'dev'
  .pipe g.p.cssUrlAdjuster, prepend: "/rev/#{rev(new Date)}/"
  .pipe g.p.autoprefixer, "last 2 version", "safari 5", "ie 8", "ie 9", "opera 12.1", "ios 6", "android 4"

g.js = lazy()
  .pipe g.p.include

if g.e != "dev"
  g.css = g.css.pipe g.p.minifyCss
  g.js  = g.js.pipe g.p.uglify

g.reload = lazy().pipe g.p.livereload, g.s

require("fs").readdirSync("./gulp").forEach (task) -> require "./gulp/#{task}"
