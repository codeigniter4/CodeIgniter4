// app/Cells/recent_posts.php
<ul>
    <?php foreach ($posts as $post): ?>
        <li><?= $this->linkPost($post) ?></li>
    <?php endforeach ?>
</ul>
