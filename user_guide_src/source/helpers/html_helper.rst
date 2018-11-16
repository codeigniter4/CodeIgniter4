###########
HTML Helper
###########

The HTML Helper file contains functions that assist in working with
HTML.

.. contents::
    :local:

.. raw:: html

    <div class="custom-index container"></div>

Loading this Helper
===================

This helper is loaded using the following code::

    helper('html');

Available Functions
===================

The following functions are available:

.. php:function:: img([$src = ''[, $indexPage = false[, $attributes = '']]])

    :param  mixed  $src:        Image source data
    :param  bool    $indexPage:  Whether to treat $src as a routed URI string
    :param  mixed   $attributes: HTML attributes
    :returns:   HTML image tag
    :rtype: string

    Lets you create HTML <img /> tags. The first parameter contains the
    image source. Example::

        echo img('images/picture.jpg');
        // <img src="http://site.com/images/picture.jpg" />

    There is an optional second parameter that is a true/false value that
    specifics if the *src* should have the page specified by
    ``$config['indexPage']`` added to the address it creates.
    Presumably, this would be if you were using a media controller::

        echo img('images/picture.jpg', true);
        // <img src="http://site.com/index.php/images/picture.jpg" alt="" />

    Additionally, an associative array can be passed as the first parameter,
    for complete control over all attributes and values. If an *alt* attribute
    is not provided, CodeIgniter will generate an empty string.

    Example::

        $imageProperties = [
            'src'    => 'images/picture.jpg',
            'alt'    => 'Me, demonstrating how to eat 4 slices of pizza at one time',
            'class'  => 'post_images',
            'width'  => '200',
            'height' => '200',
            'title'  => 'That was quite a night',
            'rel'    => 'lightbox'
        ];

        img($imageProperties);
        // <img src="http://site.com/index.php/images/picture.jpg" alt="Me, demonstrating how to eat 4 slices of pizza at one time" class="post_images" width="200" height="200" title="That was quite a night" rel="lightbox" />

.. php:function:: link_tag([$href = ''[, $rel = 'stylesheet'[, $type = 'text/css'[, $title = ''[, $media = ''[, $indexPage = false]]]]]])

    :param  string  $href:      The source of the link file
    :param  string  $rel:       Relation type
    :param  string  $type:      Type of the related document
    :param  string  $title:     Link title
    :param  string  $media:     Media type
    :param  bool    $indexPage: Whether to treat $src as a routed URI string
    :returns:   HTML link tag
    :rtype: string

    Lets you create HTML <link /> tags. This is useful for stylesheet links,
    as well as other links. The parameters are *href*, with optional *rel*,
    *type*, *title*, *media* and *indexPage*.

    *indexPage* is a boolean value that specifies if the *href* should have
    the page specified by ``$config['indexPage']`` added to the address it creates.

    Example::

        echo link_tag('css/mystyles.css');
        // <link href="http://site.com/css/mystyles.css" rel="stylesheet" type="text/css" />

    Further examples::

        echo link_tag('favicon.ico', 'shortcut icon', 'image/ico');
        // <link href="http://site.com/favicon.ico" rel="shortcut icon" type="image/ico" />

        echo link_tag('feed', 'alternate', 'application/rss+xml', 'My RSS Feed');
        // <link href="http://site.com/feed" rel="alternate" type="application/rss+xml" title="My RSS Feed" />

    Alternately, an associative array can be passed to the ``link_tag()`` function
    for complete control over all attributes and values::

        $link = [
            'href'  => 'css/printer.css',
            'rel'   => 'stylesheet',
            'type'  => 'text/css',
            'media' => 'print'
        ];

        echo link_tag($link);
        // <link href="http://site.com/css/printer.css" rel="stylesheet" type="text/css" media="print" />

.. php:function:: script_tag([$src = ''[, $indexPage = false]])

    :param  mixed  $src: The source name of a JavaScript file
    :param  bool    $indexPage: Whether to treat $src as a routed URI string
    :returns:   HTML script tag
    :rtype: string

    Lets you create HTML <script></script> tags. The parameters is *src*, with optional *indexPage*.

    *indexPage* is a boolean value that specifies if the *src* should have
    the page specified by ``$config['indexPage']`` added to the address it creates.

    Example::

        echo script_tag('js/mystyles.js');
        // <script src="http://site.com/js/mystyles.js" type="text/javascript"></script>

    Alternately, an associative array can be passed to the ``script_tag()`` function
    for complete control over all attributes and values::

        $script = ['src'  => 'js/printer.js'];

        echo script_tag($script);
        // <script src="http://site.com/js/printer.js" type="text/javascript"></script>

