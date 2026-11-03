# Pagination

CodeIgniter provides a very simple but flexible pagination library that is simple to theme, works with the model, and is capable of supporting multiple paginators on a single page.

## Loading the Library

Like all services in CodeIgniter, it can be loaded via `Config\Services`, though you usually will not need to load it manually:

```php
<?php

$pager = service('pager');
```

## Paginating with Models

In most cases, you will be using the Pager library in order to paginate results that you retrieve from the database. When using the [Model](../models/model.md) class, you can use its built-in `paginate()` method to automatically retrieve the current batch of results, as well as set up the Pager library so it's ready to use in your controllers. It even reads the current page it should display from the current URL via a `page=X` query variable.

To provide a paginated list of users in your application, your controller's method would look something like:

```php
<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController
{
    public function index()
    {
        $model = model(UserModel::class);

        $data = [
            'users' => $model->paginate(10),
            'pager' => $model->pager,
        ];

        return view('users/index', $data);
    }
}
```

In this example, we first create a new instance of our `UserModel`. Then we populate the data to send to the view. The first element is the results from the database, **users**, which are retrieved for the correct page, returning 10 users per page. The second item that must be sent to the view is the Pager instance itself. As a convenience, the Model will hold on to the instance it used and store it in the public property, `$pager`. So, we grab that and assign it to the `$pager` variable in the view.

### Customizing Query for Pagination

To customize a query for pagination in a model, you can add [Query Builder](../database/query_builder.md) methods before the `paginate()` method.

#### Adding WHERE

If you want to add WHERE conditions, you can specify conditions directly:

```php
use App\Models\UserModel;

// In your Controller.
$model = model(UserModel::class);

$data = [
    'users' => $model->where('ban', 1)->paginate(10),
    'pager' => $model->pager,
];
```

You can move the conditions to a separate method:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // ...

    public function banned()
    {
        $this->builder()->where('ban', 1);

        return $this; // This will allow the call chain to be used.
    }
}
```

```php
use App\Models\UserModel;

// In your Controller.
$model = model(UserModel::class);

$data = [
    'users' => $model->banned()->paginate(10),
    'pager' => $model->pager,
];
```

#### Adding JOIN

You can join another table:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table = 'news';

    // ...

    public function getPagination(?int $perPage = null): array
    {
        $this->builder()
            ->select('news.*, category.name')
            ->join('category', 'news.category_id = category.id');

        return [
            'news'  => $this->paginate($perPage),
            'pager' => $this->pager,
        ];
    }
}
```

