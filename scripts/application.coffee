phpsw = init: ->
  @email.init()
  @event.init()
  @hero.init()
  @

#= require_tree modules

$ -> phpsw.init()