.. php:function:: ul($list[, $attributes = ''])

    :param  array   $list: List entries
    :param  array   $attributes: HTML attributes
    :returns:   HTML-formatted unordered list
    :rtype: string

    Permits you to generate unordered HTML lists from simple or
    multi-dimensional arrays. Example::

        $list = [
            'red',
            'blue',
            'green',
            'yellow'
        ];

        $attributes = [
            'class' => 'boldlist',
            'id'    => 'mylist'
        ];

        echo ul($list, $attributes);

    The above code will produce this:

    .. code-block:: html

        <ul class="boldlist" id="mylist">
            <li>red</li>
            <li>blue</li>
            <li>green</li>
            <li>yellow</li>
        </ul>

    Here is a more complex example, using a multi-dimensional array::

        $attributes = [
            'class' => 'boldlist',
            'id'    => 'mylist'
        ];

        $list = [
            'colors' => [
                'red',
                'blue',
                'green'
            ],
            'shapes' => [
                'round',
                'square',
                'circles' => [
                    'ellipse',
                    'oval',
                    'sphere'
                ]
            ],
            'moods'  => [
                'happy',
                'upset'   => [
                    'defeated' => [
                        'dejected',
                        'disheartened',
                        'depressed'
                    ],
                    'annoyed',
                    'cross',
                    'angry'
                ]
            ]
        ];

        echo ul($list, $attributes);

    The above code will produce this:

    .. code-block:: html

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
                    <li>suare</li>
                    <li>circles
                        <ul>
                            <li>elipse</li>
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

.. php:function:: ol($list, $attributes = '')

    :param  array   $list: List entries
    :param  array   $attributes: HTML attributes
    :returns:   HTML-formatted ordered list
    :rtype: string

    Identical to :php:func:`ul()`, only it produces the <ol> tag for
    ordered lists instead of <ul>.

.. php:function:: video($src[, $unsupportedMessage = ''[, $attributes = ''[, $tracks = [][, $indexPage = false]]]])

    :param  mixed   $src:                Either a source string or an array of sources. See :php:func:`source()` function
    :param  string  $unsupportedMessage: The message to display if the media tag is not supported by the browser
    :param  string  $attributes:         HTML attributes
    :param  array   $tracks:             Use the track function inside an array. See :php:func:`track()` function
    :param  bool    $indexPage:
    :returns:                            HTML-formatted video element
    :rtype: string

    Permits you to generate HTML video element from simple or
    source arrays. Example::

        $tracks =
        [
            track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No'),
            track('subtitles_yes.vtt', 'subtitles', 'yes', 'Norwegian Yes')
        ];

        echo video('test.mp4', 'Your browser does not support the video tag.', 'controls');

        echo video
        (
            'http://www.codeigniter.com/test.mp4',
            'Your browser does not support the video tag.',
            'controls',
            $tracks
        );

        echo video
        (
            [
              source('movie.mp4', 'video/mp4', 'class="test"'),
              source('movie.ogg', 'video/ogg'),
              source('movie.mov', 'video/quicktime'),
              source('movie.ogv', 'video/ogv; codecs=dirac, speex')
            ],
            'Your browser does not support the video tag.',
            'class="test" controls',
            $tracks
         );

    The above code will produce this:

    .. code-block:: html

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

.. php:function:: audio($src[, $unsupportedMessage = ''[, $attributes = ''[, $tracks = [][, $indexPage = false]]]])

    :param  mixed   $src:                Either a source string or an array of sources. See :php:func:`source()` function
    :param  string  $unsupportedMessage: The message to display if the media tag is not supported by the browser
    :param  string  $attributes:
    :param  array   $tracks:             Use the track function inside an array. See :php:func:`track()` function
    :param  bool    $indexPage:
    :returns:                            HTML-formatted audio element
    :rtype: string

    Identical to :php:func:`video()`, only it produces the <audio> tag instead of <video>.

.. php:function:: source($src = ''[, $type = false[, $attributes = '']])

    :param  string  $src:        The path of the media resource
    :param  bool    $type:       The MIME-type of the resource with optional codecs parameters
    :param  array   $attributes: HTML attributes
    :returns:   HTML source tag
    :rtype: string

    Lets you create HTML <source /> tags. The first parameter contains the
    source source. Example::

        echo source('movie.mp4', 'video/mp4', 'class="test"');
        // <source src="movie.mp4" type="video/mp4" class="test" />

