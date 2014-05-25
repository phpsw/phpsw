g = module.parent.exports

g.task "bower", -> g.p.bowerFiles().pipe g.dest "web/vendor"
