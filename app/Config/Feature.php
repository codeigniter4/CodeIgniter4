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
     * Use lowercase HTTP method names like "get", "post" in Config\Filters::$methods.
     *
     * But the HTTP method is case-sensitive. So using lowercase is wrong.
     * We should disable this and use uppercase names like "GET", "POST", etc.
     *
     * The method token is case-sensitive because it might be used as a gateway
     * to object-based systems with case-sensitive method names. By convention,
     * standardized methods are defined in all-uppercase US-ASCII letters.
     * https://www.rfc-editor.org/rfc/rfc9110#name-overview
     */
    public bool $lowerCaseFilterMethods = true;
}
