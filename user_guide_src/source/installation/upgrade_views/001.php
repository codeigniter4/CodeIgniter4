<html>
<head>
    <title><?= esc($title) ?></title>
</head>
<body>
    <h1><?= esc($heading) ?></h1>

    <h3>My Todo List</h3>

    <ul>
    <?php foreach ($todo_list as $item): ?>
        <li><?= esc($item) ?></li>
    <?php endforeach ?>
    </ul>

</body>
</html>
