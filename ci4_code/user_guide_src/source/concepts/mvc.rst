##############################
Models, Views, and Controllers
##############################

Whenever you create an application, you have to find a way to organize the code to make it simple to locate
the proper files and make it simple to maintain. Like most of the web frameworks, CodeIgniter uses the Model,
View, Controller (MVC) pattern to organize the files. This keeps the data, the presentation, and flow through the
application as separate parts. It should be noted that there are many views on the exact roles of each element,
but this document describes our take on it. If you think of it differently, you're free to modify how you use
each piece as you need.

**Models** manage the data of the application and help to enforce any special business rules the application might need.

**Views** are simple files, with little to no logic, that display the information to the user.

**Controllers** act as glue code, marshaling data back and forth between the view (or the user that's seeing it) and
the data storage.

At their most basic, controllers and models are simply classes that have a specific job. They are not the only class
types that you can use, obviously, but they make up the core of how this framework is designed to be used. They even
have designated directories in the **/app** directory for their storage, though you're free to store them
wherever you desire, as long as they are properly namespaced. We will discuss that in more detail below.

Let's take a closer look at each of these three main components.

**************
The Components
**************

Views
=====

Views are the simplest files and are typically HTML with very small amounts of PHP. The PHP should be very simple,
usually just displaying a variable's contents, or looping over some items and displaying their information in a table.

Views get the data to display from the controllers, who pass it to the views as variables that can be displayed
with simple ``echo`` calls. You can also display other views within a view, making it pretty simple to display a
common header or footer on every page.

Views are generally stored in **/app/Views**, but can quickly become unwieldy if not organized in some fashion.
CodeIgniter does not enforce any type of organization, but a good rule of thumb would be to create a new directory in
the **Views** directory for each controller. Then, name views by the method name. This makes them very easy to find later
on. For example, a user's profile might be displayed in a controller named ``User``, and a method named ``profile``.
You might store the view file for this method in **/app/Views/User/Profile.php**.

That type of organization works great as a base habit to get into. At times you might need to organize it differently.
That's not a problem. As long as CodeIgniter can find the file, it can display it.

:doc:`Find out more about views </outgoing/views>`

Models
======

A model's job is to maintain a single type of data for the application. This might be users, blog posts, transactions, etc.
In this case, the model's job has two parts: enforce business rules on the data as it is pulled from, or put into, the
database; and handle the actual saving and retrieval of the data from the database.

For many developers, the confusion comes in when determining what business rules are enforced. It simply means that
any restrictions or requirements on the data is handled by the model. This might include normalizing raw data before
it's saved to meet company standards, or formatting a column in a certain way before handing it to the controller.
By keeping these business requirements in the model, you won't repeat code throughout several controllers and accidentally
miss updating an area.

Models are typically stored in **/app/Models**, though they can use a namespace to be grouped however you need.

:doc:`Find out more about models </models/model>`

Controllers
===========

Controllers have a couple of different roles to play. The most obvious one is that they receive input from the user and
then determine what to do with it. This often involves passing the data to a model to save it, or requesting data from
the model that is then passed on to the view to be displayed. This also includes loading up other utility classes,
if needed, to handle specialized tasks that is outside of the purview of the model.

The other responsibility of the controller is to handle everything that pertains to HTTP requests - redirects,
authentication, web safety, encoding, etc. In short, the controller is where you make sure that people are allowed to
be there, and they get the data they need in a format they can use.

Controllers are typically stored in **/app/Controllers**, though they can use a namespace to be grouped however
you need.

:doc:`Find out more about controllers </incoming/controllers>`
