# HTML Helper

The HTML Helper file contains functions that assist in working with HTML.

## Configuration

Since `v4.3.0`, void HTML elements (e.g. `<img>`) in `html_helper` functions have been changed to be HTML5-compatible by default. If you need to be compatible with XHTML, you must set the `$html5` property in **app/Config/DocTypes.php** to `false`.

## Loading this Helper

This helper is loaded using the following code:

```php
<?php

helper('html');
```

## Available Functions

The following functions are available:

#### img([$src = ''[, $indexPage = false[, $attributes = '']]])

- **Parameters**  
    - **$src** `string|array` Image source URI, or array of attributes and values
    - **$indexPage** `bool` Should `Config\App::$indexPage` be added to the source path
    - **$attributes** `mixed` Additional HTML attributes

- **Returns**: An HTML image element

- **Return type**: `string`

Lets you create HTML `<img>` elements. The first parameter contains the image source. Example:

```php
<?php

echo img('images/picture.jpg');
// <img src="http://site.com/images/picture.jpg">
```

There is an optional second parameter, a true/false value, that specifies if the *src* should have `Config\App::$indexPage` added to the address it creates. Presumably, this would be if you were using a media controller:

```php
<?php

echo img('images/picture.jpg', true);
// <img src="http://site.com/index.php/images/picture.jpg" alt="">
```

Additionally, an associative array can be passed as the first parameter, for complete control over all attributes and values. If an *alt* attribute is not provided, CodeIgniter will generate one with an empty string.

Example:

```php
<?php

$imageProperties = [
    'src'    => 'images/picture.jpg',
    'alt'    => 'Me, demonstrating how to eat 4 slices of pizza at one time',
    'class'  => 'post_images',
    'width'  => '200',
    'height' => '200',
    'title'  => 'That was quite a night',
    'rel'    => 'lightbox',
];

img($imageProperties);
// <img src="http://site.com/index.php/images/picture.jpg" alt="Me, demonstrating how to eat 4 slices of pizza at one time" class="post_images" width="200" height="200" title="That was quite a night" rel="lightbox">
```

#### img_data($path[, $mime = null])

- **Parameters**  
    - **$path** `string` Path to the image file
    - **$mime** `string|null` MIME type to use, or null to guess

- **Returns**: base64-encoded binary image string

- **Return type**: `string`

Generates a src-ready string from an image using the "data:" protocol. Example:

```php
<?php

$src = img_data('public/images/picture.jpg'); // data:image/jpg;base64,R0lGODl...
echo img($src);
```

There is an optional second parameter to specify the MIME type. Otherwise, the function will use your Mimes config to guess:

```php
<?php

$src = img_data('path/img_without_extension', 'image/png'); // data:image/png;base64,HT5A822...
```

