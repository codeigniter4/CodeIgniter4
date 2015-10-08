# The Dependency Injection Container

Modern PHP applications are full of objects. One object might handle communicating with your database, while another might allow you to send an email, while still another represents a single User. Each of these classes might have other classes they rely on to do their job. In other words, they have *dependencies* on other classes. The Dependency Injection Container (DI for short) is a special class that helps you manage and organize these classes, and is used by the framework to make it simple to get access to the framework's classes, and yet swap out any core class for a custom one that you've developed by editing a single line in a configuration file.

The DI object actually handles two main purposes. It is a combination of a Dependency Injection container and Service Locater. These terms both have lots of confusion surrounding them. Taking a little bit of time to understand how this container works will allow you a great measure of freedom and power in your applications.

## What Is Dependency Injection
The concept behind this is simple: it's providing a class' dependency to it through the constructor or through one of a few other patterns. 

	class Mailer {
	
		protected $mailService;
	
		public function __construct(MailServiceInterface $service)
		{
			$this->mailService = $service;
		}
		
		//...
	}

In this example, the Mailer class allows you to send out emails. It contains all of the information for a single email, like who it's being sent to, who is sending it, the subject line, the message, etc. However, it needs to know how to send the email. Should it use PHP's built-in `mail()` function? Or do we need to configure an SMTP server's credentials? Or even use a third-party service like PostMark? The class doesn't know that, so a class that performs that task for it is sent to it through it's constructor. In other words, it's dependency (the mailService class) was injected into the constructor when the object was made. 

In plain PHP, this can be handled quite easily for this example. 

	$mailer = new Mailer ( new SMTPMailService() );

The DI container in CodeIgniter, only supports constructor injection, and not any of the other types of injection like setter injection, interface injection, etc.

## Why Use A DI Container?

If dependency injection is that simple, why do we need another class (the DI Container) to manage things for you? **In many cases, you don't.** Just because the framework provides one, doesn't mean that you always have to use it. And you'll find that your code is a bit easier to read and understand, and the tiniest bit better performing, when you do not use the DI Container. 

It's easy for the network of dependencies to quickly become repetitive and error prone to have to manage manually, though. The SMTPMailService might have dependencies of it's own that must be passed into the constructor. This might be a Config class so that it can read your SMTP credentials from the configuration file. Additionally, the Mailer class itself might want a Themer class as a second dependency that allows it to use a single theme across all of its HTML emails. Doing this manually might look something like this:

	$config = new CodeIgniter\Config\Config();
	$themer = new App\Themers\Themer($config);
	$service = new App\Mailers\SMTPMailService($config);
	$mailer = new App\Mailers\Mailer($service, $themer);

You can see how this might quickly become tedious if you had to do this every place you wanted to send an email. Then, if you decided to use PostMarkApp.com to send email instead of SMTP you would need to find every place in your application that you've sent an email and manually make the change. It might be very simple to miss an instance this way. 

This is where the container helps out. It will look at each class' dependencies and then automatically inject an instance of the proper class where it needs it. This could turn the above code to get a mailer working into the much simpler: 

	$mailer = DI()->make('mailer');

In this example, the DI container sees that you want to create a new instance of the class with alias "mailer". The alias is just a nickname that the class goes by. Since we are using nicknames, we can easily tell it to use our custom mailer by changing the class that this alias points to. The container loads the mailer class into memory, sees that it needs both the `$service` and `$themer` instances passed in as parameters. It analyzes the constructor's dependencies, determines which classes it should create for the parameters, and loads those classes. For each of the new classes, it does the same thing: analyze the constructor for any dependencies, load the classes, create their dependencies, and pass the object into the parameter. This continues as deep into the class hierarchy as is needed to create the class that was initially requested.

All of that happens behind the scenes for you. Yes, it's a little bit of magic, and it adds a little bit of complexity to determining how things are called and instantiated. It also makes it a little bit more difficult to debug since the instantiation happens indirectly through the DI class. As mentioned previously, you don't always need or want to use the DI container. You can simply create a new instance of your class directly and go on your merry way. 

### How DI Chooses A Class To Inject

