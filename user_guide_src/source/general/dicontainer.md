# The Dependency Injection Container

Modern PHP applications are full of objects. One object might handle communicating with your database, while another might allow you to send an email, while still another represents a single User. Each of these classes might have other classes they rely on to do their job. In other words, they have *dependencies* on other classes. The Dependency Injection Container (DI for short) is a special class that helps you manage and organize these classes, and is used by the framework to make it simple to get access to the framework's classes, and yet swap out any core class for a custom one that you've developed by editing a single line in a configuration file.

The DI object actually handles two main purposes. It is a combination of a Dependency Injection container and Service Locater. These terms both have lots of confusion surrounding them. Taking a little bit of time to understand how this container works will allow you a great measure of freedom and power in your applications.

## What Is Dependency Injection
The concept behind this is simple: it's providing a class' dependency to it through the constructor or through a setter method. 

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

## Why Use A DI Container?

If dependency injection is that simple, why do we need another class (the DI Container) to manage things for you? **In many cases, you don't.** Just because the framework provides one, doesn't mean that you always have to use it. And you'll find that your code is a bit easier to read and understand, and the tiniest bit better performing, when you do not use the DI Container. 

It's easy for the network of dependencies to quickly become repetitive and error prone to have to manage manually, though. The SMTPMailService might have dependencies of it's own that must be passed into the constructor. This might be a Config class so that it can read your SMTP credentials from the configuration file. Additionally, the Mailer class itself might want a Themer class as a second dependency that allows it you use a single theme across all of its HTML emails. Doing this manually might look something like this:

	$config = new CodeIgniter\Config\Config();
	$themer = new App\Themers\Themer($config);
	$smtp = new App\Mailers\SMTPMailService($config);
	$mailer = new App\Mailers\Mailer($smtp, $themer);

You can see how this might quickly become tedious if you had to do this every place you wanted to send an email. Then, if you decided to use PostMarkApp.com to send email instead of SMTP you would need to find every place in your application that you've sent an email and manually make the change. It might be very simple to miss an instance this way. 

This is where the container helps out. It will look at each class' dependencies and then automatically inject an instance of the proper class where it needs it. This could turn the above code to get a mailer working into the much simpler: 

	$mailer = DI()->make('mailer');
	
