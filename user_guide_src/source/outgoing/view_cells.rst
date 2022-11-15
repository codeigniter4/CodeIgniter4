##########
View Cells
##########

Many applications have small view fragments that can be repeated from page to page, or in different places on the pages. These are often help boxes, navigation controls, ads, login forms, etc. CodeIgniter lets you encapsulate the logic for these presentation blocks within View Cells. They are basically mini-views that can be included in other views. They can have logic built in to handle any cell-specific display logic. They can be used to make your views more readable and maintainable by separating the logic for each cell into its own class.

CodeIgniter supports two types of View Cells: simple and controlled. Simple View Cells can be generated from any class and method of your choice and does not have to follow any rules, except that it must return a string. Controlled View Cells must be generated from a class that extends ``Codeigniter\View\Cells\Cell`` class which provides additional capability making your View Cells more flexible and faster to use.

.. contents::
    :local:
    :depth: 2

.. _app-cells:

*******************
Calling a View Cell
*******************

No matter which type of View Cell you are using, you can call it from any view by using the ``view_cell()`` helper method. The first parameter is the name of the class and method to call, and the second parameter is an array of parameters to pass to the method. The method must return a string, which will be inserted into the view where the ``view_cell()`` method was called.
::

    <?= view_cell('App\Cells\MyClass::myMethod', ['param1' => 'value1', 'param2' => 'value2']); ?>

If you do not include the full namespace for the class, it will assume in can be found in the ``App\Cells`` namespace. So, the above example would attempt to find the ``MyClass`` class in ``app/Cells/MyClass.php``. If it is not found there, all namespaces will be scanned until it is found, searching within a ``Cells`` subdirectory of each namespace.
::

    <?= view_cell('MyClass::myMethod', ['param1' => 'value1', 'param2' => 'value2']); ?>

You can also pass the parameters along as a key/value string:
::

    <?= view_cell('MyClass::myMethod', 'param1=value1, param2=value2'); ?>

************
Simple Cells
************

Simple Cells are classes that return a string from the chosen method. An example of a simple Alert Message cell might look like this:
::

    namespace App\Cells;

    class AlertMessage
    {
        public function show($params): string
        {
            return "<div class="alert alert-{$params['type']}">{$params['message']}</div>";
        }
    }

You would call it from within a view like:
::

    <?= view_cell('AlertMessage::show', ['type' => 'success', 'message' => 'The user has been updated.']); ?>

Additionally, you can use parameter names that match the parameter variables in the method for better readability.
When you use it this way, all of the parameters must always be specified in the view cell call::

    <?= view_cell('\App\Libraries\Blog::recentPosts', 'category=codeigniter, limit=5') ?>

    public function recentPosts(string $category, int $limit)
    {
        $posts = $this->blogModel->where('category', $category)
                                 ->orderBy('published_on', 'desc')
                                 ->limit($limit)
                                 ->get();

        return view('recentPosts', ['posts' => $posts]);
    }

.. _controlled-cells:

****************
Controlled Cells
****************

Controlled Cells have two primary goals: to make it as fast as possible to build the cell, and provide additional logic and flexibility to your views, if they need it. The class must extend ``CodeIgniter\View\Cells\Cell``. They should have a view file in the same folder. By convention the class name should be PascalCase and the view should be the snake_cased version of the class name. So, for example, if you have a ``MyCell`` class, the view file should be ``my_cell.php``.

Creating a Controlled Cell
==========================

At the most basic level, all you need to implement within the class are public properties. These properties will be made available to the view file automatically. Implementing the AlertMessage from above as a Controlled Cell would look like this:
::

    // app/Cells/AlertMessageCell.php
    namespace App\Cells;

    use CodeIgniter\View\Cells\Cell;

    class AlertMessage extends Cell
    {
        public $type;
        public $message;
    }

    // app/Cells/alert-message.php
    <div class="alert alert-<?= $type; ?>">
        <?= $message; ?>
    </div>

Generating Cell via Command
===========================

