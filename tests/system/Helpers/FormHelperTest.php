<?php

namespace CodeIgniter\Helpers;

use CodeIgniter\HTTP\URI;
use CodeIgniter\Services;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Filters;

/**
 * @internal
 */
final class FormHelperTest extends CIUnitTestCase
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
        if (in_array('csrf', $before, true) || array_key_exists('csrf', $before)) {
            $Value    = csrf_hash();
            $Name     = csrf_token();
            $expected = <<<EOH
                <form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">
                <input type="hidden" name="{$Name}" value="{$Value}" style="display:none;" />

                EOH;
        } else {
            $expected = <<<'EOH'
                <form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">

                EOH;
        }

        $attributes = [
            'name'   => 'form',
            'id'     => 'form',
            'method' => 'POST',
        ];
        $this->assertSame($expected, form_open('foo/bar', $attributes));
    }

    // ------------------------------------------------------------------------
    public function testFormOpenHasLocale()
    {
        $config            = new App();
        $config->baseURL   = '';
        $config->indexPage = 'index.php';
        $request           = Services::request($config);
        $request->uri      = new URI('http://example.com/');

        Services::injectMock('request', $request);
        $expected = <<<'EOH'
            <form action="http://example.com/index.php/en/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">

            EOH;

        $attributes = [
            'name'   => 'form',
            'id'     => 'form',
            'method' => 'POST',
        ];
        $this->assertSame($expected, form_open('{locale}/foo/bar', $attributes));
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
        if (in_array('csrf', $before, true) || array_key_exists('csrf', $before)) {
            $Value    = csrf_hash();
            $Name     = csrf_token();
            $expected = <<<EOH
                <form action="http://example.com/index.php" name="form" id="form" method="POST" accept-charset="utf-8">
                <input type="hidden" name="{$Name}" value="{$Value}" style="display:none;" />

                EOH;
        } else {
            $expected = <<<'EOH'
                <form action="http://example.com/index.php" name="form" id="form" method="POST" accept-charset="utf-8">

                EOH;
        }
        $attributes = [
            'name'   => 'form',
            'id'     => 'form',
            'method' => 'POST',
        ];
        $this->assertSame($expected, form_open('', $attributes));
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
        if (in_array('csrf', $before, true) || array_key_exists('csrf', $before)) {
            $Value    = csrf_hash();
            $Name     = csrf_token();
            $expected = <<<EOH
                <form action="http://example.com/index.php/foo/bar" name="form" id="form" method="post" accept-charset="utf-8">
                <input type="hidden" name="{$Name}" value="{$Value}" style="display:none;" />

                EOH;
        } else {
            $expected = <<<'EOH'
                <form action="http://example.com/index.php/foo/bar" name="form" id="form" method="post" accept-charset="utf-8">

                EOH;
        }

        $attributes = [
            'name' => 'form',
            'id'   => 'form',
        ];
        $this->assertSame($expected, form_open('foo/bar', $attributes));
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
        if (in_array('csrf', $before, true) || array_key_exists('csrf', $before)) {
            $Value    = csrf_hash();
            $Name     = csrf_token();
            $expected = <<<EOH
                <form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">
                <input type="hidden" name="foo" value="bar" />
                <input type="hidden" name="{$Name}" value="{$Value}" />

                EOH;
        } else {
            $expected = <<<'EOH'
                <form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" accept-charset="utf-8">

                <input type="hidden" name="foo" value="bar" />

                EOH;
        }

        $attributes = [
            'name'   => 'form',
            'id'     => 'form',
            'method' => 'POST',
        ];
        $hidden = [
            'foo' => 'bar',
        ];
        $this->assertSame($expected, form_open('foo/bar', $attributes, $hidden));
    }

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
        if (in_array('csrf', $before, true) || array_key_exists('csrf', $before)) {
            $Value    = csrf_hash();
            $Name     = csrf_token();
            $expected = <<<EOH
                <form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" enctype="multipart/form-data" accept-charset="utf-8">
                <input type="hidden" name="{$Name}" value="{$Value}" style="display:none;" />

                EOH;
        } else {
            $expected = <<<'EOH'
                <form action="http://example.com/index.php/foo/bar" name="form" id="form" method="POST" enctype="multipart/form-data" accept-charset="utf-8">

                EOH;
        }
        $attributes = [
            'name'   => 'form',
            'id'     => 'form',
            'method' => 'POST',
        ];
        $this->assertSame($expected, form_open_multipart('foo/bar', $attributes));

        // make sure it works with attributes as a string too
        $attributesString = 'name="form" id="form" method="POST"';
        $this->assertSame($expected, form_open_multipart('foo/bar', $attributesString));
    }

    // ------------------------------------------------------------------------
    public function testFormHidden()
    {
        $expected = <<<EOH

            <input type="hidden" name="username" value="johndoe" />\n
            EOH;
        $this->assertSame($expected, form_hidden('username', 'johndoe'));
    }

    // ------------------------------------------------------------------------
    public function testFormHiddenArrayInput()
    {
        $data = [
            'foo' => 'bar',
        ];
        $expected = <<<'EOH'

            <input type="hidden" name="foo" value="bar" />

            EOH;
        $this->assertSame($expected, form_hidden($data, null));
    }

    // ------------------------------------------------------------------------
    public function testFormHiddenArrayValues()
    {
        $data = [
            'foo' => 'bar',
        ];
        $expected = <<<'EOH'

            <input type="hidden" name="name[foo]" value="bar" />

            EOH;
        $this->assertSame($expected, form_hidden('name', $data));
    }

    // ------------------------------------------------------------------------
    public function testFormInput()
    {
        $expected = <<<EOH
            <input type="text" name="username" value="johndoe" id="username" maxlength="100" size="50" style="width:50%" />\n
            EOH;
        $data = [
            'name'      => 'username',
            'id'        => 'username',
            'value'     => 'johndoe',
            'maxlength' => '100',
            'size'      => '50',
            'style'     => 'width:50%',
        ];
        $this->assertSame($expected, form_input($data));
    }

    public function testFormInputWithExtra()
    {
        $expected = <<<EOH
            <input type="email" name="identity" value="" id="identity" class="form-control form-control-lg" />\n
            EOH;
        $data = [
            'id'   => 'identity',
            'name' => 'identity',
            'type' => 'email',
        ];
        $extra = [
            'class' => 'form-control form-control-lg',
        ];
        $this->assertSame($expected, form_input($data, '', $extra));
    }

    // ------------------------------------------------------------------------
    public function testFormPassword()
    {
        $expected = <<<EOH
            <input type="password" name="password" value="" />\n
            EOH;
        $this->assertSame($expected, form_password('password'));
    }

    // ------------------------------------------------------------------------
    public function testFormUpload()
    {
        $expected = <<<EOH
            <input type="file" name="attachment" />\n
            EOH;
        $this->assertSame($expected, form_upload('attachment'));
    }

    // ------------------------------------------------------------------------
    public function testFormTextarea()
    {
        $expected = <<<EOH
            <textarea name="notes" cols="40" rows="10">Notes</textarea>\n
            EOH;
        $this->assertSame($expected, form_textarea('notes', 'Notes'));
    }

    // ------------------------------------------------------------------------
    public function testFormTextareaWithValueAttribute()
    {
        $data = [
            'name'  => 'foo',
            'value' => 'bar',
        ];
        $expected = <<<'EOH'
            <textarea name="foo" cols="40" rows="10">bar</textarea>

            EOH;
        $this->assertSame($expected, form_textarea($data));
    }

    // ------------------------------------------------------------------------
    public function testFormTextareaExtraRowsColsArray()
    {
        $extra = [
            'cols' => '30',
            'rows' => '5',
        ];
        $expected = <<<EOH
            <textarea name="notes" cols="30" rows="5">Notes</textarea>\n
            EOH;
        $this->assertSame($expected, form_textarea('notes', 'Notes', $extra));
    }

    // ------------------------------------------------------------------------
    public function testFormTextareaExtraRowsColsString()
    {
        $extra    = 'cols="30" rows="5"';
        $expected = <<<EOH
            <textarea name="notes" cols="30" rows="5">Notes</textarea>\n
            EOH;
        $this->assertSame($expected, form_textarea('notes', 'Notes', $extra));
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
        $options = [
            'small'  => 'Small Shirt',
            'med'    => 'Medium Shirt',
            'large'  => 'Large Shirt',
            'xlarge' => 'Extra Large Shirt',
        ];
        $this->assertSame($expected, form_dropdown('shirts', $options, 'large'));
        $expected = <<<EOH
            <select name="shirts" multiple="multiple">
            <option value="small" selected="selected">Small Shirt</option>
            <option value="med">Medium Shirt</option>
            <option value="large" selected="selected">Large Shirt</option>
            <option value="xlarge">Extra Large Shirt</option>
            </select>\n
            EOH;
        $shirtsOnSale = [
            'small',
            'large',
        ];
        $this->assertSame($expected, form_dropdown('shirts', $options, $shirtsOnSale));
        $options = [
            'Swedish Cars' => [
                'volvo' => 'Volvo',
                'saab'  => 'Saab',
            ],
            'German Cars' => [
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
        $this->assertSame($expected, form_dropdown('cars', $options, ['volvo', 'audi']));
    }

    public function testFormDropdownUnselected()
    {
        $options = [
            'Swedish Cars' => [
                'volvo' => 'Volvo',
                'saab'  => 'Saab',
            ],
            'German Cars' => [
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
        $this->assertSame($expected, form_dropdown('cars', $options));
    }

    public function testFormDropdownInferred()
    {
        $options = [
            'Swedish Cars' => [
                'volvo' => 'Volvo',
                'saab'  => 'Saab',
            ],
            'German Cars' => [
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
            <option value="audi" selected="selected">Audi</option>
            </optgroup>
            </select>\n
            EOH;
        $_POST['cars'] = 'audi';
        $this->assertSame($expected, form_dropdown('cars', $options));
        unset($_POST['cars']);
    }

    // ------------------------------------------------------------------------
    public function testFormDropdownWithSelectedAttribute()
    {
        $expected = <<<'EOH'
            <select name="foo">
            <option value="bar" selected="selected">Bar</option>
            </select>

            EOH;
        $data = [
            'name'     => 'foo',
            'selected' => 'bar',
        ];
        $options = [
            'bar' => 'Bar',
        ];
        $this->assertSame($expected, form_dropdown($data, $options));
    }

    // ------------------------------------------------------------------------
    public function testFormDropdownWithOptionsAttribute()
    {
        $expected = <<<'EOH'
            <select name="foo">
            <option value="bar">Bar</option>
            </select>

            EOH;
        $data = [
            'name'    => 'foo',
            'options' => [
                'bar' => 'Bar',
            ],
        ];
        $this->assertSame($expected, form_dropdown($data));
    }

    // ------------------------------------------------------------------------
    public function testFormDropdownWithEmptyArrayOptionValue()
    {
        $expected = <<<'EOH'
            <select name="foo">
            </select>

            EOH;
        $options = [
            'bar' => [],
        ];
        $this->assertSame($expected, form_dropdown('foo', $options));
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
        $options = [
            'small'  => 'Small Shirt',
            'med'    => 'Medium Shirt',
            'large'  => 'Large Shirt',
            'xlarge' => 'Extra Large Shirt',
        ];
        $this->assertSame($expected, form_multiselect('shirts[]', $options, ['med', 'large']));
    }

    // ------------------------------------------------------------------------
    public function testFormMultiselectArrayData()
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
            'small'  => 'Small Shirt',
            'med'    => 'Medium Shirt',
            'large'  => 'Large Shirt',
            'xlarge' => 'Extra Large Shirt',
        ];

        $data = [
            'name'     => 'shirts[]',
            'options'  => $options,
            'selected' => [
                'med',
                'large',
            ],
        ];

        $this->assertSame($expected, form_multiselect($data));
    }

    // ------------------------------------------------------------------------
    public function testFormFieldset()
    {
        $expected = <<<EOH
            <fieldset>
            <legend>Address Information</legend>\n
            EOH;
        $this->assertSame($expected, form_fieldset('Address Information'));
    }

    // ------------------------------------------------------------------------
    public function testFormFieldsetWithNoLegent()
    {
        $expected = <<<'EOH'
            <fieldset>

            EOH;
        $this->assertSame($expected, form_fieldset());
    }

    // ------------------------------------------------------------------------
    public function testFormFieldsetWithAttributes()
    {
        $attributes = [
            'name' => 'bar',
        ];
        $expected = <<<'EOH'
            <fieldset name="bar">
            <legend>Foo</legend>

            EOH;
        $this->assertSame($expected, form_fieldset('Foo', $attributes));
    }

    // ------------------------------------------------------------------------
    public function testFormFieldsetClose()
    {
        $expected = <<<'EOH'
            </fieldset></div></div>
            EOH;
        $this->assertSame($expected, form_fieldset_close('</div></div>'));
    }

    // ------------------------------------------------------------------------
    public function testFormCheckbox()
    {
        $expected = <<<EOH
            <input type="checkbox" name="newsletter" value="accept" checked="checked" />\n
            EOH;
        $this->assertSame($expected, form_checkbox('newsletter', 'accept', true));
    }

    // ------------------------------------------------------------------------
    public function testFormCheckboxArrayData()
    {
        $data = [
            'name'    => 'foo',
            'value'   => 'bar',
            'checked' => true,
        ];
        $expected = <<<'EOH'
            <input type="checkbox" name="foo" value="bar" checked="checked" />

            EOH;
        $this->assertSame($expected, form_checkbox($data));
    }

    // ------------------------------------------------------------------------
    public function testFormCheckboxArrayDataWithCheckedFalse()
    {
        $data = [
            'name'    => 'foo',
            'value'   => 'bar',
            'checked' => false,
        ];
        $expected = <<<'EOH'
            <input type="checkbox" name="foo" value="bar" />

            EOH;
        $this->assertSame($expected, form_checkbox($data));
    }

    // ------------------------------------------------------------------------
    public function testFormRadio()
    {
        $expected = <<<EOH
            <input type="radio" name="newsletter" value="accept" checked="checked" />\n
            EOH;
        $this->assertSame($expected, form_radio('newsletter', 'accept', true));
    }

    // ------------------------------------------------------------------------
    public function testFormSubmit()
    {
        $expected = <<<EOH
            <input type="submit" name="mysubmit" value="Submit Post!" />\n
            EOH;
        $this->assertSame($expected, form_submit('mysubmit', 'Submit Post!'));
    }

    // ------------------------------------------------------------------------
    public function testFormLabel()
    {
        $expected = <<<'EOH'
            <label for="username">What is your Name</label>
            EOH;
        $this->assertSame($expected, form_label('What is your Name', 'username'));
    }

    // ------------------------------------------------------------------------
    public function testFormLabelWithAttributes()
    {
        $attributes = [
            'id' => 'label1',
        ];
        $expected = <<<'EOH'
            <label for="foo" id="label1">bar</label>
            EOH;
        $this->assertSame($expected, form_label('bar', 'foo', $attributes));
    }

    // ------------------------------------------------------------------------
    public function testFormReset()
    {
        $expected = <<<EOH
            <input type="reset" name="myreset" value="Reset" />\n
            EOH;
        $this->assertSame($expected, form_reset('myreset', 'Reset'));
    }

    // ------------------------------------------------------------------------
    public function testFormButton()
    {
        $expected = <<<EOH
            <button name="name" type="button">content</button>\n
            EOH;
        $this->assertSame($expected, form_button('name', 'content'));
    }

    // ------------------------------------------------------------------------
    public function testFormButtonWithDataArray()
    {
        $data = [
            'name'    => 'foo',
            'content' => 'bar',
        ];
        $expected = <<<'EOH'
            <button name="foo" type="button">bar</button>

            EOH;
        $this->assertSame($expected, form_button($data));
    }

    // ------------------------------------------------------------------------
    public function testFormClose()
    {
        $expected = <<<'EOH'
            </form></div></div>
            EOH;
        $this->assertSame($expected, form_close('</div></div>'));
    }

    // ------------------------------------------------------------------------
    public function testFormDatalist()
    {
        $options = [
            'foo1',
            'bar1',
        ];
        $expected = <<<'EOH'
            <input type="text" name="foo" value="bar" list="foo_list" />

            <datalist id='foo_list'><option value='foo1'>
            <option value='bar1'>
            </datalist>

            EOH;
        $this->assertSame($expected, form_datalist('foo', 'bar', $options));
    }

    // ------------------------------------------------------------------------
    public function testSetValue()
    {
        $_SESSION['_ci_old_input']['post']['foo'] = '<bar';
        $this->assertSame('&lt;bar', set_value('foo'));

        unset($_SESSION['_ci_old_input']['post']['foo']);
        $this->assertSame('baz', set_value('foo', 'baz'));
    }

    // ------------------------------------------------------------------------
    public function testSetSelect()
    {
        $_SESSION['_ci_old_input']['post']['foo'] = 'bar';
        $this->assertSame(' selected="selected"', set_select('foo', 'bar'));

        $_SESSION['_ci_old_input']['post']['foo'] = ['foo' => 'bar'];
        $this->assertSame(' selected="selected"', set_select('foo', 'bar'));
        $this->assertSame('', set_select('foo', 'baz'));

        unset($_SESSION['_ci_old_input']['post']['foo']);
        $this->assertSame(' selected="selected"', set_select('foo', 'baz', true));
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

        $this->assertSame(' checked="checked"', set_checkbox('foo', 'bar'));

        $_SESSION = [
            '_ci_old_input' => [
                'post' => [
                    'foo' => ['foo' => 'bar'],
                ],
            ],
        ];
        $this->assertSame(' checked="checked"', set_checkbox('foo', 'bar'));
        $this->assertSame('', set_checkbox('foo', 'baz'));

        $_SESSION = [];
        $this->assertSame('', set_checkbox('foo', 'bar'));

        $_SESSION = [];
        $this->assertSame(' checked="checked"', set_checkbox('foo', 'bar', true));
    }

    // ------------------------------------------------------------------------
    public function testSetCheckboxWithValueZero()
    {
        $_SESSION = [
            '_ci_old_input' => [
                'post' => [
                    'foo' => '0',
                ],
            ],
        ];

        $this->assertSame(' checked="checked"', set_checkbox('foo', '0'));

        $_SESSION = [
            '_ci_old_input' => [
                'post' => [
                    'foo' => ['foo' => '0'],
                ],
            ],
        ];
        $this->assertSame(' checked="checked"', set_checkbox('foo', '0'));
        $this->assertSame('', set_checkbox('foo', 'baz'));

        $_SESSION = [];
        $this->assertSame('', set_checkbox('foo', 'bar'));

        $_SESSION = [];
        $this->assertSame(' checked="checked"', set_checkbox('foo', '0', true));
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

        $this->assertSame(' checked="checked"', set_radio('foo', 'bar'));
        $this->assertSame('', set_radio('foo', 'baz'));
        unset($_SESSION['_ci_old_input']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testSetRadioFromPost()
    {
        $_POST['bar'] = 'baz';
        $this->assertSame(' checked="checked"', set_radio('bar', 'baz'));
        $this->assertSame('', set_radio('bar', 'boop'));
        $this->assertSame(' checked="checked"', set_radio('bar', 'boop', true));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState  disabled
     */
    public function testSetRadioFromPostWithValueZero()
    {
        $_POST['bar'] = 0;
        $this->assertSame(' checked="checked"', set_radio('bar', '0'));
        $this->assertSame('', set_radio('bar', 'boop'));

        $_POST = [];
        $this->assertSame(' checked="checked"', set_radio('bar', '0', true));
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
        $this->assertSame(' checked="checked"', set_radio('bar', 'boop'));
        $this->assertSame('', set_radio('bar', 'baz'));
    }

    public function testSetRadioFromPostArrayWithValueZero()
    {
        $_SESSION = [
            '_ci_old_input' => [
                'post' => [
                    'bar' => [
                        '0',
                        'fuzzy',
                    ],
                ],
            ],
        ];
        $this->assertSame(' checked="checked"', set_radio('bar', '0'));
        $this->assertSame('', set_radio('bar', 'baz'));
    }

    public function testSetRadioDefault()
    {
        $this->assertSame(' checked="checked"', set_radio('code', 'alpha', true));
        $this->assertSame('', set_radio('code', 'beta', false));
    }

    // ------------------------------------------------------------------------
    public function testFormParseFormAttributesTrue()
    {
        $expected = 'readonly ';
        $this->assertSame($expected, parse_form_attributes(['readonly' => true], []));
    }

    // ------------------------------------------------------------------------
    public function testFormParseFormAttributesFalse()
    {
        $expected = 'disabled ';
        $this->assertSame($expected, parse_form_attributes(['disabled' => false], []));
    }

    // ------------------------------------------------------------------------
    public function testFormParseFormAttributesNull()
    {
        $expected = 'bar=""';
        $this->assertSame($expected, parse_form_attributes(['bar' => null], []));
    }

    // ------------------------------------------------------------------------
    public function testFormParseFormAttributesStringEmpty()
    {
        $expected = 'bar=""';
        $this->assertSame($expected, parse_form_attributes(['bar' => ''], []));
    }

    // ------------------------------------------------------------------------
    public function testFormParseFormAttributesStringFoo()
    {
        $expected = 'bar="foo"';
        $this->assertSame($expected, parse_form_attributes(['bar' => 'foo'], []));
    }

    // ------------------------------------------------------------------------
    public function testFormParseFormAttributesInt0()
    {
        $expected = 'ok="0"';
        $this->assertSame($expected, parse_form_attributes(['ok' => 0], []));
    }

    // ------------------------------------------------------------------------
    public function testFormParseFormAttributesInt1()
    {
        $expected = 'ok="1"';
        $this->assertSame($expected, parse_form_attributes(['ok' => 1], []));
    }
}
