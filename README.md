PHP South West UK User Group
============================

A simple [Silex](http://silex.sensiolabs.org)-based website for the [PHP South West UK User Group](http://phpsw.org.uk).

Prerequisites
-------------

- PHP >=5.4
- Redis

Setup
-----

```bash
git clone https://github.com/phpsw/phpsw.git
cd phpsw
composer install
app/console redis:restore-fixtures
```

Config
------

If you're forking this for your own meetup, you'll need you set up your own `secrets.yml` to pull in your own content from Meetup & Twitter:

```yaml
# config/secrets.yml
meetup:
    api:
        key: changeme

twitter:
    access_token: changeme
    access_token_secret: changeme

    api:
        key: changeme
        secret: changeme
```

Fixtures
--------

If you're want to test against PHPSW event data, you can simply load the fixtures into Redis:

```bash
app/console redis:restore-fixtures
```

Tasks
-----

Almost all of our content is stored in [Meetup](http://www.meetup.com/php-sw), but we cache it in Redis to save on API requests, and we also pull in tweets from our Twitter account.

If you're forking this project for your own use, you'll need to run these tasks to pull in the content from your own accounts, and you'll probably want to set them up as cron jobs in production.

```bash
app/console meetup:import:all
app/console twitter:import:all
```

Data
----

Almost all of the data we store in Redis can be considered disposable, the tasks overwrite it all on every run. This is true of everything except the hash `phpsw:slides`, where Redis is the primary store for this data (Meetup has no way concept of talks or slides).

If in the dev env, we parse event descriptions on the fly, based on a common syntax we use across our events, this is so we can derive talks, speakers and assosciated social profiles, as well as link in any slides. If in production we do this parsing when the `meetup:import:all` task is ran, so that the parsed data is cached in Redis and simply read when serving requests.
