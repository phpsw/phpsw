PHPSW Website
=============

A simple [Silex](http://silex.sensiolabs.org)-based website for PHP South West UK User Group.

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
