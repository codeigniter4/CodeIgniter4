News section
###############################################################################

In the last section, we went over some basic concepts of the framework
by writing a class that references static pages. We cleaned up the URI by
adding custom routing rules. Now it's time to introduce dynamic content
and start using a database.

Create a database to work with
-------------------------------------------------------

The CodeIgniter installation assumes that you have set up an appropriate
database, as outlined in the :doc:`requirements </intro/requirements>`.
In this tutorial, we provide SQL code for a MySQL database, and
we also assume that you have a suitable client for issuing database
commands (mysql, MySQL Workbench, or phpMyAdmin).

You need to create a database that can be used for this tutorial,
and then configure CodeIgniter to use it.

Using your database client, connect to your database and run the SQL command below (MySQL).
Also, add some seed records. For now, we'll just show you the SQL statements needed
to create the table, but you should be aware that this can be done programmatically
once you are more familiar with CodeIgniter; you can read about :doc:`Migrations <../dbmgmt/migration>`
and :doc:`Seeds <../dbmgmt/seeds>` to create more useful database setups later.

::

    CREATE TABLE news (
        id int(11) NOT NULL AUTO_INCREMENT,
        title varchar(128) NOT NULL,
        slug varchar(128) NOT NULL,
        body text NOT NULL,
        PRIMARY KEY (id),
        KEY slug (slug)
    );

A note of interest: a "slug", in the context of web publishing, is a
user- and SEO-friendly short text used in a URL to identify and describe a resource.

The seed records might be something like:

::

    INSERT INTO news VALUES
    (1,'Elvis sighted','elvis-sighted','Elvis was sighted at the Podunk internet cafe. It looked like he was writing a CodeIgniter app.'),
    (2,'Say it isn\'t so!','say-it-isnt-so','Scientists conclude that some programmers have a sense of humor.'),
    (3,'Caffeination, Yes!','caffeination-yes','World\'s largest coffee shop open onsite nested coffee shop for staff only.');

Connect to your database
-------------------------------------------------------

The local configuration file, ``.env``, that you created when you installed
CodeIgniter, should have the database property settings uncommented and
set appropriately for the database you want to use. Make sure you've configured
your database properly as described :doc:`here <../database/configuration>`.

::

    database.default.hostname = localhost
    database.default.database = ci4tutorial
    database.default.username = root
    database.default.password = root
    database.default.DBDriver = MySQLi

Setting up your model
-------------------------------------------------------

Instead of writing database operations right in the controller, queries
should be placed in a model, so they can easily be reused later. Models
are the place where you retrieve, insert, and update information in your
database or other data stores. They provide access to your data.
You can read more about it :doc:`here </models/model>`.