.. php:function:: embed($src = ''[, $type = false[, $attributes = ''[, $indexPage = false]]])

    :param  string  $src:        The path of the resource to embed
    :param  bool    $type:       MIME-type
    :param  array   $attributes: HTML attributes
    :param  bool    $indexPage:
    :returns:   HTML embed tag
    :rtype: string

    Lets you create HTML <embed /> tags. The first parameter contains the
    embed source. Example::

        echo embed('movie.mov', 'video/quicktime', 'class="test"');
        // <embed src="movie.mov" type="video/quicktime" class="test"/>

.. php:function:: object($data = ''[, $type = false[, $attributes = '']])

    :param  string  $data:       A resource URL
    :param  bool    $type:       Content-type of the resource
    :param  array   $attributes: HTML attributes
    :param  array   $params:     Use the param function inside an array. See :php:func:`param()` function
    :returns:   HTML object tag
    :rtype: string

    Lets you create HTML <object /> tags. The first parameter contains the
    object data. Example::

        echo object('movie.swf', 'application/x-shockwave-flash', 'class="test"');

        echo object
        (
            'movie.swf',
            'application/x-shockwave-flash',
            'class="test"',
            [
                param('foo', 'bar', 'ref', 'class="test"'),
                param('hello', 'world', 'ref', 'class="test"')
            ]
        );

    The above code will produce this:

    .. code-block:: html

        <object data="movie.swf" class="test"></object>

        <object data="movie.swf" class="test">
          <param name="foo" type="ref" value="bar" class="test" />
          <param name="hello" type="ref" value="world" class="test" />
        </object>

.. php:function:: param($name = ''[, $type = false[, $attributes = '']])

    :param  string  $name:       The name of the parameter
    :param  string  $value:      The value of the parameter
    :param  array   $attributes: HTML attributes
    :returns:   HTML param tag
    :rtype: string

    Lets you create HTML <param /> tags. The first parameter contains the
    param source. Example::

        echo param('movie.mov', 'video/quicktime', 'class="test"');
        // <param src="movie.mov" type="video/quicktime" class="test"/>

.. php:function:: track($name = ''[, $type = false[, $attributes = '']])

    :param  string  $name:       The name of the parameter
    :param  string  $value:      The value of the parameter
    :param  array   $attributes: HTML attributes
    :returns:   HTML track tag
    :rtype: string

    Generates a track element to specify timed tracks. The tracks are
    formatted in WebVTT format. Example::

        echo track('subtitles_no.vtt', 'subtitles', 'no', 'Norwegian No');
        // <track src="subtitles_no.vtt" kind="subtitles" srclang="no" label="Norwegian No" />

.. php:function:: doctype([$type = 'html5'])

    :param  string  $type: Doctype name
    :returns:   HTML DocType tag
    :rtype: string

    Helps you generate document type declarations, or DTD's. HTML 5
    is used by default, but many doctypes are available.

    Example::

        echo doctype();
        // <!DOCTYPE html>

        echo doctype('html4-trans');
        // <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

    The following is a list of the pre-defined doctype choices. These are configurable,
    pulled from `application/Config/DocTypes.php`, or they could be over-ridden in your `.env` configuration.

    =============================== =================== ==================================================================================================================================================
    Document type                   Option              Result
    =============================== =================== ==================================================================================================================================================
    XHTML 1.1                       xhtml11             <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
    XHTML 1.0 Strict                xhtml1-strict       <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    XHTML 1.0 Transitional          xhtml1-trans        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    XHTML 1.0 Frameset              xhtml1-frame        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
    XHTML Basic 1.1                 xhtml-basic11       <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">
    HTML 5                          html5               <!DOCTYPE html>
    HTML 4 Strict                   html4-strict        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
    HTML 4 Transitional             html4-trans         <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    HTML 4 Frameset                 html4-frame         <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
    MathML 1.01                     mathml1             <!DOCTYPE math SYSTEM "http://www.w3.org/Math/DTD/mathml1/mathml.dtd">
    MathML 2.0                      mathml2             <!DOCTYPE math PUBLIC "-//W3C//DTD MathML 2.0//EN" "http://www.w3.org/Math/DTD/mathml2/mathml2.dtd">
    SVG 1.0                         svg10               <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
    SVG 1.1 Full                    svg11               <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
    SVG 1.1 Basic                   svg11-basic         <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Basic//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-basic.dtd">
    SVG 1.1 Tiny                    svg11-tiny          <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Tiny//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-tiny.dtd">
    XHTML+MathML+SVG (XHTML host)   xhtml-math-svg-xh   <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">
    XHTML+MathML+SVG (SVG host)     xhtml-math-svg-sh   <!DOCTYPE svg:svg PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">
    XHTML+RDFa 1.0                  xhtml-rdfa-1        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
    XHTML+RDFa 1.1                  xhtml-rdfa-2        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.1//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-2.dtd">
    =============================== =================== ==================================================================================================================================================
