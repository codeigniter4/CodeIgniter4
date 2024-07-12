<?php

namespace Config;

/**
 * Configuration class for document types and HTML compatibility.
 * 
 * @immutable
 */
class DocTypes
{
    /**
     * List of valid document types.
     *
     * @var array<string, string>
     */
    public array $list = [
        'xhtml11'           => self::XHTML11,
        'xhtml1-strict'     => self::XHTML1_STRICT,
        'xhtml1-trans'      => self::XHTML1_TRANS,
        'xhtml1-frame'      => self::XHTML1_FRAME,
        'xhtml-basic11'     => self::XHTML_BASIC11,
        'html5'             => self::HTML5,
        'html4-strict'      => self::HTML4_STRICT,
        'html4-trans'       => self::HTML4_TRANS,
        'html4-frame'       => self::HTML4_FRAME,
        'mathml1'           => self::MATHML1,
        'mathml2'           => self::MATHML2,
        'svg10'             => self::SVG10,
        'svg11'             => self::SVG11,
        'svg11-basic'       => self::SVG11_BASIC,
        'svg11-tiny'        => self::SVG11_TINY,
        'xhtml-math-svg-xh' => self::XHTML_MATH_SVG_XH,
        'xhtml-math-svg-sh' => self::XHTML_MATH_SVG_SH,
        'xhtml-rdfa-1'      => self::XHTML_RDFA_1,
        'xhtml-rdfa-2'      => self::XHTML_RDFA_2,
    ];

    /**
     * Whether to remove the solidus (`/`) character for void HTML elements (e.g. `<input>`)
     * for HTML5 compatibility.
     *
     * Set to:
     *    `true` - to be HTML5 compatible
     *    `false` - to be XHTML compatible
     *
     * @var bool
     */
    public bool $html5 = true;

    // Document type strings as constants
    public const XHTML11           = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
    public const XHTML1_STRICT     = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    public const XHTML1_TRANS      = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    public const XHTML1_FRAME      = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
    public const XHTML_BASIC11     = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">';
    public const HTML5             = '<!DOCTYPE html>';
    public const HTML4_STRICT      = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
    public const HTML4_TRANS       = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    public const HTML4_FRAME       = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
    public const MATHML1           = '<!DOCTYPE math SYSTEM "http://www.w3.org/Math/DTD/mathml1/mathml.dtd">';
    public const MATHML2           = '<!DOCTYPE math PUBLIC "-//W3C//DTD MathML 2.0//EN" "http://www.w3.org/Math/DTD/mathml2/mathml2.dtd">';
    public const SVG10             = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">';
    public const SVG11             = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
    public const SVG11_BASIC       = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Basic//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-basic.dtd">';
    public const SVG11_TINY        = '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Tiny//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-tiny.dtd">';
    public const XHTML_MATH_SVG_XH = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">';
    public const XHTML_MATH_SVG_SH = '<!DOCTYPE svg:svg PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN" "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">';
    public const XHTML_RDFA_1      = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">';
    public const XHTML_RDFA_2      = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.1//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-2.dtd">';
}
