<!doctype html>
<html>
    <head>
        <title><?= $meetup->name ?></title>
        <link rel="stylesheet" href="/css/style.css">
    </head>
    <body>
        <div class="group">
            <div>
                <header class="box">
                    <h1><?= $meetup->name ?></h1>

                    <ul>
                        <li><a href="<?= $meetup->url ?>">Meetup page</a></li>
                        <li><a href="<?= $twitter->url ?>">Twitter @<?= $twitter->user ?></a></li>
                    </ul>
                </header>

                <section class="posts">
                    <h2>Posts</h2>

                    <?php foreach (array_slice($posts, 0, 3) as $post) : ?>
                        <?php include __DIR__ . '/post.php' ?>
                    <?php endforeach ?>
                </section>
            </div>

            <section class="events">
                <h2>Events</h2>

                <?php foreach ($events as $event) : ?>
                    <?php include __DIR__ . '/event.php' ?>
                <?php endforeach ?>
            </section>
        </div>
        <script src="http://code.jquery.com/jquery-2.1.0.min.js"></script>
        <script src="http://maps.google.com/maps/api/js?sensor=true"></script>
        <script src="/js/gmaps.js"></script>
        <script src="/js/main.js"></script>
    </body>
</html>
