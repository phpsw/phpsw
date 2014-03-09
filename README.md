PHPSW Website
=============

A simple [Silex](http://silex.sensiolabs.org)-based website for PHP South West UK User Group.

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
```

Config
------

You'll need you set up your own `secrets.yml`:

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

Tasks
-----

A good portion of the content is grabbed from Meetup & Twitter, and stored in Redis, a couple of tasks manage it all, you'll need to run these to get started.

```bash
app/console meetup:import:all
app/console twitter:import:all
```

Meetup
------

Almost all of our data resides in Meetup, when we grab event descriptions we try to derive what talks feature in them so that they can be highlighted in the templates, mapped to members or Twitter profiles, and so that slides can be attached.

Slide URL's are stored in the redis hset `phpsw:slides` and map to talk ID's we made up when we stored them (based on the event ID and title sluggified).
