<?php

namespace PHPSW\Spec\Suite;

describe("Meetup client", function () {
    global $meetup;

    describe("group", function () use ($meetup) {
        it("expects that the name is PHPSW", function () use ($meetup) {
            $group = $meetup->getGroup();

            expect($group->name)->toBe('PHPSW');
        });
    });

    describe("events", function () use ($meetup) {
        it("expects that number of events is > 25", function () use ($meetup) {
            $events = $meetup->getEvents();

            expect(count($events))->toBeGreaterThan(25);
        });
    });
});
