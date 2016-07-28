##########
Pagination
##########

CodeIgniter provides a very simple, but flexible pagination library that is simple to theme, works with the model,
and capable of supporting multiple paginators on a single page.

*******************
Loading the Library
*******************

Like all services in CodeIgniter, it can be loaded via ``Config\Services``, though you usually will not need
to load it manually::

    $pager = \Config\Services::pager();

***************************
Paginating Database Results
***************************

In most cases, you will be using the Pager library in order to paginate results that you retrieve from the database.
When using the :doc:`Model </database/model>` class, you can use its built-in ``paginate()`` method to automatically
retrieve the current batch of results, as well as setup the Pager library so it's ready to use in your controllers.
It even reads the current page it should display from the current URL via a ``page=X`` query variable.

To provide a paginated list of users in your application, your controller's method would look something like::

    class UserController extends Controller
    {
        public function index()
        {
            $model = new \App\Models\UserModel();

            $data = [
                'users' => $model->paginate(10),
                'pager' => $model->pager
            ];

            echo view('users/index', $data);
        }
    }

In this example, we first create a new instance of our UserModel. Then we populate the data to sent to the view.
The first element is the results from the database, **users**, which is retrieved for the correct page, returning
10 users per page. The second item that must be sent to the view is the Pager instance itself. As a convenience,
the Model will hold on to the instance it used and store it in the public class variable, **$pager**. So, we grab
that and assign it to the $pager variable in the view.

Within the view, we then need to tell it where to display the resulting links::

    <?= $pager->links() ?>

And that's all it takes. The Pager class will render a series of links that are compatible with the Boostrap CSS
framework by default. It will have First and Last page links, as well as Next and Previous links for any pages more
than two pages on either side of the current page.

If you prefer a simpler output, you can use the ``simpleLinks()`` method, which only uses "Older" and "Newer" links,
instead of the details pagination links::

    <?= $pager->simpleLinks() ?>


Behind the scenes, the library loads a view file that determines how the links are formatted, making it simple to
modify to your needs. See below for details on how to completely customize the output.

Paginating Multiple Results
===========================

If you need to provide links from two different result sets, you can pass group names to most of the pagination
methods to keep the data separate::

    // In the Controller
    public function index()
    {
        $userModel = new \App\Models\UserModel();
        $pageModel = new \App\Models\PageModel();

        $data = [
            'users' => $userModel->paginate(10, 'group1'),
            'pages' => $pageModel->paginate(15, 'group2'),
            'pager' => $model->pager
        ];

        echo view('users/index', $data);
    }

    // In the views:
    <?= $pager->links('group1') ?>
    <?= $pager->simpleLinks('group2') ?>

Manual Pagination
=================

You may find times where you just need to create pagination based on known data. You can create links manually
with the ``makeLinks()`` method, which takes the current page, the amount of results per page, and
the total number of items as the first, second, and third parameters, respectively::

    <?= $pager->makeLinks($page, $perPage, $total) ?>

*********************
Customizing the Links
*********************
