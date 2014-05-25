g = module.parent.exports

g.task "build", ["clean"], -> g.start "fonts", "images", "scripts", "styles", "vendor"
