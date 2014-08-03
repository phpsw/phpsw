phpsw =
  init: ->
    @modules()
    @pjax()
    @nprogress()

  modules: ->
    @email.init()
    @event.init()
    @events.init()
    @hero.init()

  pjax: ->
    $(document)
      .pjax 'a:not([href*="images"])', '.content',
        fragment: '.content',
        timeout: 10000
      .on 'pjax:success', => @modules()

  nprogress: ->
    NProgress.configure
      ease: 'ease'
      minimum: .75
      showSpinner: false
      speed: 500

    $(document)
      .on "pjax:start",    -> NProgress.start()
      .on "pjax:complete", -> NProgress.done()
      .on "pjax:end",      -> NProgress.remove()

#= require_tree modules

$ -> phpsw.init()
