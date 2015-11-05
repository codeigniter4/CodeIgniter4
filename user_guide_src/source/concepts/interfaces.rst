##########
Interfaces
##########

CodeIgniter provides interfaces to all of the core components that make up the framework. For example,
``CodeIgniter\Router\RouteCollectionInterface`` defines the methods needed to manage a collection of routes,
while ``CodeIgniter\Router\RouterInterface`` defines the methods needed for dispatching routes.

Why Provide Interfaces?
=======================

CodeIgniter is designed to be a framework that can grow with you. It can provide all of the common code needed
to serve up PHP pages in a consistent and simple workflow. It's impossible that we could anticipate the needs
of every single project that someone wants to create. Many might have special problems that are unique to that
application.

By providing interfaces to all core components, it allows you to provide your own custom classes and to
use them in place of the existing class by editing a single line in the services configuration file. If you
needed some additional routing features for your system, you could create a new class to handle the Route Collection
that implements the correct interface. Then change the class name used for the service configuration. Then,
everywhere that the DI is used to get a reference to that class, your new class will be used in its place.

Using The Interfaces
====================

