<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Kint;

use Kint\Renderer\RichRenderer as KintRichRenderer;

/**
 * Overrides RichRenderer::preRender() for CSP
 */
class RichRenderer extends KintRichRenderer
{
    public function preRender()
    {
        $output = '';

        if ($this->pre_render) {
            foreach (self::$pre_render_sources as $type => $values) {
                $contents = '';

                foreach ($values as $v) {
                    $contents .= $v($this);
                }

                if (! \strlen($contents)) {
                    continue;
                }

                switch ($type) {
                    case 'script':
                        $output .= '<script {csp-script-nonce} class="kint-rich-script">' . $contents . '</script>';
                        break;

                    case 'style':
                        $output .= '<style {csp-style-nonce} class="kint-rich-style">' . $contents . '</style>';
                        break;

                    default:
                        $output .= $contents;
                }
            }

            // Don't pre-render on every dump
            if (! $this->force_pre_render) {
                self::$needs_pre_render = false;
            }
        }

        $output .= '<div class="kint-rich';

        if ($this->use_folder) {
            $output .= ' kint-file';

            if (self::$needs_folder_render || $this->force_pre_render) {
                $output = $this->renderFolder() . $output;

                if (! $this->force_pre_render) {
                    self::$needs_folder_render = false;
                }
            }
        }

        $output .= '">';

        return $output;
    }
}
