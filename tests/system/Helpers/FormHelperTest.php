<?php

namespace CodeIgniter\Helpers;

use CodeIgniter\HTTP\URI;
use Config\App;
use CodeIgniter\Services;
use Config\Filters;

class FormHelperTest extends \CIUnitTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		helper('form');
	}

	// ------------------------------------------------------------------------
	public function testFormOpenBasic()
	{
		$config            = new App();
		$config->baseURL   = '';
		$config->indexPage = 'index.php';
		$request           = Services::request($config);
		$request->uri      = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$before = (new Filters())->globals['before'];
		if (in_array('csrf', $before) || array_key_exists('csrf', $before))
		{
			$Value    = csrf_hash();
			$Name     = csrf_token();
			$expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">
<input type="hidden" name="$Name" value="$Value" style="display:none;" />

EOH;
		}
		else
		{
			$expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">

EOH;
		}

		$attributes = [
			'name'   => 'form',
			'id'     => 'form',
			'method' => 'POST',
		];
		$this->assertEquals($expected, form_open('foo/bar', $attributes));
	}

	// ------------------------------------------------------------------------
	public function testFormOpenWithoutAction()
	{
		$config            = new App();
		$config->baseURL   = '';
		$config->indexPage = 'index.php';
		$request           = Services::request($config);
		$request->uri      = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$before = (new Filters())->globals['before'];
		if (in_array('csrf', $before) || array_key_exists('csrf', $before))
		{
			$Value    = csrf_hash();
			$Name     = csrf_token();
			$expected = <<<EOH
<form action="http://example.com/" name="form" id="form" method="POST" accept-charset="utf-8">
<input type="hidden" name="$Name" value="$Value" style="display:none;" />

EOH;
		}
		else
		{
			$expected = <<<EOH
<form action="http://example.com/" name="form" id="form" method="POST" accept-charset="utf-8">

EOH;
		}
		$attributes = [
			'name'   => 'form',
			'id'     => 'form',
			'method' => 'POST',
		];
		$this->assertEquals($expected, form_open('', $attributes));
	}

	// ------------------------------------------------------------------------
	public function testFormOpenWithoutMethod()
	{
		$config            = new App();
		$config->baseURL   = '';
		$config->indexPage = 'index.php';
		$request           = Services::request($config);
		$request->uri      = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$before = (new Filters())->globals['before'];
		if (in_array('csrf', $before) || array_key_exists('csrf', $before))
		{
			$Value    = csrf_hash();
			$Name     = csrf_token();
			$expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="post" accept-charset="utf-8">
<input type="hidden" name="$Name" value="$Value" style="display:none;" />

EOH;
		}
		else
		{
			$expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="post" accept-charset="utf-8">

EOH;
		}

		$attributes = [
			'name' => 'form',
			'id'   => 'form',
		];
		$this->assertEquals($expected, form_open('foo/bar', $attributes));
	}

	// ------------------------------------------------------------------------
	public function testFormOpenWithHidden()
	{
		$config            = new App();
		$config->baseURL   = '';
		$config->indexPage = 'index.php';
		$request           = Services::request($config);
		$request->uri      = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$before = (new Filters())->globals['before'];
		if (in_array('csrf', $before) || array_key_exists('csrf', $before))
		{
			$Value    = csrf_hash();
			$Name     = csrf_token();
			$expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">
<input type="hidden" name="foo" value="bar" style="display:none;" />
<input type="hidden" name="$Name" value="$Value" style="display:none;" />

EOH;
		}
		else
		{
			$expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">

<input type="hidden" name="foo" value="bar" style="display:none;" />

EOH;
		}

		$attributes = [
			'name'   => 'form',
			'id'     => 'form',
			'method' => 'POST',
		];
		$hidden     = [
			'foo' => 'bar',
		];
		$this->assertEquals($expected, form_open('foo/bar', $attributes, $hidden));
	}

	// ------------------------------------------------------------------------
	//FIXME This needs dynamic filters to complete
	//  public function testFormOpenWithCSRF()
	//  {
	//      $config = new App();
	//      $config->baseURL = '';
	//      $config->indexPage = 'index.php';
	//      $request = Services::request($config);
	//      $request->uri = new URI('http://example.com/');
	//
	//      Services::injectMock('request', $request);
	//
	//      $filters = Services::filters();
	//      $filters->globals['before'][] = 'csrf'; // force CSRF
	//      $before = $filters->globals['before'];
	//
	//      $Value = csrf_hash();
	//      $Name = csrf_token();
	//      $expected = <<<EOH
	//<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">
	//<input type="hidden" name="foo" value="bar" style="display: none;" />
	//<input type="hidden" name="$Name" value="$Value" style="display: none;" />
	//
	//EOH;
	//
	//      $attributes = [
	//          'name' => 'form',
	//          'id' => 'form',
	//          'method' => 'POST'
	//      ];
	//      $hidden = [
	//          'foo' => 'bar'
	//      ];
	//      $this->assertEquals($expected, form_open('foo/bar', $attributes, $hidden));
	//  }
	// ------------------------------------------------------------------------
	public function testFormOpenMultipart()
	{
		$config            = new App();
		$config->baseURL   = '';
		$config->indexPage = 'index.php';
		$request           = Services::request($config);
		$request->uri      = new URI('http://example.com/');

		Services::injectMock('request', $request);

		$before = (new Filters())->globals['before'];
		if (in_array('csrf', $before) || array_key_exists('csrf', $before))
		{
			$Value    = csrf_hash();
			$Name     = csrf_token();
			$expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" enctype="multipart&#x2F;form-data" accept-charset="utf-8">
<input type="hidden" name="$Name" value="$Value" style="display:none;" />

EOH;
		}
		else
		{
			$expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" enctype="multipart&#x2F;form-data" accept-charset="utf-8">

EOH;
		}
		$attributes = [
			'name'   => 'form',
			'id'     => 'form',
			'method' => 'POST',
		];
		$this->assertEquals($expected, form_open_multipart('foo/bar', $attributes));

		// make sure it works with attributes as a string too
		$attributesString = 'name="form" id="form" method="POST"';
		$this->assertEquals($expected, form_open_multipart('foo/bar', $attributesString));
	}

	// ------------------------------------------------------------------------
	public function testFormHidden()
	{
		$expected = <<<EOH

<input type="hidden" name="username" value="johndoe" style="display:none;" />\n
EOH;
		$this->assertEquals($expected, form_hidden('username', 'johndoe'));
	}

	// ------------------------------------------------------------------------
	public function testFormHiddenArrayInput()
	{
		$data     = [
			'foo' => 'bar',
		];
		$expected = <<<EOH

<input type="hidden" name="foo" value="bar" style="display:none;" />

EOH;
		$this->assertEquals($expected, form_hidden($data, null));
	}

	// ------------------------------------------------------------------------
	public function testFormHiddenArrayValues()
	{
		$data     = [
			'foo' => 'bar',
		];
		$expected = <<<EOH

<input type="hidden" name="name[foo]" value="bar" style="display:none;" />

EOH;
		$this->assertEquals($expected, form_hidden('name', $data));
	}

	// ------------------------------------------------------------------------
	public function testFormInput()
	{
		$expected = <<<EOH
<input type="text" name="username" value="johndoe" id="username" maxlength="100" size="50" style="width:50%"  />\n
EOH;
		$data     = [
			'name'      => 'username',
			'id'        => 'username',
			'value'     => 'johndoe',
			'maxlength' => '100',
			'size'      => '50',
			'style'     => 'width:50%',
		];
		$this->assertEquals($expected, form_input($data));
	}

	// ------------------------------------------------------------------------
	public function testFormPassword()
	{
		$expected = <<<EOH
<input type="password" name="password" value=""  />\n
EOH;
		$this->assertEquals($expected, form_password('password'));
	}

	// ------------------------------------------------------------------------
	public function testFormUpload()
	{
		$expected = <<<EOH
<input type="file" name="attachment"  />\n
EOH;
		$this->assertEquals($expected, form_upload('attachment'));
	}

	// ------------------------------------------------------------------------
	public function testFormTextarea()
	{
		$expected = <<<EOH
<textarea name="notes" cols="40" rows="10" >Notes</textarea>\n
EOH;
		$this->assertEquals($expected, form_textarea('notes', 'Notes'));
	}

	// ------------------------------------------------------------------------
	public function testFormTextareaWithValueAttribute()
	{
		$data     = [
			'name'  => 'foo',
			'value' => 'bar',
		];
		$expected = <<<EOH
<textarea name="foo" cols="40" rows="10" >bar</textarea>

EOH;
		$this->assertEquals($expected, form_textarea($data));
	}

	// ------------------------------------------------------------------------
	public function testFormDropdown()
	{
		$expected = <<<EOH
<select name="shirts">
<option value="small">Small Shirt</option>
<option value="med">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>\n
EOH;
		$options  = [
			'small'  => 'Small Shirt',
			'med'    => 'Medium Shirt',
			'large'  => 'Large Shirt',
			'xlarge' => 'Extra Large Shirt',
		];
		$this->assertEquals($expected, form_dropdown('shirts', $options, 'large'));
		$expected       = <<<EOH
<select name="shirts" multiple="multiple">
<option value="small" selected="selected">Small Shirt</option>
<option value="med">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>\n
EOH;
		$shirts_on_sale = [
			'small',
			'large',
		];
		$this->assertEquals($expected, form_dropdown('shirts', $options, $shirts_on_sale));
		$options  = [
			'Swedish Cars' => [
				'volvo' => 'Volvo',
				'saab'  => 'Saab',
			],
			'German Cars'  => [
				'mercedes' => 'Mercedes',
				'audi'     => 'Audi',
			],
		];
		$expected = <<<EOH
<select name="cars" multiple="multiple">
<optgroup label="Swedish Cars">
<option value="volvo" selected="selected">Volvo</option>
<option value="saab">Saab</option>
</optgroup>
<optgroup label="German Cars">
<option value="mercedes">Mercedes</option>
<option value="audi" selected="selected">Audi</option>
</optgroup>
</select>\n
EOH;
		$this->assertEquals($expected, form_dropdown('cars', $options, ['volvo', 'audi']));
	}

	public function testFormDropdownUnselected()
	{
		$options  = [
			'Swedish Cars' => [
				'volvo' => 'Volvo',
				'saab'  => 'Saab',
			],
			'German Cars'  => [
				'mercedes' => 'Mercedes',
				'audi'     => 'Audi',
			],
		];
		$expected = <<<EOH
<select name="cars">
<optgroup label="Swedish Cars">
<option value="volvo">Volvo</option>
<option value="saab">Saab</option>
</optgroup>
<optgroup label="German Cars">
<option value="mercedes">Mercedes</option>
<option value="audi">Audi</option>
</optgroup>
</select>\n
EOH;
		$this->assertEquals($expected, form_dropdown('cars', $options, []));
	}

	public function testFormDropdownInferred()
	{
		$options       = [
			'Swedish Cars' => [
				'volvo' => 'Volvo',
				'saab'  => 'Saab',
			],
			'German Cars'  => [
				'mercedes' => 'Mercedes',
				'audi'     => 'Audi',
			],
		];
		$expected      = <<<EOH
<select name="cars">
<optgroup label="Swedish Cars">
<option value="volvo">Volvo</option>
<option value="saab">Saab</option>
</optgroup>
<optgroup label="German Cars">
<option value="mercedes">Mercedes</option>
<option value="audi" selected="selected">Audi</option>
</optgroup>
</select>\n
EOH;
		$_POST['cars'] = 'audi';
		$this->assertEquals($expected, form_dropdown('cars', $options, []));
		unset($_POST['cars']);
	}

	// ------------------------------------------------------------------------
	public function testFormDropdownWithSelectedAttribute()
	{
		$expected = <<<EOH
<select name="foo">
<option value="bar" selected="selected">Bar</option>
</select>

EOH;
		$data     = [
			'name'     => 'foo',
			'selected' => 'bar',
		];
		$options  = [
			'bar' => 'Bar',
		];
		$this->assertEquals($expected, form_dropdown($data, $options));
	}

	// ------------------------------------------------------------------------
	public function testFormDropdownWithOptionsAttribute()
	{
		$expected = <<<EOH
<select name="foo">
<option value="bar">Bar</option>
</select>

EOH;
		$data     = [
			'name'    => 'foo',
			'options' => [
				'bar' => 'Bar',
			],
		];
		$this->assertEquals($expected, form_dropdown($data));
	}

	// ------------------------------------------------------------------------
	public function testFormDropdownWithEmptyArrayOptionValue()
	{
		$expected = <<<EOH
<select name="foo">
</select>

EOH;
		$options  = [
			'bar' => [],
		];
		$this->assertEquals($expected, form_dropdown('foo', $options));
	}

	// ------------------------------------------------------------------------
	public function testFormMultiselect()
	{
		$expected = <<<EOH
<select name="shirts[]"  multiple="multiple">
<option value="small">Small Shirt</option>
<option value="med" selected="selected">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>\n
EOH;
		$options  = [
			'small'  => 'Small Shirt',
			'med'    => 'Medium Shirt',
			'large'  => 'Large Shirt',
			'xlarge' => 'Extra Large Shirt',
		];
		$this->assertEquals($expected, form_multiselect('shirts[]', $options, ['med', 'large']));
	}

	// ------------------------------------------------------------------------
	public function testFormFieldset()
	{
		$expected = <<<EOH
<fieldset>
<legend>Address Information</legend>\n
EOH;
		$this->assertEquals($expected, form_fieldset('Address Information'));
	}

	// ------------------------------------------------------------------------
	public function testFormFieldsetWithNoLegent()
	{
		$expected = <<<EOH
<fieldset>

EOH;
		$this->assertEquals($expected, form_fieldset());
	}

	// ------------------------------------------------------------------------
	public function testFormFieldsetWithAttributes()
	{
		$attributes = [
			'name' => 'bar',
		];
		$expected   = <<<EOH
<fieldset name="bar">
<legend>Foo</legend>

EOH;
		$this->assertEquals($expected, form_fieldset('Foo', $attributes));
	}

	// ------------------------------------------------------------------------
	public function testFormFieldsetClose()
	{
		$expected = <<<EOH
</fieldset></div></div>
EOH;
		$this->assertEquals($expected, form_fieldset_close('</div></div>'));
	}

	// ------------------------------------------------------------------------
	public function testFormCheckbox()
	{
		$expected = <<<EOH
<input type="checkbox" name="newsletter" value="accept" checked="checked"  />\n
EOH;
		$this->assertEquals($expected, form_checkbox('newsletter', 'accept', true));
	}

	// ------------------------------------------------------------------------
	public function testFormCheckboxArrayData()
	{
		$data     = [
			'name'    => 'foo',
			'value'   => 'bar',
			'checked' => true,
		];
		$expected = <<<EOH
<input type="checkbox" name="foo" value="bar" checked="checked"  />

EOH;
		$this->assertEquals($expected, form_checkbox($data));
	}

	// ------------------------------------------------------------------------
	public function testFormCheckboxArrayDataWithCheckedFalse()
	{
		$data     = [
			'name'    => 'foo',
			'value'   => 'bar',
			'checked' => false,
		];
		$expected = <<<EOH
<input type="checkbox" name="foo" value="bar"  />

EOH;
		$this->assertEquals($expected, form_checkbox($data));
	}

	// ------------------------------------------------------------------------
	public function testFormRadio()
	{
		$expected = <<<EOH
<input type="radio" name="newsletter" value="accept" checked="checked"  />\n
EOH;
		$this->assertEquals($expected, form_radio('newsletter', 'accept', true));
	}

	// ------------------------------------------------------------------------
	public function testFormSubmit()
	{
		$expected = <<<EOH
<input type="submit" name="mysubmit" value="Submit Post!"  />\n
EOH;
		$this->assertEquals($expected, form_submit('mysubmit', 'Submit Post!'));
	}

	// ------------------------------------------------------------------------
	public function testFormLabel()
	{
		$expected = <<<EOH
<label for="username">What is your Name</label>
EOH;
		$this->assertEquals($expected, form_label('What is your Name', 'username'));
	}

	// ------------------------------------------------------------------------
	public function testFormLabelWithAttributes()
	{
		$attributes = [
			'id' => 'label1',
		];
		$expected   = <<<EOH
<label for="foo" id="label1">bar</label>
EOH;
		$this->assertEquals($expected, form_label('bar', 'foo', $attributes));
	}

	// ------------------------------------------------------------------------
	public function testFormReset()
	{
		$expected = <<<EOH
<input type="reset" name="myreset" value="Reset"  />\n
EOH;
		$this->assertEquals($expected, form_reset('myreset', 'Reset'));
	}

	// ------------------------------------------------------------------------
	public function testFormButton()
	{
		$expected = <<<EOH
<button name="name" type="button" >content</button>\n
EOH;
		$this->assertEquals($expected, form_button('name', 'content'));
	}

	// ------------------------------------------------------------------------
	public function testFormButtonWithDataArray()
	{
		$data     = [
			'name'    => 'foo',
			'content' => 'bar',
		];
		$expected = <<<EOH
<button name="foo" type="button" >bar</button>

EOH;
		$this->assertEquals($expected, form_button($data));
	}

	// ------------------------------------------------------------------------
	public function testFormClose()
	{
		$expected = <<<EOH
</form></div></div>
EOH;
		$this->assertEquals($expected, form_close('</div></div>'));
	}

	// ------------------------------------------------------------------------
	public function testFormDatalist()
	{
		$options  = [
			'foo1',
			'bar1',
		];
		$expected = <<<EOH
<input type="text" name="foo" value="bar" list="foo_list"  />

<datalist id='foo_list'><option value='foo1'>
<option value='bar1'>
</datalist>

EOH;
		$this->assertEquals($expected, form_datalist('foo', 'bar', $options));
	}

	// ------------------------------------------------------------------------
	public function testSetValue()
	{
		$_SESSION['_ci_old_input']['post']['foo'] = '<bar';
		$this->assertEquals('&lt;bar', set_value('foo'));

		unset($_SESSION['_ci_old_input']['post']['foo']);
		$this->assertEquals('baz', set_value('foo', 'baz'));
	}

	// ------------------------------------------------------------------------
	public function testSetSelect()
	{
		$_SESSION['_ci_old_input']['post']['foo'] = 'bar';
		$this->assertEquals(' selected="selected"', set_select('foo', 'bar'));

		$_SESSION['_ci_old_input']['post']['foo'] = ['foo' => 'bar'];
		$this->assertEquals(' selected="selected"', set_select('foo', 'bar'));
		$this->assertEquals('', set_select('foo', 'baz'));

		unset($_SESSION['_ci_old_input']['post']['foo']);
		$this->assertEquals(' selected="selected"', set_select('foo', 'baz', true));
	}

	// ------------------------------------------------------------------------
	public function testSetCheckbox()
	{
		$_SESSION = [
			'_ci_old_input' => [
				'post' => [
					'foo' => 'bar',
				],
			],
		];

		$this->assertEquals(' checked="checked"', set_checkbox('foo', 'bar'));

		$_SESSION = [
			'_ci_old_input' => [
				'post' => [
					'foo' => ['foo' => 'bar'],
				],
			],
		];
		$this->assertEquals(' checked="checked"', set_checkbox('foo', 'bar'));
		$this->assertEquals('', set_checkbox('foo', 'baz'));

		$_SESSION = [];
		$this->assertEquals('', set_checkbox('foo', 'bar'));
	}

	// ------------------------------------------------------------------------
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testSetRadio()
	{
		$_SESSION = [
			'_ci_old_input' => [
				'post' => [
					'foo' => 'bar',
				],
			],
		];

		$this->assertEquals(' checked="checked"', set_radio('foo', 'bar'));
		$this->assertEquals('', set_radio('foo', 'baz'));
		unset($_SESSION['_ci_old_input']);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState  disabled
	 */
	public function testSetRadioFromPost()
	{
		$_POST['bar'] = 'baz';
		$this->assertEquals(' checked="checked"', set_radio('bar', 'baz'));
		$this->assertEquals('', set_radio('bar', 'boop'));
	}

	public function testSetRadioFromPostArray()
	{
		$_SESSION = [
			'_ci_old_input' => [
				'post' => [
					'bar' => [
						'boop',
						'fuzzy',
					],
				],
			],
		];
		$this->assertEquals(' checked="checked"', set_radio('bar', 'boop'));
		$this->assertEquals('', set_radio('bar', 'baz'));
	}

	public function testSetRadioDefault()
	{
		$this->assertEquals(' checked="checked"', set_radio('code', 'alpha', true));
		$this->assertEquals('', set_radio('code', 'beta', false));
	}

	// ------------------------------------------------------------------------
	public function testFormParseFormAttributesTrue()
	{
		$expected = 'readonly ';
		$this->assertEquals($expected, parse_form_attributes(['readonly' => true], []));
	}

	// ------------------------------------------------------------------------
	public function testFormParseFormAttributesFalse()
	{
		$expected = 'disabled ';
		$this->assertEquals($expected, parse_form_attributes(['disabled' => false], []));
	}

	// ------------------------------------------------------------------------
	public function testFormParseFormAttributesNull()
	{
		$expected = 'bar="" ';
		$this->assertEquals($expected, parse_form_attributes(['bar' => null], []));
	}

	// ------------------------------------------------------------------------
	public function testFormParseFormAttributesStringEmpty()
	{
		$expected = 'bar="" ';
		$this->assertEquals($expected, parse_form_attributes(['bar' => ''], []));
	}

	// ------------------------------------------------------------------------
	public function testFormParseFormAttributesStringFoo()
	{
		$expected = 'bar="foo" ';
		$this->assertEquals($expected, parse_form_attributes(['bar' => 'foo'], []));
	}

	// ------------------------------------------------------------------------
	public function testFormParseFormAttributesInt0()
	{
		$expected = 'ok="0" ';
		$this->assertEquals($expected, parse_form_attributes(['ok' => 0], []));
	}

	// ------------------------------------------------------------------------
	public function testFormParseFormAttributesInt1()
	{
		$expected = 'ok="1" ';
		$this->assertEquals($expected, parse_form_attributes(['ok' => 1], []));
	}
}
