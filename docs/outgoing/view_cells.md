# View Cells

Many applications have small view fragments that can be repeated from page to page, or in different places on the pages. These are often help boxes, navigation controls, ads, login forms, etc. CodeIgniter lets you encapsulate the logic for these presentation blocks within View Cells. They are basically mini-views that can be included in other views. They can have logic built in to handle any cell-specific display logic. They can be used to make your views more readable and maintainable by separating the logic for each cell into its own class.

## Simple and Controlled Cells

CodeIgniter supports two types of View Cells: simple and controlled.

**Simple View Cells** can be generated from any class and method of your choice and do not have to follow any rules, except that they must return a string.

**Controlled View Cells** must be generated from a class that extends the `CodeIgniter\View\Cells\Cell` class, which provides additional capabilities that make your View Cells more flexible and faster to use.

## Calling a View Cell

No matter which type of View Cell you are using, you can call it from any view by using the `view_cell()` helper function.

The first parameter is (1) *the name of the class and method* (Simple Cell) or (2) *the name of the class and optional method* (Controlled Cell) to call, and the second parameter is an array or string of parameters to pass to the method:

```php
// In a View.

// Simple Cell
<?= view_cell('App\Cells\MyClass::myMethod', ['param1' => 'value1', 'param2' => 'value2']) ?>

// Controlled Cell
<?= view_cell('App\Cells\MyCell', ['param1' => 'value1', 'param2' => 'value2']) ?>
```

The string that the Cell returns will be inserted into the view where the `view_cell()` function was called.

### Namespace Omission

!!! success "Available from version 4.3.0"

If you do not include the full namespace for the class, it will assume it can be found in the `App\Cells` namespace. So, the following example would attempt to find the `MyClass` class in **app/Cells/MyClass.php**. If it is not found there, all namespaces will be scanned until it is found, searching within a **Cells** subdirectory of each namespace:

```php
// In a View.
<?= view_cell('MyClass::myMethod', ['param1' => 'value1', 'param2' => 'value2']) ?>
```

### Passing Parameters as Key/Value String

You can also pass the parameters along as a key/value string:

```php
// In a View.
<?= view_cell('MyClass::myMethod', 'param1=value1, param2=value2') ?>
```

## Simple Cells

Simple Cells are classes that return a string from the chosen method. An example of a simple Alert Message cell might look like this:

```php
<?php

namespace App\Cells;

class AlertMessage
{
    public function show(array $params): string
    {
        return "<div class=\"alert alert-{$params['type']}\">{$params['message']}</div>";
    }
}
```

You would call it from within a view like this:

```php
// In a View.
<?= view_cell('AlertMessage::show', ['type' => 'success', 'message' => 'The user has been updated.']) ?>
```

Additionally, you can use parameter names that match the parameter variables in the method for better readability. When you use it this way, all of the parameters must always be specified in the view cell call:

```php
// In a View.
<?= view_cell('Blog::recentPosts', 'category=codeigniter, limit=5') ?>
```

```php
<?php

// In a Cell.

namespace App\Cells;

class Blog
{
    // ...

    public function recentPosts(string $category, int $limit): string
    {
        $posts = $this->blogModel->where('category', $category)
            ->orderBy('published_on', 'desc')
            ->limit($limit)
            ->get();

        return view('recentPosts', ['posts' => $posts]);
    }
}
```

## Controlled Cells

!!! success "Available from version 4.3.0"

Controlled cells have two primary goals: (1) to make it as fast as possible to build the cell, and (2) to provide additional logic and flexibility to your views, if they need it.

The class must extend `CodeIgniter\View\Cells\Cell`. It should have a view file in the same folder. By convention, the class name should be in PascalCase suffixed with `Cell` and the view should be the snake_cased version of the class name, without the suffix. For example, if you have a `MyCell` class, the view file should be `my.php`.

!!! note "Note"
    Prior to v4.3.5, the generated view file ended with `_cell.php`. Though v4.3.5 and newer will generate view files without the `_cell` suffix, existing view files will still be located and loaded.

### Creating a Controlled Cell

At the most basic level, all you need to implement within the class are public properties. These properties will be made available to the view file automatically.

Implementing the AlertMessage from above as a Controlled Cell would look like this:

```php
<?php

// app/Cells/AlertMessageCell.php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class AlertMessageCell extends Cell
{
    public $type;
    public $message;
}
```

```php
// app/Cells/alert_message.php
<div class="alert alert-<?= esc($type, 'attr') ?>">
    <?= esc($message) ?>
</div>
```

```php
// Called in main View:
<?= view_cell('AlertMessageCell', 'type=warning, message=Failed.') ?>
```

!!! note "Note"
    If you use typed properties, you must set the initial values:

    ```php
    <?php

    // app/Cells/AlertMessageCell.php

    namespace App\Cells;

    use CodeIgniter\View\Cells\Cell;

    class AlertMessageCell extends Cell
    {
        public string $type    = '';
        public string $message = '';
    }
    ```

