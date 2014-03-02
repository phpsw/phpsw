<article class="event event--<?= $event->status ?> box">
    <h3>
        <a href="<?= $event->url ?>">
            <?= $event->name ?>
        </a>
    </h3>

    <?php if ((array) $event->venue) : ?>
        <p
            class="event__venue"
            <?php if ($event->venue->lat) : ?>data-latitude="<?= $event->venue->lat ?>"<?php endif ?>
            <?php if ($event->venue->lon) : ?>data-longitude="<?= $event->venue->lon ?>"<?php endif ?>
        >
            <span class="event__venue__name"><?= $event->venue->name ?></span>,
            <?= $event->venue->address_1 ?>,
            <?= $event->venue->city ?>
        </p>
    <?php endif ?>

    <p><?= $event->yes_rsvp_count ?> Attending</p>

    <time datetime="<?= $event->date->format(DATE_W3C) ?>">
        <?= $event->date->format('l jS F Y') ?>
    </time>
</article>
