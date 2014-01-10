<article class="post box">
    <h3>
        <a href="<?= $post->url ?>">
            <?= $post->subject ?>
        </a>
    </h3>

    <time datetime="<?= $post->last_post->created_date->format(DATE_W3C) ?>">
        <?= $post->last_post->created_date->format('l jS F Y') ?>
    </time>
</article>