Now that we've seen how the DI container helps you create a class and it's dependencies, we need to look at *how* it determines which class or *service* it will create. This is where the Service Locater part of the container comes into play. A "service" is simply any class that the container needs to be able to locate. There are no special requirements for the class. 

All mapping of services happens in the `/application/config/services.php` configuration file. This file contains one big array that, at it's simplest, maps the alias (or nickname) of the service to the actual class that will be created. Declaring our four classes used above, we might have something like this: 

	$config['services'] = [
		'config' => 'CodeIgniter\Config\Config',
		'themer' => 'App\Themers\Themer',
		'mail_service' => 'App\Mailers\SMTPMailService',
		'mailer' => 'App\Mailers\Mailer'
	];

On the left is the alias, and on the right is the class name that will be instantiated. When we called the `make()` method on the container, we told it we wanted an instance of the class with the alias of 'mailer'. It looks in this array, sees that the 'mailer' alias points to the 'App\Mailers\Mailer` class and tries to create an instance of that class, injecting it's dependencies as needed. 

When you create your own classes that you expect to be used with the container, there is one convention that you need to be aware of in order for it to work. The name  of the parameter in your class MUST match the alias of the class you want an instance of. Let's take a look at how the constructor of the Mailer class might look to make this clear.
	
	namespace App\Mailers;
	
	use App\Mailers\MailServiceInterface;
	use App\Themers\ThemerInterface;
	
	class Mailer {
	
		protected $mail_service;
		protected $themer;
	
		public function __construct(MailServiceInterface $mail_service, ThemerInterface $themer)
		{
			$this->mail_service = $mail_service;
			$this->themer = $themer;
		}
	}

For each parameter we do two things. First, we specify what type of class this parameter must be. This lets the interpreter find any errors for us as fast as possible when the script is ran, and ensures we get what we need. In this case, we are saying that the classes must implement the specified interfaces. This ensures we get the API we need, but doesn't limit us to any single implementation of that class. 

The second thing to notice is that we name the parameter exactly the same as the service is aliased in the container. The name of the mail service, `$mail_service`, exactly matches the alias `mail_service` in the services configuration file. That is how the DI container can determine which class it should create.

## Overriding Core Classes

You should never modify any of the classes in the `system` directory in your application. This makes things very difficult to upgrade when new versions of CodeIgniter are released with security patches, bug fixes, and new features. The container allows you to instantly tell the entire application, including any of the core classes, to use your custom class instead of the one that CodeIgniter provides. If you used previous versions of CodeIgniter, this is a much more powerful version of the "MY_" class overrides, and one of the big reasons that the container was used within the framework.

Imagine that you created a new TemplateParser library that provides much more functionality than the basic one that CodeIgniter comes with. All you need to do is to update the services configuration file to point to your class. 

	// Change this...
	'parser' => 'CodeIgniter\Views\Parser'
	
	// To this
	'parser' => 'App\Libraries\Parser'

While this example is unlikely to affect the way the core classes work, this same method can be used to replace the Router or FormValidation libraries, or any other core classes.

## Class Reuse Through the DI

The container also helps to manage the number of instances of a particular class that your application creates. While you often want to create multiple instances of a class, like a number of Users would each have their own instance, there are times when you want to share an instance of a class throughout the application. This is especially true of core framework classes like the Router or the Database class. If you're not careful, having multiple copies of the Router could re-read the the routes file multiple times which is an unnecessary waste of memory and causes a performance hit. Creating multiple connections to the database, when all you really need is one, can also cause major performance issues when your site is under load. The container allows you to get either a new instance of that class or to get an existing instance, though the `make()` and `single()` methods. 

The `make()` method will always return a new instance of the class. The `single()` method will, as the name suggests, only ever create a single instance of the class. Once the first instance has been created, it is cached in the class. Any further requests for that class will return a reference to the class. 

	// Share the application's database connection.
	$db = DI()->single('database');
	// Can also be called like: 
	$db = DI('database');
	
	// Create two connections, usually to different databases.
	$db1 = DI()->make('database');
	$db2 = DI()->make('database');