### Generating Cell via Command

You can also create a controlled cell via a built-in command from the CLI. The command is `php spark make:cell`. It takes one argument, the name of the cell to create. The name should be in PascalCase, and the class will be created in the **app/Cells** directory. The view file will also be created in the **app/Cells** directory.

```console
php spark make:cell AlertMessageCell
```

### Using a Different View

You can specify a custom view filepath by setting the `view` property in the class. The view will be located like any view would be normally:

```php
<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class AlertMessageCell extends Cell
{
    public $type;
    public $message;

    protected string $view = APPPATH . 'Views/cells/alerts.php';
}
```

### Customize the Rendering

If you need more control over the rendering of the HTML, you can implement a `render()` method. This method allows you to perform additional logic and pass extra data to the view, if needed. The `render()` method must return a string.

To take advantage of the full features of controlled Cells, you should use `$this->view()` instead of the normal `view()` helper function:

```php
<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class AlertMessageCell extends Cell
{
    public $type;
    public $message;

    public function render(): string
    {
        return $this->view('my/custom/view', ['extra' => 'data']);
    }
}
```

### Computed Properties

If you need to perform additional logic for one or more properties, you can use computed properties. These require setting the property to either `protected` or `private` and implementing a public method whose name consists of the property name surrounded by `get` and `Property`:

```php
// In a View. Initialize the protected properties.
<?= view_cell('AlertMessageCell', ['type' => 'note', 'message' => 'test']) ?>
```

```php
<?php

// app/Cells/AlertMessageCell.php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class AlertMessageCell extends Cell
{
    protected $type;
    protected $message;
    private $computed;

    public function mount(): void
    {
        $this->computed = sprintf('%s - %s', $this->type, $this->message);
    }

    public function getComputedProperty(): string
    {
        return $this->computed;
    }

    public function getTypeProperty(): string
    {
        return $this->type;
    }

    public function getMessageProperty(): string
    {
        return $this->message;
    }
}
```

```php
// app/Cells/alert_message.php
<div>
    <p>type - <?= esc($type) ?></p>
    <p>message - <?= esc($message) ?></p>
    <p>computed: <?= esc($computed) ?></p>
</div>
```

!!! important "Important"
    You can't set properties that are declared as private during cell initialization. The `getDataProperty()` and `getViewProperty()` methods are not available: they are used only for internal processes.

### Presentation Methods

Sometimes you need to perform additional logic for the view, but you don't want to pass it as a parameter. You can implement a method that will be called from within the cell's view itself. This can help the readability of your views. You may have noticed that not only method calls are allowed in the template: the entire Cell object with public properties is available.

```php
<?php

// app/Cells/RecentPostsCell.php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class RecentPostsCell extends Cell
{
    protected $posts;

    public function linkPost($post): string
    {
        return anchor('posts/' . $post->id, $post->title);
    }
}
```

```php
// app/Cells/recent_posts.php
<ul>
    <?php foreach ($posts as $post): ?>
        <li><?= $this->linkPost($post) ?></li>
    <?php endforeach ?>
</ul>
```

### Performing Setup Logic

If you need to perform additional logic before the view is rendered, you can implement a `mount()` method. This method will be called just after the class is instantiated, and can be used to set additional properties or perform other logic:

```php
<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class RecentPostsCell extends Cell
{
    protected $posts;

    public function mount(): void
    {
        $this->posts = model('PostModel')->orderBy('created_at', 'DESC')->findAll(10);
    }
}
```

You can pass additional parameters to the `mount()` method by passing them as an array to the `view_cell()` helper function. Any of the parameters sent that match a parameter name of the `mount()` method will be passed in:

```php
<?php

// app/Cells/RecentPostsCell.php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class RecentPostsCell extends Cell
{
    protected $posts;

    public function mount(?int $categoryId): void
    {
        $this->posts = model('PostModel')
            ->when(
                $categoryId,
                static fn ($query, $categoryId) => $query->where('category_id', $categoryId),
            )
            ->orderBy('created_at', 'DESC')
            ->findAll(10);
    }
}
```

```php
// Called in main View:
<?= view_cell('RecentPostsCell', ['categoryId' => 5]) ?>
```

## Cell Caching

You can cache the results of the view cell call by passing the number of seconds to cache the data for as the third parameter. This will use the currently configured cache engine:

```php
// Cache the view for 5 minutes
<?= view_cell('App\Cells\Blog::recentPosts', 'limit=5', 300) ?>
```

You can provide a custom name to use instead of the auto-generated one if you like, by passing the new name as the fourth parameter:

```php
// Cache the view for 5 minutes
<?= view_cell('App\Cells\Blog::recentPosts', 'limit=5', 300, 'newcacheid') ?>
```
