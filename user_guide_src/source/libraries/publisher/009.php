<?php

namespace App\Publishers;

use CodeIgniter\Publisher\Publisher;

class BootstrapPublisher extends Publisher
{
    /**
     * Tell Publisher where to get the files.
     * Since we will use Composer to download
     * them we point to the "vendor" directory.
     *
     * @var string
     */
    protected $source = VENDORPATH . 'twbs/bootstrap/';

    /**
     * FCPATH is always the default destination,
     * but we may want them to go in a sub-folder
     * to keep things organized.
     *
     * @var string
     */
    protected $destination = FCPATH . 'bootstrap';

    /**
     * Use the "publish" method to indicate that this
     * class is ready to be discovered and automated.
     */
    public function publish(): bool
    {
        return $this
            // Add all the files relative to $source
            ->addPath('dist')

            // Indicate we only want the minimized versions
            ->retainPattern('*.min.*')

            // Merge-and-replace to retain the original directory structure
            ->merge(true);
    }
}