By default, the container creates new instances of classes for parameters. If you want the parameters to be shared instances, you can pass `true` as the second parameter to the `make()` method. The `single()` method always uses shared parameters. 

	// Creates new instances of the MailerService and Themer classes 
	// that are passed to the Mailer's constructor.
	$mailer = DI()->make('mailer');
	
	// Will pass shared, or singleton, instances of the MailerService
	// and Themer classes to the Mailer's constructor, using the 
	// same instance that is used throughout the application.
	$mailer = DI()->make('mailer', true);

## Customizing the Class Instantiation Process

For many simple cases, the ability to pass in new instances of dependencies as we have been doing above is just fine. There are times, however, when you need to customize exactly how a class is created. You might need one parameter to be a shared instance, while another parameter might need to be a singleton. Or you might need to pass in a configuration array to the class. Both of these can be handled through the services configuration file. 

### Configuring Services With Closures
So far, we have seen the services configuration to simply match an alias against a class name. You can match the alias against an anonymous function, though, which allows you complete control over how your class is created. For this example, let's assume that the Mailer class needs a shared instance of the MailService, since it won't likely change between emails being sent out. However, we want a new instance of the Themer class since we will use a different theme for our emails then for the rest of the site. Yes, it's true the Themer class would probably support a simple flag for changing the theme, but ignore that inefficiency for the sake of this example.

To accomplish this, we would need to customize the class creation process like this: 

	$config['services'] = [
		'mailer' => function ($di) {
			return new \App\Mailers\Mailer($di->single('mail_service'), $di->make('themer'));
		}
	];

The container will always pass a reference to itself into the anonymous function so that you can use the container while creating new class instances. The function must return a class instance. Everything else is up to you. 

### Using Parameters
What happens when you need to pass a configuration array, or other simple variable, to a class? You can do this through the closure as well. For this example, we will assume that the Themer class accepts the name of the theme to use as the only parameter.

	$config['services'] = [
		'themer' => function ($di) {
			return new \App\Themers\SimpleThemer('theme_name');
		}
	];

While this works, it is not very flexible. In order to use multiple themes, you would need to define several different aliases in the services array. Not practical or desirable. Instead, you can use the DI container to hold parameters for you. These parameters can be anything. A string, array, number or even other class instances are all acceptable parameters. The only requirement is that the parameter name does not share the name of any service alias, or any method name in the DI class. An exception will be thrown if you attempt to do that.

You define parameters by simply assigning the value to DI instance. 

	DI()->theme_name = 'admin';
	
Then you can reference that parameter in your services configuration to pass the proper value in.

	$config['services'] = [
		'themer' => function ($di) {
			return new \App\Themers\SimpleThemer($di->theme_name);
		}
	];
	
	// This would be used like
	DI()->theme_name = 'admin';
	$themer = DI()->make('themer');

## When NOT to Use the Container
While the container is very powerful and very flexible, it should not be used without considering the pros and cons of it. 

**Never use the container when you don't need to.** The last example about the themer is the perfect example of this. In that case, you will likely always create a new themer when you need it, sine the theme name is passed in as a configuration value. In this case, you should use direct method of calling `$themer = new Themer('admin');` instead of going through the container. This keeps your code more directly readable which is a good thing for you and your team of developers (and any future developers who have to work on your project).

**Never use the container when you won't use multiple types of a class.** If you are only ever going to have one type of Themer in your application, using the DI container is unnecessary and a waste of performance and memory, not to mention making things more difficult to read and understand. If, however, different parts of your application might use completely different themers, then the container is a good solution. This might happen if you are adding new areas to an existing application, and the company has decided to migrate to a different theme engine. 

**Never pass the container into a class as a dependency.** The container is designed to help you manage your classes and dependencies. It should not *become* a dependency of any of your classes. Typically, you will only use the container within your controllers. Libraries, models, and other uses for classes should have their dependencies passed into them. They shouldn't call the DI from within the class themselves. That defeats the purpose of dependency injection altogether and simply moves the location of the dependency. This also would cause you to implement the Service Locater anti-pattern. While Service Locater's are a fine part of a framework, they are considered a problem when passed into classes or used as an internal dependency.
	
