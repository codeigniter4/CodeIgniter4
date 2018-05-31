#################
Create news items
#################

You now know how you can read data from a database using CodeIgniter, but
you haven't written any information to the database yet. In this section
you'll expand your news controller and model created earlier to include
this functionality.

.. note:: This section of the tutorial cannot be completed as certain
    portions of the framework, like the form helper and the validation
    library have not been completed yet.

Create a form
-------------

To input data into the database you need to create a form where you can
input the information to be stored. This means you'll be needing a form
with two fields, one for the title and one for the text. You'll derive
the slug from our title in the model. Create the new view at
*application/Views/news/create.php*.

::

    <h2><?= esc($title); ?></h2>

    <?= validation_errors(); ?>

    <?= form_open('news/create'); ?>

        <label for="title">Title</label>
        <input type="input" name="title" /><br />

        <label for="text">Text</label>
        <textarea name="text"></textarea><br />

        <input type="submit" name="submit" value="Create news item" />

    </form>

There are only two things here that probably look unfamiliar to you: the
``form_open()`` function and the ``validation_errors()`` function.

The first function is provided by the :doc:`form
helper <../helpers/form_helper>` and renders the form element and
adds extra functionality, like adding a hidden :doc:`CSRF prevention
field <../libraries/security>`. The latter is used to report
errors related to form validation.

Go back to your news controller. You're going to do two things here,
check whether the form was submitted and whether the submitted data
passed the validation rules. You'll use the :doc:`form
validation <../libraries/validation>` library to do this.

::

    public function create()
    {
        helper('form');
        $model = new NewsModel();

        if (! $this->validate($this->request, [
            'title' => 'required|min[3]|max[255]',
            'text'  => 'required'
        ]))
        {
            echo view('templates/header', ['title' => 'Create a news item']);
            echo view('news/create');
            echo view('templates/footer');

        }
        else
        {
            $model->save([
                'title' => $this->request->getVar('title'),
                'slug'  => url_title($this->request->getVar('title')),
                'text'  => $this->request->getVar('text'),
            ]);
            echo view('news/success');
        }
    }

The code above adds a lot of functionality. The first few lines load the
form helper and the NewsModel. After that, the Controller-provided helper
function is used to validate the $_POST fields. In this case the title and
text fields are required.

CodeIgniter has a powerful validation library as demonstrated
above. You can read :doc:`more about this library
here <../libraries/validation>`.

Continuing down, you can see a condition that checks whether the form
validation ran successfully. If it did not, the form is displayed, if it
was submitted **and** passed all the rules, the model is called. This
takes care of passing the news item into the model.
This contains a new function, url\_title(). This function -
provided by the :doc:`URL helper <../helpers/url_helper>` - strips down
the string you pass it, replacing all spaces by dashes (-) and makes
sure everything is in lowercase characters. This leaves you with a nice
slug, perfect for creating URIs.

After this, a view is loaded to display a success message. Create a view at
**application/Views/news/success.php** and write a success message.

Model
-----

The only thing that remains is ensuring that your model is setup
to allow data to be saved properly. The ``save()`` method that was
used will determine whether the information should be inserted
or if the row already exists and should be updated, based on the presence
of a primary key. In this case, there is no ``id`` field passed to it,
so it will insert a new row into it's table, **news**.

However, by default the insert and update methods in the model will
not actually save any data because it doesn't know what fields are
safe to be updated. Edit the model to provide it a list of updatable
fields in the ``$allowedFields`` property.

::

    <?php
    class NewsModel extends \CodeIgniter\Model
    {
        protected $table = 'news';

        protected $allowedFields = ['title', 'slug', 'text'];
    }

This new property now contains the fields that we allow to be saved to the
database. Notice that we leave out the ``id``? That's because you will almost
never need to do that, since it is an auto-incrementing field in the database.
This helps protect against Mass Assignment Vulnerabilities. If your model is
handling your timestamps, you would also leave those out.

Routing
-------

Before you can start adding news items into your CodeIgniter application
you have to add an extra rule to *Config/Routes.php* file. Make sure your
file contains the following. This makes sure CodeIgniter sees 'create'
as a method instead of a news item's slug.

::

    $routes->post('news/create', 'News::create');
    $routes->add('news/(:segment)', 'News::view/$1');
    $routes->get('news', 'News::index');
    $routes->add('(:any)', 'Pages::view/$1');

Now point your browser to your local development environment where you
installed CodeIgniter and add index.php/news/create to the URL.
Congratulations, you just created your first CodeIgniter application!
Add some news and check out the different pages you made.
