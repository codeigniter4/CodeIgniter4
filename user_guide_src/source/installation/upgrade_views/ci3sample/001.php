<html>
<head>
    <title><?php echo html_escape($title); ?></title>
</head>
<body>
    <h1><?php echo html_escape($heading); ?></h1>

    <h3>My Todo List</h3>

    <ul>
    <?php foreach ($todo_list as $item): ?>
        <li><?php echo html_escape($item); ?></li>
    <?php endforeach; ?>
    </ul>

</body>
</html>
