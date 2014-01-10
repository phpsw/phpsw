<article class="event event--<?= $event->status ?> box">
    <h3>
        <a href="<?= $event->url ?>">
            <?= $event->name ?>
        </a>
    </h3>

    <?php if ((array) $event->venue) : ?>
        <p>
            <?= $event->venue->name ?>,
            <?= $event->venue->address_1 ?>,
            <?= $event->venue->city ?>
        </p>
    <?php endif ?>

    <p><?= $event->yes_rsvp_count ?> Attending</p>

    <time datetime="<?= $event->date->format(DATE_W3C) ?>">
        <?= $event->date->format('l jS F Y') ?>
    </time>
</article>
