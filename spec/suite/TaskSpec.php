<?php

namespace PHPSW\Spec\Suite;

describe("Task", function () {
    describe("meetup:import:all", function () {
        it("expects that the task runs", function () {
            $stdout = task('meetup:import:all');

            expect($stdout)->toMatch(trimlns('#Group: \.
                Events: \.+
                Photos: \.+
                Posts: \.+
                Reviews: \.+
                Members: \.+
                Speakers: \.+
                Talks: \.+
            #'));
        });
    });

    describe("redis:restore-fixtures", function () {
        it("expects that the task runs", function () {
            $stdout = task('redis:restore-fixtures');

            expect($stdout)->toMatch(trimlns('#events: \.+
                group: \.
                members: \.+
                photos: \.+
                posts: \.+
                reviews: \.+
                slides: \.+
                speakers: \.+
                talks: \.+
            #'));
        });
    });

    describe("twitter:import:all", function () {
        it("expects that the task runs", function () {
            $stdout = task('twitter:import:all');

            expect($stdout)->toMatch('#Tweets: \.+\n#');
        });
    });

    afterEach(function () {
        global $redis;

        $redis->del('events');
        $redis->del('group');
        $redis->del('members');
        $redis->del('photos');
        $redis->del('posts');
        $redis->del('reviews');
        $redis->del('slides');
        $redis->del('speakers');
        $redis->del('talks');
        $redis->del('tweets');
    });
});
