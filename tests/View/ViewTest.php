<?php

use CodeIgniter\View\View;

class ViewTest extends CIUnitTestCase
{
	protected $loader;
	protected $viewsDir;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->loader = new \CodeIgniter\Loader(new \App\Config\Autoload());
		$this->viewsDir = dirname(__FILE__).'/views';
	}

	//--------------------------------------------------------------------

	public function testSetVarStoresData()
	{
	    $view = new View($this->viewsDir, $this->loader);

		$view->setVar('foo', 'bar');

		$this->assertEquals(['foo' => 'bar'], $view->getData());
	}

	//--------------------------------------------------------------------

	public function testSetVarOverwrites()
	{
		$view = new View($this->viewsDir, $this->loader);

		$view->setVar('foo', 'bar');
		$view->setVar('foo', 'baz');

		$this->assertEquals(['foo' => 'baz'], $view->getData());
	}

	//--------------------------------------------------------------------

	public function testSetDataStoresValue()
	{
		$view = new View($this->viewsDir, $this->loader);

		$expected = [
			'foo' => 'bar',
		    'bar' => 'baz'
		];

		$view->setData($expected);

		$this->assertEquals($expected, $view->getData());
	}

	//--------------------------------------------------------------------

	public function testSetDataMergesData()
	{
		$view = new View($this->viewsDir, $this->loader);

		$expected = [
			'fee' => 'fi',
			'foo' => 'bar',
			'bar' => 'baz'
		];

		$view->setVar('fee', 'fi');
		$view->setData([
			'foo' => 'bar',
			'bar' => 'baz'
		]);

		$this->assertEquals($expected, $view->getData());
	}

	//--------------------------------------------------------------------

	public function testSetDataOverwritesData()
	{
		$view = new View($this->viewsDir, $this->loader);

		$expected = [
			'foo' => 'bar',
			'bar' => 'baz'
		];

		$view->setVar('foo', 'fi');
		$view->setData([
			'foo' => 'bar',
			'bar' => 'baz'
		]);

		$this->assertEquals($expected, $view->getData());
	}

	//--------------------------------------------------------------------

	public function testSetVarWillEscape()
	{
		$view = new View($this->viewsDir, $this->loader);

		$view->setVar('foo', 'bar&', 'html');

		$this->assertEquals(['foo' => 'bar&amp;'], $view->getData());
	}

	//--------------------------------------------------------------------

	public function testSetDataWillEscapeAll()
	{
		$view = new View($this->viewsDir, $this->loader);

		$expected = [
			'foo' => 'bar&amp;',
			'bar' => 'baz&lt;'
		];

		$view->setData([
			'foo' => 'bar&',
			'bar' => 'baz<'
		], 'html');

		$this->assertEquals($expected, $view->getData());
	}

	//--------------------------------------------------------------------

	public function testRenderFindsView()
	{
		$view = new View($this->viewsDir, $this->loader);

		$view->setVar('testString', 'Hello World');
		$expected = '<h1>Hello World</h1>';

		$this->assertEquals($expected, $view->render('simple'));
	}

	//--------------------------------------------------------------------

	public function testRendersThrowsExceptionIfFileNotFound()
	{
		$view = new View($this->viewsDir, $this->loader);

		$this->setExpectedException('InvalidArgumentException');
		$view->setVar('testString', 'Hello World');

		$view->render('missing');
	}

	//--------------------------------------------------------------------
}