Open up the **app/Models/** directory and create a new file called
**NewsModel.php** and add the following code.

::

    <?php namespace App\Models;

    use CodeIgniter\Model;

    class NewsModel extends Model
    {
        protected $table = 'news';
    }

This code looks similar to the controller code that was used earlier. It
creates a new model by extending ``CodeIgniter\Model`` and loads the database
library. This will make the database class available through the
``$this->db`` object.

Now that the database and a model have been set up, you'll need a method
to get all of our posts from our database. To do this, the database
abstraction layer that is included with CodeIgniter —
:doc:`Query Builder <../database/query_builder>` — is used. This makes it
possible to write your 'queries' once and make them work on :doc:`all
supported database systems <../intro/requirements>`. The Model class
also allows you to easily work with the Query Builder and provides
some additional tools to make working with data simpler. Add the
following code to your model.

::

    public function getNews($slug = false)
    {
        if ($slug === false)
        {
            return $this->findAll();
        }

        return $this->asArray()
                    ->where(['slug' => $slug])
                    ->first();
    }

With this code, you can perform two different queries. You can get all
news records, or get a news item by its `slug <#>`_. You might have
noticed that the ``$slug`` variable wasn't sanitized before running the
query; :doc:`Query Builder <../database/query_builder>` does this for you.

The two methods used here, ``findAll()`` and ``first()``, are provided
by the Model class. They already know the table to use based on the ``$table``
property we set in **NewsModel** class, earlier. They are helper methods
that use the Query Builder to run their commands on the current table, and
returning an array of results in the format of your choice. In this example,
``findAll()`` returns an array of objects.

Display the news
-------------------------------------------------------

Now that the queries are written, the model should be tied to the views
that are going to display the news items to the user. This could be done
in our ``Pages`` controller created earlier, but for the sake of clarity,
a new ``News`` controller is defined. Create the new controller at
**app/Controllers/News.php**.

::

    <?php namespace App\Controllers;

    use App\Models\NewsModel;
    use CodeIgniter\Controller;

    class News extends Controller
    {
        public function index()
        {
            $model = new NewsModel();

            $data['news'] = $model->getNews();
        }

        public function view($slug = null)
        {
            $model = new NewsModel();

            $data['news'] = $model->getNews($slug);
        }
    }

Looking at the code, you may see some similarity with the files we
created earlier. First, it extends a core CodeIgniter class, ``Controller``,
which provides a couple of helper methods, and makes sure that you have
access to the current ``Request`` and ``Response`` objects, as well as the
``Logger`` class, for saving information to disk.

Next, there are two methods, one to view all news items, and one for a specific
news item. You can see that the ``$slug`` variable is passed to the model's
method in the second method. The model is using this slug to identify the
news item to be returned.

Now the data is retrieved by the controller through our model, but
nothing is displayed yet. The next thing to do is, passing this data to
the views. Modify the ``index()`` method to look like this::

    public function index()
    {
        $model = new NewsModel();

        $data = [
            'news'  => $model->getNews(),
            'title' => 'News archive',
        ];

        echo view('templates/header', $data);
        echo view('news/overview', $data);
        echo view('templates/footer', $data);
    }

The code above gets all news records from the model and assigns it to a
variable. The value for the title is also assigned to the ``$data['title']``
element and all data is passed to the views. You now need to create a
view to render the news items. Create **app/Views/news/overview.php**
and add the next piece of code.

::

    <h2><?= esc($title); ?></h2>

    <?php if (! empty($news) && is_array($news)) : ?>

        <?php foreach ($news as $news_item): ?>

            <h3><?= esc($news_item['title']); ?></h3>

            <div class="main">
                <?= esc($news_item['body']); ?>
            </div>
            <p><a href="/news/<?= esc($news_item['slug'], 'url'); ?>">View article</a></p>

        <?php endforeach; ?>

    <?php else : ?>

        <h3>No News</h3>

        <p>Unable to find any news for you.</p>

    <?php endif ?>


.. note:: We are again using using **esc()** to help prevent XSS attacks.
    But this time we also passed "url" as a second parameter. That's because
    attack patterns are different depending on the context in which the output
    is used. You can read more about it :doc:`here </general/common_functions>`.

Here, each news item is looped and displayed to the user. You can see we
wrote our template in PHP mixed with HTML. If you prefer to use a template
language, you can use CodeIgniter's :doc:`View
Parser </outgoing/view_parser>` or a third party parser.

The news overview page is now done, but a page to display individual
news items is still absent. The model created earlier is made in such
a way that it can easily be used for this functionality. You only need to
add some code to the controller and create a new view. Go back to the
``News`` controller and update the ``view()`` method with the following:

::

    public function view($slug = NULL)
    {
        $model = new NewsModel();

        $data['news'] = $model->getNews($slug);

        if (empty($data['news']))
        {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the news item: '. $slug);
        }

        $data['title'] = $data['news']['title'];

        echo view('templates/header', $data);
        echo view('news/view', $data);
        echo view('templates/footer', $data);
    }

Instead of calling the ``getNews()`` method without a parameter, the
``$slug`` variable is passed, so it will return the specific news item.
The only thing left to do is create the corresponding view at
**app/Views/news/view.php**. Put the following code in this file.

::

    <h2><?= esc($news['title']); ?></h2>
    <?= esc($news['body']); ?>

Routing
-------------------------------------------------------

Because of the wildcard routing rule created earlier, you need an extra
route to view the controller that you just made. Modify your routing file
(**app/Config/Routes.php**) so it looks as follows.
This makes sure the requests reach the ``News`` controller instead of
going directly to the ``Pages`` controller. The first line routes URI's
with a slug to the ``view()`` method in the ``News`` controller.

::

    $routes->get('news/(:segment)', 'News::view/$1');
    $routes->get('news', 'News::index');
    $routes->get('(:any)', 'Pages::view/$1');

Point your browser to your "news" page, i.e. ``localhost:8080/news``,
you should see a list of the news items, each of which has a link
to display just the one article.

.. image:: ../images/tutorial2.png
    :align: center