You can also create a controlled cell via a built in command from the CLI. The command is ``php spark make:cell``. It takes one argument, the name of the cell to create. The name should be in PascalCase, and the class will be created in the ``app/Cells`` directory. The view file will also be created in the ``app/Views/cells`` directory.

```console
php spark make:cell AlertMessage
```

Using a Different View
======================

You can specify a custom view name by setting the ``view`` property in the class. The view will be located like any view would be normally.

::

    namespace App\Cells;

    use CodeIgniter\View\Cells\Cell;

    class AlertMessage extends Cell
    {
        public $type;
        public $message;

        protected $view = 'my/custom/view';
    }

Customize the Rendering
=======================

If you need more control over the rendering of the HTML, you can implement a ``render()`` method. This method allows you to perform additional logic and pass extra data the view, if needed. The ``render()`` method must return a string. To take advantage of the full features of controlled Cells, you should use ``$this->view()`` instead of the normal ``view()`` helper function.
::

    namespace App\Cells;

    use CodeIgniter\View\Cells\Cell;

    class AlertMessage extends Cell
    {
        public $type;
        public $message;

        public function render(): string
        {
            return $this->view('my/custom/view', ['extra' => 'data']);
        }
    }

Computed Properties
===================

If you need to perform additional logic for one or more properties you can use computed properties. These require setting the property to either ``protected`` or ``private`` and implementing a public method whose name consists of the property name surrounded by ``get`` and ``Property``.
::

    namespace App\Cells;

    use CodeIgniter\View\Cells\Cell;

    class AlertMessage extends Cell
    {
        protected $type;
        protected $message;

        public function getTypeProperty(): string
        {
            return $this->type;
        }

        public function getMessageProperty(): string
        {
            return $this->message;
        }
    }

Presentation Methods
====================

Sometimes you need to perform additional logic for the view, but you don't want to pass it as a parameter. You can implement a method that will be called from within the cell's view itself. This can help the readability of your views.
::

    // app/Cells/RecentPostsCell.php
    namespace App\Cells;

    use CodeIgniter\View\Cells\Cell;

    class RecentPosts extends Cell
    {
        protected $posts;

        public function linkPost($post)
        {
            return anchor('posts/' . $post->id, $post->title);
        }
    }

    // app/Cells/recent-posts.php
    <ul>
        <?php foreach ($posts as $post): ?>
            <li><?= $this->linkPost($post) ?></li>
        <?php endforeach; ?>
    </ul>

Performing Setup Logic
======================

If you need to perform additional logic before the view is rendered, you can implement a ``mount()`` method. This method will be called just after the class is instantiated, and can be used to set additional properties or perform other logic.

::

    namespace App\Cells;

    use CodeIgniter\View\Cells\Cell;

    class RecentPosts extends Cell
    {
        protected $posts;

        public function mount()
        {
            $this->posts = model('PostModel')->getRecent();
        }
    }

You can pass additional parameters to the ``mount()`` method by passing them as an array to the ``view_cell()`` helper function. Any of the parameters sent that match a parameter name of the ``mount`` method will be passed in.
::

    // app/Cells/RecentPosts.php
    namespace App\Cells;

    use CodeIgniter\View\Cells\Cell;

    class RecentPosts extends Cell
    {
        protected $posts;

        public function mount(?int $categoryId)
        {
            $this->posts = model('PostModel')
                ->when($categoryId, function ($query, $category) {
                    return $query->where('category_id', $categoryId);
                })
                ->getRecent();
        }
    }

    // Called in main View:
    <?= view_cell('RecentPosts::show', ['categoryId' => 5]); ?>

************
Cell Caching
************

You can cache the results of the view cell call by passing the number of seconds to cache the data for as the
third parameter. This will use the currently configured cache engine.
::

    // Cache the view for 5 minutes
    <?= view_cell('\App\Libraries\Blog::recentPosts', 'limit=5', 300) ?>

You can provide a custom name to use instead of the auto-generated one if you like, by passing the new name
as the fourth parameter::

    // Cache the view for 5 minutes
    <?= view_cell('\App\Libraries\Blog::recentPosts', 'limit=5', 300, 'newcacheid') ?>
