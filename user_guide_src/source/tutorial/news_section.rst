############
News section
############

In the last section, we went over some basic concepts of the framework
by writing a class that includes static pages. We cleaned up the URI by
adding custom routing rules. Now it's time to introduce dynamic content
and start using a database.

Setting up your model
---------------------

Instead of writing database operations right in the controller, queries
should be placed in a model, so they can easily be reused later. Models
are the place where you retrieve, insert, and update information in your
database or other data stores. They provide access to your data.

Open up the *application/Models/* directory and create a new file called
*NewsModel.php* and add the following code. Make sure you've configured
your database properly as described :doc:`here <../database/configuration>`.

::

	<?php

	namespace App\Models;

	class NewsModel extends \CodeIgniter\Model
	{
		protected $table = 'news';
	}

This code looks similar to the controller code that was used earlier. It
creates a new model by extending ``CodeIgniter\Model`` and loads the database
library. This will make the database class available through the
``$this->db`` object.

Before querying the database, a database schema has to be created.
Connect to your database and run the SQL command below (MySQL).
Also add some seed records. For now, we'll just show you the query needed
to create the table, but you should read about :doc:`Migrations <../database/migration>`
and :doc:`Seeds <../database/seeds>` to create more useful database setups.

::

	CREATE TABLE news (
		id int(11) NOT NULL AUTO_INCREMENT,
		title varchar(128) NOT NULL,
		slug varchar(128) NOT NULL,
		text text NOT NULL,
		PRIMARY KEY (id),
		KEY slug (slug)
	);

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

With this code you can perform two different queries. You can get all
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
----------------

Now that the queries are written, the model should be tied to the views
that are going to display the news items to the user. This could be done
in our ``Pages`` controller created earlier, but for the sake of clarity,
a new ``News`` controller is defined. Create the new controller at
*application/Controllers/News.php*.

::

	<?php namespace App\Controllers;

	use App\Models\NewsModel;

	class News extends \CodeIgniter\Controller
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

Next, there are two methods to view all news items and one for a specific
news item. You can see that the ``$slug`` variable is passed to the model's
method in the second method. The model is using this slug to identify the
news item to be returned.

Now the data is retrieved by the controller through our model, but
nothing is displayed yet. The next thing to do is passing this data to
the views. Modify the ``index()`` method to look like this::

	public function index()
	{
		$model = new NewsModel();

		$data = [
			'news'  => $model->getNews(),
			'title' => 'News archive',
		];

		echo view('templates/header', $data);
		echo view('news/index', $data);
		echo view('templates/footer');
	}

The code above gets all news records from the model and assigns it to a
variable. The value for the title is also assigned to the ``$data['title']``
element and all data is passed to the views. You now need to create a
view to render the news items. Create *application/Views/news/index.php*
and add the next piece of code.

::

	<h2><?= $title ?></h2>

	<?php if (! empty($news) && is_array($news)) : ?>

		<?php foreach ($news as $news_item): ?>

			<h3><?= $news_item['title'] ?></h3>

			<div class="main">
				<?= $news_item['text'] ?>
			</div>
			<p><a href="<?= '/news/'.$news_item['slug'] ?>">View article</a></p>

		<?php endforeach; ?>

	<?php else : ?>

		<h3>No News</h3>

		<p>Unable to find any news for you.</p>

	<?php endif ?>

Here, each news item is looped and displayed to the user. You can see we
wrote our template in PHP mixed with HTML. If you prefer to use a template
language, you can use CodeIgniter's :doc:`View
Parser <../general/view_parser>` or a third party parser.

The news overview page is now done, but a page to display individual
news items is still absent. The model created earlier is made in such
way that it can easily be used for this functionality. You only need to
add some code to the controller and create a new view. Go back to the
``News`` controller and update ``view()`` with the following:

::

	public function view($slug = NULL)
	{
		$model = new NewsModel();

		$data['news'] = $model->getNews($slug);

		if (empty($data['news']))
		{
			throw new \CodeIgniter\PageNotFoundException('Cannot find the page: '. $slug);
		}

		$data['title'] = $data['news']['title'];

		echo view('templates/header', $data);
		echo view('news/view', $data);
		echo view('templates/footer');
	}

Instead of calling the ``getNews()`` method without a parameter, the
``$slug`` variable is passed, so it will return the specific news item.
The only things left to do is create the corresponding view at
*application/Views/news/view.php*. Put the following code in this file.

::

	<?php
	echo '<h2>'.$news['title'].'</h2>';
	echo $news['text'];

Routing
-------

Because of the wildcard routing rule created earlier, you need an extra
route to view the controller that you just made. Modify your routing file
(*application/config/routes.php*) so it looks as follows.
This makes sure the requests reach the ``News`` controller instead of
going directly to the ``Pages`` controller. The first line routes URI's
with a slug to the ``view()`` method in the ``News`` controller.

::

	$routes->get('news/(:segment)', 'News::view/$1');
	$routes->get('news', 'News::index');
	$routes->add('(:any)', 'Pages::view/$1');

Point your browser to your document root, followed by index.php/news and
watch your news page.
