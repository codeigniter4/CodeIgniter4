<?php

namespace App\Controllers;

class Home extends BaseController
{

	public function index()
	{
		//      return view('welcome_message');

		echo date('m/d/Y h:i:s'), '<br><br>';

		//  var_dump($this->logger);
		//  var_dump($this->logger->getHandlers());
		//  var_dump($this->logger->getHandlers('FileHandler'));
		//  var_dump($this->logger->getHandlers('EmailHandler'));

		//  $this->logger->emergency('There be Dragons!');
		//  $this->logger->alert('You don\'t seem very alert');
		//  $this->logger->critical('I mean to be critical');
		//  $this->logger->error('There was a error.');
		//  $this->logger->warning('Warning the sign has sharp edges');
		//  $this->logger->notice('Did you notice my new shoes?');
		//  $this->logger->info('For your information...');
		//  $this->logger->debug('This is a debug message.');

		//  log_message('error', 'Something blowed up real good.');
		//  $id = 'Dave';
		//  $this->logger->debug("The value of \$id is {id}", [
		//      'id' => $id
		//  ]);

		//  $user = new \stdClass();
		//  $user->id = '42';
		//  $info = [
		//      'id'         => $user->id,
		//      'ip_address'     => $this->request->getIPAddress()
		//  ];
		//  log_message('info', 'User {id} logged into the system from {ip_address}', $info);
		//  try
		//  {
		//      throw new \Exception('Division by zero.');
		//  }
		//  catch (\Exception $e)
		//  {
		//      log_message('error', '[ERROR] {exception}', [
		//          'exception' => $e]);
		//  }
		//  $class = $this->response;
		//  $class = $this->logger;
		//  $class = $this->request;
		//  $class = new \Config\App();
		//  $class = $this;
		//  $class = "Hello World!";
		//  $class = [
		//      "Hello World!",
		//      "How are you?"
		//  ];
		//  $class = 42;
		//  $this->logger->debug($class);
		//  $this->logger->debug('The class in question is \'{0}\'.', [
		//      \get_class($class)]);
		//  $this->logger->debug('We logged {0} <script>alert(\'oops\');</script>', [
		//      $class]);
		//  $this->logger->debug('We logged {0} {1}', [
		//      '<script>alert(\'oops\');</script>',
		//      $class]);
		//  $this->logger->debug('We logged {0} {1}', [
		//      '<?php echo \'got ya; ?',
		//      $class]);
		//  $this->logger->debug('We logged <?php //echo "got ya"; ?');
	}

}
