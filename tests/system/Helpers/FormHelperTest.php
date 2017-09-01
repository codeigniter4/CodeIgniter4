<?php namespace CodeIgniter\Helpers;

use CodeIgniter\HTTP\URI;
use Config\App;
use CodeIgniter\Services;

class FormHelperTest extends \CIUnitTestCase
{
    public function setUp()
    {
        helper('form');
    }
    // ------------------------------------------------------------------------
    public function testFormOpenBasic()
    {
        $config = new App();
        $config->baseURL = '';
        $config->indexPage = 'index.php';
        $request = Services::request($config);
        $request->uri = new URI('http://example.com/');

        Services::injectMock('request', $request);

        $expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">

EOH;
        $attributes = [
            'name'   => 'form',
            'id'     => 'form',
            'method' => 'POST'
        ];
        $this->assertEquals($expected, form_open('foo/bar', $attributes));
    }
    // ------------------------------------------------------------------------
    public function testFormOpenWithoutAction()
    {
        $config = new App();
        $config->baseURL = '';
        $config->indexPage = 'index.php';
        $request = Services::request($config);
        $request->uri = new URI('http://example.com/');

        Services::injectMock('request', $request);

        $expected = <<<EOH
<form action="http://example.com/" name="form" id="form" method="POST" accept-charset="utf-8">

EOH;
        $attributes = [
            'name'   => 'form',
            'id'     => 'form',
            'method' => 'POST'
        ];
        $this->assertEquals($expected, form_open('', $attributes));
    }
    // ------------------------------------------------------------------------
    public function testFormOpenWithoutMethod()
    {
        $config = new App();
        $config->baseURL = '';
        $config->indexPage = 'index.php';
        $request = Services::request($config);
        $request->uri = new URI('http://example.com/');

        Services::injectMock('request', $request);

        $expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="post" accept-charset="utf-8">

EOH;
        $attributes = [
            'name'   => 'form',
            'id'     => 'form'
        ];
        $this->assertEquals($expected, form_open('foo/bar', $attributes));
    }
    // ------------------------------------------------------------------------
    public function testFormOpenWithHidden()
    {
        $config = new App();
        $config->baseURL = '';
        $config->indexPage = 'index.php';
        $request = Services::request($config);
        $request->uri = new URI('http://example.com/');

        Services::injectMock('request', $request);

        $expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">
<input type="hidden" name="foo" value="bar" style="display: none;" />

EOH;
        $attributes = [
            'name'   => 'form',
            'id'     => 'form',
            'method' => 'POST'
        ];
        $hidden = [
            'foo' => 'bar'
        ];
        $this->assertEquals($expected, form_open('foo/bar', $attributes, $hidden));
    }
    // ------------------------------------------------------------------------
    public function testFormOpenMultipart()
    {
        $config = new App();
        $config->baseURL = '';
        $config->indexPage = 'index.php';
        $request = Services::request($config);
        $request->uri = new URI('http://example.com/');

        Services::injectMock('request', $request);

        $expected = <<<EOH
<form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" enctype="multipart&#x2F;form-data" accept-charset="utf-8">

EOH;
        $attributes = [
            'name'   => 'form',
            'id'     => 'form',
            'method' => 'POST'
        ];
        $this->assertEquals($expected, form_open_multipart('foo/bar', $attributes));
    }
    // ------------------------------------------------------------------------
    public function testFormHidden()
    {
        $expected = <<<EOH

<input type="hidden" name="username" value="johndoe" />\n
EOH;
        $this->assertEquals($expected, form_hidden('username', 'johndoe'));
    }
    // ------------------------------------------------------------------------
    public function testFormHiddenArrayInput()
    {
        $data = [
            'foo' => 'bar'
        ];
        $expected = <<<EOH

<input type="hidden" name="foo" value="bar" />

EOH;
        $this->assertEquals($expected, form_hidden($data, null));
    }
    // ------------------------------------------------------------------------
    public function testFormHiddenArrayValues()
    {
        $data = [
            'foo' => 'bar'
        ];
        $expected = <<<EOH

<input type="hidden" name="name[foo]" value="bar" />

EOH;
        $this->assertEquals($expected, form_hidden('name', $data));
    }
    // ------------------------------------------------------------------------
    public function testFormInput()
    {
        $expected = <<<EOH
<input type="text" name="username" value="johndoe" id="username" maxlength="100" size="50" style="width:50%"  />\n
EOH;
        $data = [
            'name'        => 'username',
            'id'          => 'username',
            'value'       => 'johndoe',
            'maxlength'   => '100',
            'size'        => '50',
            'style'       => 'width:50%',
        ];
        $this->assertEquals($expected, form_input($data));
    }
    // ------------------------------------------------------------------------
    public function test_form_password()
    {
        $expected = <<<EOH
<input type="password" name="password" value=""  />\n
EOH;
        $this->assertEquals($expected, form_password('password'));
    }
    // ------------------------------------------------------------------------
    public function test_form_upload()
    {
        $expected = <<<EOH
<input type="file" name="attachment"  />\n
EOH;
        $this->assertEquals($expected, form_upload('attachment'));
    }
    // ------------------------------------------------------------------------
    public function test_form_textarea()
    {
        $expected = <<<EOH
<textarea name="notes" cols="40" rows="10" >Notes</textarea>\n
EOH;
        $this->assertEquals($expected, form_textarea('notes', 'Notes'));
    }
    // ------------------------------------------------------------------------
    public function testFormTextareaWithValueAttribute()
    {
        $data = [
            'name' => 'foo',
            'value' => 'bar'
        ];
        $expected = <<<EOH
<textarea name="foo" cols="40" rows="10" >bar</textarea>

EOH;
        $this->assertEquals($expected, form_textarea($data));
    }
    // ------------------------------------------------------------------------
    public function test_form_dropdown()
    {
        $expected = <<<EOH
<select name="shirts">
<option value="small">Small Shirt</option>
<option value="med">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>\n
EOH;
        $options = [
            'small'		=> 'Small Shirt',
            'med'		=> 'Medium Shirt',
            'large'		=> 'Large Shirt',
            'xlarge'	=> 'Extra Large Shirt',
        ];
        $this->assertEquals($expected, form_dropdown('shirts', $options, 'large'));
        $expected = <<<EOH
<select name="shirts" multiple="multiple">
<option value="small" selected="selected">Small Shirt</option>
<option value="med">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>\n
EOH;
        $shirts_on_sale = ['small', 'large'];
        $this->assertEquals($expected, form_dropdown('shirts', $options, $shirts_on_sale));
        $options = [
            'Swedish Cars' => [
                'volvo'	=> 'Volvo',
                'saab'	=> 'Saab'
            ],
            'German Cars' => [
                'mercedes'	=> 'Mercedes',
                'audi'		=> 'Audi'
            ]
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
    // ------------------------------------------------------------------------
    public function testFormDropdownWithSelectedAttribute()
    {
        $expected = <<<EOH
<select name="foo">
<option value="bar" selected="selected">Bar</option>
</select>

EOH;
        $data = [
            'name'     => 'foo',
            'selected' => 'bar'
        ];
        $options = [
            'bar' => 'Bar'
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
        $data = [
            'name'     => 'foo',
            'options' => [
                'bar' => 'Bar'
            ]
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
        $options = [
            'bar' => []
        ];
        $this->assertEquals($expected, form_dropdown('foo', $options));
    }
    // ------------------------------------------------------------------------
    public function test_form_multiselect()
    {
        $expected = <<<EOH
<select name="shirts[]"  multiple="multiple">
<option value="small">Small Shirt</option>
<option value="med" selected="selected">Medium Shirt</option>
<option value="large" selected="selected">Large Shirt</option>
<option value="xlarge">Extra Large Shirt</option>
</select>\n
EOH;
        $options = [
            'small'		=> 'Small Shirt',
            'med'		=> 'Medium Shirt',
            'large'		=> 'Large Shirt',
            'xlarge'	=> 'Extra Large Shirt',
        ];
        $this->assertEquals($expected, form_multiselect('shirts[]', $options, ['med', 'large']));
    }
    // ------------------------------------------------------------------------
    public function test_form_fieldset()
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
            'name' => 'bar'
        ];
        $expected = <<<EOH
<fieldset name="bar">
<legend>Foo</legend>

EOH;
        $this->assertEquals($expected, form_fieldset('Foo', $attributes));
    }
    // ------------------------------------------------------------------------
    public function test_form_fieldset_close()
    {
        $expected = <<<EOH
</fieldset></div></div>
EOH;
        $this->assertEquals($expected, form_fieldset_close('</div></div>'));
    }
    // ------------------------------------------------------------------------
    public function test_form_checkbox()
    {
        $expected = <<<EOH
<input type="checkbox" name="newsletter" value="accept" checked="checked"  />\n
EOH;
        $this->assertEquals($expected, form_checkbox('newsletter', 'accept', TRUE));
    }
    // ------------------------------------------------------------------------
    public function testFormCheckboxArrayData()
    {
        $data = [
            'name'  => 'foo',
            'value' => 'bar',
            'checked' => true
        ];
        $expected = <<<EOH
<input type="checkbox" name="foo" value="bar" checked="checked"  />

EOH;
        $this->assertEquals($expected, form_checkbox($data));
    }
    // ------------------------------------------------------------------------
    public function testFormCheckboxArrayDataWithCheckedFalse()
    {
        $data = [
            'name'  => 'foo',
            'value' => 'bar',
            'checked' => false
        ];
        $expected = <<<EOH
<input type="checkbox" name="foo" value="bar"  />

EOH;
        $this->assertEquals($expected, form_checkbox($data));
    }
    // ------------------------------------------------------------------------
    public function test_form_radio()
    {
        $expected = <<<EOH
<input type="radio" name="newsletter" value="accept" checked="checked"  />\n
EOH;
        $this->assertEquals($expected, form_radio('newsletter', 'accept', TRUE));
    }
    // ------------------------------------------------------------------------
    public function test_form_submit()
    {
        $expected = <<<EOH
<input type="submit" name="mysubmit" value="Submit Post!"  />\n
EOH;
        $this->assertEquals($expected, form_submit('mysubmit', 'Submit Post!'));
    }
    // ------------------------------------------------------------------------
    public function test_form_label()
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
            'id' => 'label1'
        ];
        $expected = <<<EOH
<label for="foo" id="label1">bar</label>
EOH;
        $this->assertEquals($expected, form_label('bar', 'foo', $attributes));
    }
    // ------------------------------------------------------------------------
    public function test_form_reset()
    {
        $expected = <<<EOH
<input type="reset" name="myreset" value="Reset"  />\n
EOH;
        $this->assertEquals($expected, form_reset('myreset', 'Reset'));
    }
    // ------------------------------------------------------------------------
    public function test_form_button()
    {
        $expected = <<<EOH
<button name="name" type="button" >content</button>\n
EOH;
        $this->assertEquals($expected, form_button('name', 'content'));
    }
    // ------------------------------------------------------------------------
    public function testFormButtonWithDataArray()
    {
        $data = [
            'name'    => 'foo',
            'content' => 'bar'
        ];
        $expected = <<<EOH
<button name="foo" type="button" >bar</button>

EOH;
        $this->assertEquals($expected, form_button($data));
    }
    // ------------------------------------------------------------------------
    public function test_form_close()
    {
        $expected = <<<EOH
</form></div></div>
EOH;
        $this->assertEquals($expected, form_close('</div></div>'));
    }
    // ------------------------------------------------------------------------
    public function testFormDatalist()
    {
        $options = [
            'foo1',
            'bar1'
        ];
        $expected = <<<EOH
<input type="text" name="foo" value="bar" list="foo_list"  />

<datalist id='foo_list'><option value='foo1'>
<option value='bar1'>
</datalist>

EOH;
        $this->assertEquals($expected, form_datalist('foo', 'bar', $options));
    }
}