!!! important "Important"
    It is important to understand that the `Model::paginate()` method uses the **Model** and the **Query Builder** instance in the Model. Therefore, trying to use `Model::paginate()` with [$db->query()](../database/queries.md#db-query) **will not work** because `$db->query()` executes the query immediately and is not associated with the Query Builder.

If you need a complicated SQL query that you cannot write with Query Builder, try using [$db->query()](../database/queries.md#db-query) and [Manual Pagination](#manual-pagination).

### Displaying Pager Links

Within the view, we then need to tell it where to display the resulting links:

```
<?= $pager->links() ?>
```

And that's all it takes. The Pager class will render First and Last page links, as well as Next and Previous links for any pages more than two pages on either side of the current page.

It is important to be aware that the library pattern for Next and Previous is different from what is used in the traditional way of paging results.

Next and Previous here are linked to the group of links to be displayed in the pagination structure, and not to the next or previous page of records.

If you prefer a simpler output, you can use the `simpleLinks()` method, which only uses "Older" and "Newer" links, instead of the detailed pagination links:

```
<?= $pager->simpleLinks() ?>
```

Behind the scenes, the library loads a view file that determines how the links are formatted, making it simple to modify to your needs. See below for details on how to completely customize the output.

### Paginating Multiple Results

If you need to provide links from two different result sets, you can pass group names to most of the pagination methods to keep the data separate:

```php
<?php

namespace App\Controllers;

use App\Models\PageModel;
use App\Models\UserModel;

class UserController extends BaseController
{
    public function index()
    {
        $userModel = model(UserModel::class);
        $pageModel = model(PageModel::class);

        $data = [
            'users' => $userModel->paginate(10, 'group1'),
            'pages' => $pageModel->paginate(15, 'group2'),
            'pager' => $userModel->pager,
        ];

        echo view('users/index', $data);
    }
}
?>

<!-- In your view file: -->
<?= $pager->links('group1') ?>
<?= $pager->simpleLinks('group2') ?>
```

### Setting Page Manually

If you need to specify which page of results to return, you can specify the page as the 3rd argument. This can be handy when you use a different way than the default `$_GET` variable to control which page to show.

```php
<?php

use App\Models\UserModel;

$userModel = model(UserModel::class);
$page      = 3;

$users = $userModel->paginate(10, 'group1', $page);
```

### Specifying the URI Segment for Page

It is also possible to use a URI segment for the page number, instead of the page query parameter. Simply specify the segment number to use as the fourth argument. URIs generated by the pager would then look like **https://domain.tld/foo/bar/[pageNumber]** instead of **https://domain.tld/foo/bar?page=[pageNumber]**.

```php
<?php

$users = $userModel->paginate(10, 'group1', null, $segment);
```

Please note: the `$segment` value cannot be greater than the number of URI segments plus 1.

## Manual Pagination

There may be times when you just need to create pagination based on known data. You can create links manually with the `makeLinks()` method, which takes the current page, the number of results per page, and the total number of items as the first, second, and third parameters, respectively:

```php
<?php

namespace App\Controllers;

class UserController extends BaseController
{
    public function index()
    {
        // ...

        $pager = service('pager');

        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        $total   = 200;

        // Call makeLinks() to make pagination links.
        $pager_links = $pager->makeLinks($page, $perPage, $total);

        $data = [
            // ...
            'pager_links' => $pager_links,
        ];

        return view('users/index', $data);
    }
}
?>

<!-- In your view file: -->
<?= $pager_links ?>
```

This will, by default, display the links in the normal manner, as a series of links, but you can change the display template used by passing in the name of the template as the fourth parameter. More details can be found in the following sections:

```
$pager->makeLinks($page, $perPage, $total, 'template_name');
```

It is also possible to use a URI segment for the page number, instead of the page query parameter, as described in the previous section. Specify the segment number to use as the fifth parameter to `makeLinks()`:

```
$pager->makeLinks($page, $perPage, $total, 'template_name', $segment);
```

Please note: the `$segment` value cannot be greater than the number of URI segments plus 1.

If you need to show many pagers on one page, then an additional parameter that defines a group can be helpful:

```php
<?php

$pager = service('pager');
$pager->setPath('path/for/my-group', 'my-group'); // Additionally you could define path for every group.
$pager->makeLinks($page, $perPage, $total, 'template_name', $segment, 'my-group');
```

The Pagination library uses the **page** query parameter for HTTP queries by default (if no group or the `default` group name is given) or `page_[groupName]` for custom group names.

## Paginating with Only Expected Queries

By default, all GET queries are shown in the pagination links.

For example, when accessing the URL **https://domain.tld?search=foo&order=asc&hello=i+am+here&page=2**, the page 3 link can be generated, along with the other links, as follows:

```php
<?php

echo $pager->links();
// Page 3 link: https://domain.tld?search=foo&order=asc&hello=i+am+here&page=3
```

The `only()` method allows you to limit this just to queries already expected:

```php
<?php

echo $pager->only(['search', 'order'])->links();
// Page 3 link: https://domain.tld?search=foo&order=asc&page=3
```

The *page* query is enabled by default. The `only()` method applies to all pagination links.

## Customizing the Links

### View Configuration

When the links are rendered out to the page, they use a view file to describe the HTML. You can easily change the view that is used by editing **app/Config/Pager.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pager extends BaseConfig
{
    public $templates = [
        'default_full'   => 'CodeIgniter\Pager\Views\default_full',
        'default_simple' => 'CodeIgniter\Pager\Views\default_simple',
    ];

    // ...
}
```

This setting stores the alias and [namespaced view paths](../outgoing/views.md) for the view that should be used. The `default_full` and `default_simple` views are used for the `links()` and `simpleLinks()` methods, respectively. To change the way those are displayed application-wide, you could assign a new view here.

For example, say you create a new view file that works with the Foundation CSS framework, and you place that file at **app/Views/Pagers/foundation_full.php**. Since the **app** directory is namespaced as `App`, and all directories underneath it map directly to segments of the namespace, you can locate the view file through its namespace:

```
'default_full' => 'App\Views\Pagers\foundation_full'
```

Since it is under the standard **app/Views** directory, though, you do not need to namespace it since the `view()` method can locate it by filename. In that case, you can simply give the sub-directory and file name:

```
'default_full' => 'Pagers/foundation_full'
```

Once you have created the view and set it in the configuration, it will automatically be used. You don't have to replace the existing templates. You can create as many additional templates as you need in the configuration file. A common situation would be needing different styles for the frontend and the backend of your application.

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pager extends BaseConfig
{
    public $templates = [
        'default_full'   => 'CodeIgniter\Pager\Views\default_full',
        'default_simple' => 'CodeIgniter\Pager\Views\default_simple',
        'front_full'     => 'App\Views\Pagers\foundation_full',
    ];

    // ...
}
```

Once configured, you can specify it as the last parameter in the `links()`, `simpleLinks()`, and `makeLinks()` methods:

```
<?= $pager->links('group1', 'front_full') ?>
<?= $pager->simpleLinks('group2', 'front_full') ?>
<?= $pager->makeLinks($page, $perPage, $total, 'front_full') ?>
```

### Creating the View

When you create a new view, you only need to create the code that is needed for creating the pagination links themselves. You should not create unnecessary wrapping divs, since the view might be used in multiple places and they would only limit its usefulness. It is easiest to demonstrate creating a new view by showing you the existing `default_full` template:

```php
<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Page navigation">
    <ul class="pagination">
    <?php if ($pager->hasPrevious()) : ?>
        <li>
            <a href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
                <span aria-hidden="true"><?= lang('Pager.first') ?></span>
            </a>
        </li>
        <li>
            <a href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>">
                <span aria-hidden="true"><?= lang('Pager.previous') ?></span>
            </a>
        </li>
    <?php endif ?>

    <?php foreach ($pager->links() as $link): ?>
        <li <?= $link['active'] ? 'class="active"' : '' ?>>
            <a href="<?= $link['uri'] ?>">
                <?= $link['title'] ?>
            </a>
        </li>
    <?php endforeach ?>

    <?php if ($pager->hasNext()) : ?>
        <li>
            <a href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>">
                <span aria-hidden="true"><?= lang('Pager.next') ?></span>
            </a>
        </li>
        <li>
            <a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>">
                <span aria-hidden="true"><?= lang('Pager.last') ?></span>
            </a>
        </li>
    <?php endif ?>
    </ul>
</nav>
```

#### setSurroundCount()

In the first line, the `setSurroundCount()` method specifies that we want to show two links to either side of the current page link. The only parameter that it accepts is the number of links to show.

!!! note "Note"
    You must call this method first to generate correct pagination links.

#### hasPrevious() & hasNext()

These methods return a boolean `true` if there are more links that can be displayed on either side of the current page, based on the value passed to [setSurroundCount()](#setsurroundcount).

For example, let's say we have 20 pages of data. The current page is page 3. If the surrounding count is 2, then the following links would show up like this:

```
1  |  2  |  3  |  4  |  5
```

Since the first link displayed is page one, `hasPrevious()` would return `false` since there is no page zero. However, `hasNext()` would return `true` since there are 15 additional pages of results after page five.

#### getPrevious() & getNext()

These methods return the **URL** for the previous or next pages of results on either side of the numbered links.

For example, if you have the current page set at 5 and you want the links before and after (the surroundCount) to be 2 each, that will give you something like this:

```
3  |  4  |  5  |  6  |  7
```

`getPrevious()` returns the URL for page 2. `getNext()` returns the URL for page 8.

If you want to get page 4 and page 6, use [getPreviousPage() & getNextPage()](#getpreviouspage-getnextpage) instead.

#### getFirst() & getLast()

Much like [getPrevious() & getNext()](#getprevious-getnext), these methods return the **URL** to the first and last pages in the result set.

#### links()

Returns an array of data about all of the numbered links. Each link's array contains the URI for the link, the title, which is just the number, and a boolean that tells whether the link is the current/active link or not:

```php
<?php

$link = [
    'active' => false,
    'uri'    => 'https://example.com/foo?page=2',
    'title'  => 1,
];
```

In the code presented for the standard pagination structure, the methods [getPrevious() & getNext()](#getprevious-getnext) are used to obtain the links to the previous and next pagination groups, respectively.

If you want to use the pagination structure where prev and next will be links to the previous and next pages based on the current page, just replace the [getPrevious() & getNext()](#getprevious-getnext) methods with [getPreviousPage() & getNextPage()](#getpreviouspage-getnextpage), and the methods [hasPrevious() & hasNext()](#hasprevious-hasnext) with [hasPreviousPage() & hasNextPage()](#haspreviouspage-hasnextpage), respectively.

See the following example with these changes:

```php
<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
    <ul class="pagination">
        <?php if ($pager->hasPreviousPage()) : ?>
            <li>
                <a href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
                    <span aria-hidden="true"><?= lang('Pager.first') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getPreviousPage() ?>" aria-label="<?= lang('Pager.previous') ?>">
                    <span aria-hidden="true"><?= lang('Pager.previous') ?></span>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link): ?>
            <li <?= $link['active'] ? 'class="active"' : '' ?>>
                <a href="<?= $link['uri'] ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNextPage()) : ?>
            <li>
                <a href="<?= $pager->getNextPage() ?>" aria-label="<?= lang('Pager.next') ?>">
                    <span aria-hidden="true"><?= lang('Pager.next') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>">
                    <span aria-hidden="true"><?= lang('Pager.last') ?></span>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>
```

#### hasPreviousPage() & hasNextPage()

These methods return a boolean `true` if there are links to a page before and after, respectively, the current page being displayed.

For example, let's say we have 20 pages of data. The current page is page 3. If the surrounding count is 2, then the following links would show up like this:

```
1  |  2  |  3  |  4  |  5
```

`hasPreviousPage()` would return `true` since page 2 exists, and `hasNextPage()` would return `true` since page 4 exists.

!!! note "Note"
    The difference from [hasPrevious() & hasNext()](#hasprevious-hasnext) is that these methods are based on the current page, while [hasPrevious() & hasNext()](#hasprevious-hasnext) are based on the set of links to be displayed before and after the current page based on the value passed in [setSurroundCount()](#setsurroundcount).

#### getPreviousPage() & getNextPage()

These methods return a **URL** for the previous and next pages in relation to the current page being displayed.

For example, if you have the current page set at 5 and you want the links before and after (the surroundCount) to be 2 each, that will give you something like this:

```
3  |  4  |  5  |  6  |  7
```

`getPreviousPage()` returns the URL for page 4. `getNextPage()` returns the URL for page 6.

!!! note "Note"
    [getPrevious() & getNext()](#getprevious-getnext) return the URL for the previous or next pages of results on either side of the numbered links.

If you want page numbers instead of URLs, you can use the following methods:

#### getPreviousPageNumber() & getNextPageNumber()

These methods return the page number for the previous or next pages in relation to the current page being displayed.

#### getFirstPageNumber() & getLastPageNumber()

These methods return the page numbers of the first and last pages in the set of links to be displayed. For example, if the set of links to be displayed is something like this:

```
3  |  4  |  5  |  6  |  7
```

`getFirstPageNumber()` will return 3 while `getLastPageNumber()` will return 7.

!!! note "Note"
    To obtain the page numbers of the first and last pages in the entire result set, you can use the following approach: The first page number is always 1, and [getPageCount()](#getpagecount) can be used to retrieve the last page number.

#### getCurrentPageNumber()

This method returns the page number of the current page.

#### getPageCount()

This method returns the total number of pages.

### Displaying the Number of Items on the Page

!!! success "Available from version 4.6.0"

When paginating items, it’s often helpful to display the total number of items and the range of items shown on the current page. To simplify this task, new methods have been added. These methods make it easier to manage and display pagination details. Here's an example:

```php
<?php $pager->setSurroundCount(1) ?>

<p>
    Showing <span class="font-medium"><?= $pager->getPerPageStart() ?></span>
    to <span class="font-medium"><?= $pager->getPerPageEnd() ?></span>
    of <span class="font-medium"><?= $pager->getTotal() ?></span> results
</p>
```

#### getTotal()

Returns the total number of items.

#### getPerPage()

Returns the number of items to be displayed on the page.

#### getPerPageStart()

Returns the number of the item the page starts with.

#### getPerPageEnd()

Returns the number of the item the page ends with.