Note that `$path` must exist and be a readable image format supported by the `data:` protocol. This function is not recommended for very large files, but it provides a convenient way of serving images from your app that are not web-accessible (e.g., in **writable/**).

#### link_tag([$href = ''[, $rel = 'stylesheet'[, $type = 'text/css'[, $title = ''[, $media = ''[, $indexPage = false[, $hreflang = '']]]]]]])

- **Parameters**  
    - **$href** `string` The source of the link file
    - **$rel** `string` Relation type
    - **$type** `string` Type of the related document
    - **$title** `string` Link title
    - **$media** `string` Media type
    - **$indexPage** `bool` Should indexPage be added to the link path
    - **$hreflang** `string` Hreflang type

- **Returns**: An HTML link element

- **Return type**: `string`

Lets you create HTML `<link>` elements. This is useful for stylesheet links, as well as other links. The parameters are *href*, with optional *rel*, *type*, *title*, *media*, *indexPage* and *hreflang*.

*indexPage* is a boolean value that specifies if the *href* should have the page specified by `Config\App::$indexPage` added to the address it creates.

Example:

```php
<?php

echo link_tag('css/mystyles.css');
// <link href="http://site.com/css/mystyles.css" rel="stylesheet" type="text/css">
```

Further examples:

```php
<?php

echo link_tag('favicon.ico', 'shortcut icon', 'image/ico');
// <link href="http://site.com/favicon.ico" rel="shortcut icon" type="image/ico">

echo link_tag('feed', 'alternate', 'application/rss+xml', 'My RSS Feed');
// <link href="http://site.com/feed" rel="alternate" type="application/rss+xml" title="My RSS Feed">
```

Alternatively, an associative array can be passed to the `link_tag()` function for complete control over all attributes and values:

```php
<?php

$link = [
    'href'  => 'css/printer.css',
    'rel'   => 'stylesheet',
    'type'  => 'text/css',
    'media' => 'print',
];

echo link_tag($link);
// <link href="http://site.com/css/printer.css" rel="stylesheet" type="text/css" media="print">
```

#### script_tag([$src = ''[, $indexPage = false]])

- **Parameters**  
    - **$src** `array|string` The source name or URL of a JavaScript file, or an associative array specifying the attributes
    - **$indexPage** `bool` Whether to treat `$src` as a routed URI string

- **Returns**: An HTML script element

- **Return type**: `string`

Lets you create HTML `<script>` elements. The parameters are *src* and optional *indexPage*.

*indexPage* is a boolean value that specifies if the *src* should have the page specified by `Config\App::$indexPage` added to the address it creates.

Example:

```php
<?php

echo script_tag('js/mystyles.js');
// <script src="http://site.com/js/mystyles.js"></script>
```

Alternatively, an associative array can be passed to the `script_tag()` function for complete control over all attributes and values:

```php
<?php

$script = ['src' => 'js/printer.js', 'defer' => null];

echo script_tag($script);
// <script src="http://site.com/js/printer.js" defer></script>
```

#### ul($list[, $attributes = ''])

- **Parameters**  
    - **$list** `array` List entries
    - **$attributes** `array` HTML attributes

- **Returns**: An HTML unordered list element

- **Return type**: `string`

Permits you to generate an unordered HTML list from a simple or multi-dimensional array. Example:

```php
<?php

$list = [
    'red',
    'blue',
    'green',
    'yellow',
];

$attributes = [
    'class' => 'boldlist',
    'id'    => 'mylist',
];

echo ul($list, $attributes);
```

The above code will produce this:

```html
<ul class="boldlist" id="mylist">
    <li>red</li>
    <li>blue</li>
    <li>green</li>
    <li>yellow</li>
</ul>
```

Here is a more complex example, using a multi-dimensional array:

```php
<?php

$attributes = [
    'class' => 'boldlist',
    'id'    => 'mylist',
];

$list = [
    'colors' => [
        'red',
        'blue',
        'green',
    ],
    'shapes' => [
        'round',
        'square',
        'circles' => [
            'ellipse',
            'oval',
            'sphere',
        ],
    ],
    'moods' => [
        'happy',
        'upset' => [
            'defeated' => [
                'dejected',
                'disheartened',
                'depressed',
            ],
            'annoyed',
            'cross',
            'angry',
        ],
    ],
];

echo ul($list, $attributes);
```

The above code will produce this:

```html
<ul class="boldlist" id="mylist">
    <li>colors
        <ul>
            <li>red</li>
            <li>blue</li>
            <li>green</li>
        </ul>
    </li>
    <li>shapes
        <ul>
            <li>round</li>
            <li>square</li>
            <li>circles
                <ul>
                    <li>ellipse</li>
                    <li>oval</li>
                    <li>sphere</li>
                </ul>
            </li>
        </ul>
    </li>
    <li>moods
        <ul>
            <li>happy</li>
            <li>upset
                <ul>
                    <li>defeated
                        <ul>
                            <li>dejected</li>
                            <li>disheartened</li>
                            <li>depressed</li>
                        </ul>
                    </li>
                    <li>annoyed</li>
                    <li>cross</li>
                    <li>angry</li>
                </ul>
            </li>
        </ul>
    </li>
</ul>
```

#### ol($list, $attributes = '')

- **Parameters**  
    - **$list** `array` List entries
    - **$attributes** `array` HTML attributes

- **Returns**: An HTML ordered list element

- **Return type**: `string`

Identical to `ul()`, except that it produces an `<ol>` element for ordered lists instead of `<ul>`.

#### video($src[, $unsupportedMessage = ''[, $attributes = ''[, $tracks = [][, $indexPage = false]]]])

- **Parameters**  
    - **$src** `mixed` Either a source string or an array of sources. See `source()` function
    - **$unsupportedMessage** `string` The message to display if the video element is not supported by the browser
    - **$attributes** `string` HTML attributes
    - **$tracks** `array` Use the track function inside an array. See `track()` function
    - **$indexPage** `bool` Should indexPage be added to the video source path

- **Returns**: An HTML video element

- **Return type**: `string`

Permits you to generate an HTML video element from a source string or an array of sources. Example:

```php
<?php

$tracks = [
    track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No'),
    track('subtitles_yes.vtt', 'subtitles', 'yes', 'Norwegian Yes'),
];

echo video('test.mp4', 'Your browser does not support the video tag.', 'controls');

echo video(
    'http://www.codeigniter.com/test.mp4',
    'Your browser does not support the video tag.',
    'controls',
    $tracks,
);

echo video(
    [
        source('movie.mp4', 'video/mp4', 'class="test"'),
        source('movie.ogg', 'video/ogg'),
        source('movie.mov', 'video/quicktime'),
        source('movie.ogv', 'video/ogv; codecs=dirac, speex'),
    ],
    'Your browser does not support the video tag.',
    'class="test" controls',
    $tracks,
);
```

The above code will produce this:

```html
<video src="test.mp4" controls>
  Your browser does not support the video tag.
</video>

<video src="http://www.codeigniter.com/test.mp4" controls>
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the video tag.
</video>

<video class="test" controls>
  <source src="movie.mp4" type="video/mp4" class="test" />
  <source src="movie.ogg" type="video/ogg" />
  <source src="movie.mov" type="video/quicktime" />
  <source src="movie.ogv" type="video/ogv; codecs=dirac, speex" />
  <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />
  <track src="subtitles_yes.vtt" kind="subtitles" srclang="yes" label="Norwegian Yes" />
  Your browser does not support the video tag.
</video>
```

#### audio($src[, $unsupportedMessage = ''[, $attributes = ''[, $tracks = [][, $indexPage = false]]]])

- **Parameters**  
    - **$src** `mixed` Either a source string or an array of sources. See `source()` function
    - **$unsupportedMessage** `string` The message to display if the audio element is not supported by the browser
    - **$attributes** `string` HTML attributes
    - **$tracks** `array` Use the track function inside an array. See `track()` function
    - **$indexPage** `bool` Should indexPage be added to the audio source path

- **Returns**: An HTML audio element

- **Return type**: `string`

Identical to `video()`, except that it produces an `<audio>` element instead of `<video>`.

#### source($src, $type = 'unknown', $attributes = '', $indexPage = false)

- **Parameters**  
    - **$src** `string` The path of the media resource
    - **$type** `string` The MIME-type of the resource with optional codecs parameters
    - **$attributes** `string` HTML attributes
    - **$indexPage** `bool` Should indexPage be added to the source path

- **Returns**: An HTML source element

- **Return type**: `string`

Lets you create HTML `<source>` elements. The first parameter contains the path of the resource. Example:

```php
<?php

echo source('movie.mp4', 'video/mp4', 'class="test"');
// <source src="movie.mp4" type="video/mp4" class="test">
```

#### embed($src[, $type = 'unknown'[, $attributes = ''[, $indexPage = false]]])

- **Parameters**  
    - **$src** `string` The path of the resource to embed
    - **$type** `string` MIME-type
    - **$attributes** `string` HTML attributes
    - **$indexPage** `bool` Should indexPage be added to the source path

- **Returns**: An HTML embed element

- **Return type**: `string`

Lets you create HTML `<embed>` elements. The first parameter contains the embed source. Example:

```php
<?php

echo embed('movie.mov', 'video/quicktime', 'class="test"');
// <embed src="movie.mov" type="video/quicktime" class="test">
```

#### object($data[, $type = 'unknown'[, $attributes = ''[, $params = [][, $indexPage = false]]]])

- **Parameters**  
    - **$data** `string` A resource URL
    - **$type** `string` Content-type of the resource
    - **$attributes** `string` HTML attributes
    - **$indexPage** `bool` Should indexPage be added to the resource URL
    - **$params** `array` Use the param function inside an array. See `param()` function

- **Returns**: An HTML object element

- **Return type**: `string`

Lets you create HTML `<object>` elements. The first parameter contains the object data. Example:

```php
<?php

echo object('movie.swf', 'application/x-shockwave-flash', 'class="test"');

echo object(
    'movie.swf',
    'application/x-shockwave-flash',
    'class="test"',
    [
        param('foo', 'bar', 'ref', 'class="test"'),
        param('hello', 'world', 'ref', 'class="test"'),
    ],
);
```

The above code will produce this:

```html
<object data="movie.swf" class="test"></object>

<object data="movie.swf" class="test">
  <param name="foo" type="ref" value="bar" class="test" />
  <param name="hello" type="ref" value="world" class="test" />
</object>
```

#### param($name, $value[, $type = 'ref'[, $attributes = '']])

- **Parameters**  
    - **$name** `string` The name of the parameter
    - **$value** `string` The value of the parameter
    - **$type** `string` The type of the parameter value
    - **$attributes** `string` HTML attributes

- **Returns**: An HTML param element

- **Return type**: `string`

Lets you create HTML `<param>` elements. The first parameter contains the param source. Example:

```php
<?php

echo param('movie.mov', 'video/quicktime', 'class="test"');
// <param src="movie.mov" type="video/quicktime" class="test">
```

#### track($src, $kind, $srcLanguage, $label)

- **Parameters**  
    - **$src** `string` The path of the track (.vtt file)
    - **$kind** `string` The kind of timed track
    - **$srcLanguage** `string` The language of the timed track
    - **$label** `string` A user-readable title for the timed track

- **Returns**: An HTML track element

- **Return type**: `string`

Generates a track element to specify timed tracks. The tracks are formatted in WebVTT format. Example:

```php
<?php

echo track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No');
// <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No">
```

#### doctype([$type = 'html5'])

- **Parameters**  
    - **$type** `string` Doctype name

- **Returns**: An HTML DocType declaration

- **Return type**: `string`

Helps you generate document type declarations (DTDs). HTML 5 is used by default, but many doctypes are available.

Example:

```php
<?php

echo doctype();
// <!DOCTYPE html>

echo doctype('html4-trans');
// <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
```

The following is a list of the pre-defined doctypes. These are pulled from **app/Config/DocTypes.php**, or they could be overridden in your **.env** configuration.

| Document type | $type parameter | Result |
|----|----|----|
| XHTML 1.1 | xhtml11 | &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "<http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd>"> |
| XHTML 1.0 Strict | xhtml1-strict | &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "<http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd>"> |
| XHTML 1.0 Transitional | xhtml1-trans | &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "<http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd>"> |
| XHTML 1.0 Frameset | xhtml1-frame | &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "<http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd>"> |
| XHTML Basic 1.1 | xhtml-basic11 | &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "<http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd>"> |
| HTML 5 | html5 | &lt;!DOCTYPE html> |
| HTML 4 Strict | html4-strict | &lt;!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "<http://www.w3.org/TR/html4/strict.dtd>"> |
| HTML 4 Transitional | html4-trans | &lt;!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "<http://www.w3.org/TR/html4/loose.dtd>"> |
| HTML 4 Frameset | html4-frame | &lt;!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "<http://www.w3.org/TR/html4/frameset.dtd>"> |
| MathML 1.01 | mathml1 | &lt;!DOCTYPE math SYSTEM "<http://www.w3.org/Math/DTD/mathml1/mathml.dtd>"> |
| MathML 2.0 | mathml2 | &lt;!DOCTYPE math PUBLIC "-//W3C//DTD MathML 2.0//EN" "<http://www.w3.org/Math/DTD/mathml2/mathml2.dtd>"> |
| SVG 1.0 | svg10 | &lt;!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" "<http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd>"> |
| SVG 1.1 Full | svg11 | &lt;!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "<http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd>"> |
| SVG 1.1 Basic | svg11-basic | &lt;!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Basic//EN" "<http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-basic.dtd>"> |
| SVG 1.1 Tiny | svg11-tiny | &lt;!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Tiny//EN" "<http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-tiny.dtd>"> |
| XHTML+MathML+SVG (XHTML host) | xhtml-math-svg-xh | &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "<http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd>"> |
| XHTML+MathML+SVG (SVG host) | xhtml-math-svg-sh | &lt;!DOCTYPE svg:svg PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "<http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd>"> |
| XHTML+RDFa 1.0 | xhtml-rdfa-1 | &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "<http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd>"> |
| XHTML+RDFa 1.1 | xhtml-rdfa-2 | &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.1//EN" "<http://www.w3.org/MarkUp/DTD/xhtml-rdfa-2.dtd>"> |
