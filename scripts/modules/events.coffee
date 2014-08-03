phpsw.events =
  init: ->
    @el = $(".events")

    return unless @el.length

    @calendar = @el.find(".events-calendar")

    @datepicker.init @

  datepicker:
    init: (e) ->
      e.el.find(".event").each (i, el) =>
        date = new Date $(el).find("time").attr("datetime")

        @events[@format date] = $(el)

      e.calendar
        .before $ "<h2>", text: "Calendar"
        .datepicker
          beforeShowDay: (date) =>
            $event = @events[@format date]

            if $event and $event.length
              $a = $event.find('h3 a')

              classes: "events-calendar__event"
              tooltip: $a.text()
            else
              enabled: false
          todayHighlight: true
        .on 'changeDate', (e) =>
          @events[@format e.date].find('a').click() if e.date

    events: {}

    format: (d) -> d.getFullYear() + @z(d.getMonth() + 1) + @z(d.getDate())

    z:   (x) -> ("0" + x).slice -2
