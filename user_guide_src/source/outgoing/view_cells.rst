##########
View Cells
##########

View Cells allow you to insert HTML that is generated outside of your controller. It simply calls the specified
class and method, which must return a string of valid HTML. This method could be in any callable method, found in any class
that the autoloader can locate. The only restriction is that the class can not have any constructor parameters.
This is intended to be used within views, and is a great aid to modularizing your code.
::

    <?= view_cell('\App\Libraries\Blog::recentPosts') ?>

In this example, the class ``App\Libraries\Blog`` is loaded, and the method ``recentPosts()`` is run. The method
must return the generated HTML as a string. The method can be either a static method or not. Either way works.

Cell Parameters
---------------

You can further refine the call by passing a list of parameters in the second parameter to the method. The values passed 
can be an array of key/value pairs, or a comma-separated string of key/value pairs::

    // Passing Parameter Array
    <?= view_cell('\App\Libraries\Blog::recentPosts', ['category' => 'codeigniter', 'limit' => 5]) ?>

    // Passing Parameter String
    <?= view_cell('\App\Libraries\Blog::recentPosts', 'category=codeigniter, limit=5') ?>

    public function recentPosts(array $params=[])
    {
        $posts = $this->blogModel->where('category', $params['category'])
                                 ->orderBy('published_on', 'desc')
                                 ->limit($params['limit'])
                                 ->get();

        return view('recentPosts', ['posts' => $posts]);
    }

Additionally, you can use parameter names that match the parameter variables in the method for better readability.
When you use it this way, all of the parameters must always be specified in the view cell call::

    <?= view_cell('\App\Libraries\Blog::recentPosts', 'category=codeigniter, limit=5') ?>

    public function recentPosts(int $limit, string $category)
    {
        $posts = $this->blogModel->where('category', $category)
                                 ->orderBy('published_on', 'desc')
                                 ->limit($limit)
                                 ->get();

        return view('recentPosts', ['posts' => $posts]);
    }

Cell Caching
------------

You can cache the results of the view cell call by passing the number of seconds to cache the data for as the
third parameter. This will use the currently configured cache engine.
::

    // Cache the view for 5 minutes
    <?= view_cell('\App\Libraries\Blog::recentPosts', 'limit=5', 300) ?>

You can provide a custom name to use instead of the auto-generated one if you like, by passing the new name
as the fourth parameter::

    // Cache the view for 5 minutes
    <?= view_cell('\App\Libraries\Blog::recentPosts', 'limit=5', 300, 'newcacheid') ?>
