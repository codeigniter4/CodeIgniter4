<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Enable/disable backward compatibility breaking features.
 */
class Feature extends BaseConfig
{
    /**
     * Use improved new auto routing instead of the default legacy version.
     */
    public bool $autoRoutesImproved = false;

    /**
     * Use filter execution order in 4.4 or before.
     */
    public bool $oldFilterOrder = false;

    /**
     * Keep the behavior of `limit(0)` in Query Builder in 4.4 or before.
     *
     * If true, `limit(0)` returns all records. (the behavior in 4.4 or before)
     * If false, `limit(0)` returns no records.
     */
    public bool $limitZeroAsAll = false;
}
