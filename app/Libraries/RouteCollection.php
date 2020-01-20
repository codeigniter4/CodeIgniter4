<?php namespace App\Libraries;

use CodeIgniter\Config\Services;

class RouteCollection extends \CodeIgniter\Router\RouteCollection
{
    private $host = null;

    public function __construct(\CodeIgniter\Autoloader\FileLocator $locator, \Config\Modules $moduleConfig)
    {
        parent::__construct($locator, $moduleConfig);
        $this->host = Services::request()->uri->getHost();
    }

    public function getRoutes($verb = null): array
    {

        if (empty($verb))
        {
            $verb = $this->getHTTPVerb();
        }

        // Since this is the entry point for the Router,
        // take a moment to do any route discovery
        // we might need to do.
        $this->discoverRoutes();

        $routes = [];

        if (isset($this->routes[$verb]))
        {
            // Keep current verb's routes at the beginning so they're matched
            // before any of the generic, "add" routes.
            if (isset($this->routes['*']))
            {
                $extraRules = array_diff_key($this->routes['*'], $this->routes[$verb]);
                $collection = array_merge($this->routes[$verb], $extraRules);
            }
            foreach ($collection as $r)
            {
                if(
                    (empty($r['options']['subdomain']) && empty($r['options']['hostname'])) ||
                    (!empty($r['options']['subdomain']) && $r['options']['subdomain'] === $this->determineCurrentSubdomain()) ||
                    (!empty($r['options']['hostname']) && !empty($this->host) && $r['options']['hostname'] === $this->host)
                )
                {
                    $key = key($r['route']);
                    $routes[$key] = $r['route'][$key];
                }
            }
        }

        return $routes;
    }


    private function getScheme($options)
    {
        if($options['scheme'] ?? false)
            return $options['scheme'] . '://';

        return Services::request()->uri->getScheme() . '://';

    }


    private function getPort($options)
    {
        if((int)($options['port'] ?? 0)>0)
            return ':' . $options['port'];

        $port = Services::request()->uri->getPort();
        $scheme = Services::request()->uri->getScheme();

        return (null === $port || (null !== $port && $port !== Services::request()->uri->defaultPorts[$scheme])) ? ':' . $port : '';
    }


    public function reverseRoute(string $search, ...$params)
    {
        // Named routes get higher priority.

        foreach ($this->routes as $verb => $collection)
        {
            if (array_key_exists($search, $collection))
            {
                $prefix = '';
                if(!empty($collection[$search]['options']['subdomain']) && $collection[$search]['options']['subdomain'] !== ($old = $this->determineCurrentSubdomain()))
                {
                    $prefix = $this->getScheme($collection[$search]['options']) .
                        $collection[$search]['options']['subdomain'] .
                        str_replace($old, '', $this->host ?? config('App')->baseURL) .
                        $this->getPort($collection[$search]['options']);
                }
                else if(!empty($collection[$search]['options']['hostname']))
                {
                    $prefix = $this->getScheme($collection[$search]['options']) .
                        $collection[$search]['options']['hostname'] .
                        $this->getPort($collection[$search]['options']);
                }
                if(empty($prefix) && !empty($collection[$search]['options']['port']))
                {
                    $prefix = $this->getScheme($collection[$search]['options']) .
                        ($this->host ?? config('App')->baseURL).
                        $this->getPort($collection[$search]['options']);
                }


                return
                    $prefix .
                    $this->fillRouteParams(key($collection[$search]['route']), $params);
            }
        }

        // If it's not a named route, then loop over
        // all routes to find a match.
        foreach ($this->routes as $verb => $collection)
        {
            foreach ($collection as $route)
            {
                $from = key($route['route']);
                $to   = $route['route'][$from];

                // ignore closures
                if (! is_string($to))
                {
                    continue;
                }

                // Lose any namespace slash at beginning of strings
                // to ensure more consistent match.
                $to     = ltrim($to, '\\');
                $search = ltrim($search, '\\');

                // If there's any chance of a match, then it will
                // be with $search at the beginning of the $to string.
                if (strpos($to, $search) !== 0)
                {
                    continue;
                }

                // Ensure that the number of $params given here
                // matches the number of back-references in the route
                if (substr_count($to, '$') !== count($params))
                {
                    continue;
                }

                return $this->fillRouteParams($from, $params);
            }
        }

        // If we're still here, then we did not find a match.
        return false;
    }


    protected function create(string $verb, string $from, $to, array $options = null)
    {
        $overwrite = false;
        $prefix    = is_null($this->group) ? '' : $this->group . '/';

        $from = filter_var($prefix . $from, FILTER_SANITIZE_STRING);

        // While we want to add a route within a group of '/',
        // it doesn't work with matching, so remove them...
        if ($from !== '/')
        {
            $from = trim($from, '/');
        }

        $options = array_merge((array) $this->currentOptions, (array) $options);

        // Hostname limiting?
        if (($options['enabled']??false) === false && ! empty($options['hostname']))
        {
            // @todo determine if there's a way to whitelist hosts?
            if (isset($this->host) && strtolower($this->host) !== strtolower($options['hostname']))
            {
                return;
            }

            $overwrite = true;
        }

        // Limiting to subdomains?
        else if (($options['enabled']??false) === false && ! empty($options['subdomain']))
        {
            // If we don't match the current subdomain, then
            // we don't need to add the route.
            if (! $this->checkSubdomains($options['subdomain']))
            {
                return;
            }

            $overwrite = true;
        }

        // Are we offsetting the binds?
        // If so, take care of them here in one
        // fell swoop.
        if (isset($options['offset']) && is_string($to))
        {
            // Get a constant string to work with.
            $to = preg_replace('/(\$\d+)/', '$X', $to);

            for ($i = (int) $options['offset'] + 1; $i < (int) $options['offset'] + 7; $i ++)
            {
                $to = preg_replace_callback(
                    '/\$X/', function ($m) use ($i) {
                    return '$' . $i;
                }, $to, 1
                );
            }
        }

        // Replace our regex pattern placeholders with the actual thing
        // so that the Router doesn't need to know about any of this.
        foreach ($this->placeholders as $tag => $pattern)
        {
            $from = str_ireplace(':' . $tag, $pattern, $from);
        }

        // If no namespace found, add the default namespace
        if (is_string($to) && (strpos($to, '\\') === false || strpos($to, '\\') > 0))
        {
            $namespace = $options['namespace'] ?? $this->defaultNamespace;
            $to        = trim($namespace, '\\') . '\\' . $to;
        }

        // Always ensure that we escape our namespace so we're not pointing to
        // \CodeIgniter\Routes\Controller::method.
        if (is_string($to))
        {
            $to = '\\' . ltrim($to, '\\');
        }

        $name = $options['as'] ?? $from;

        // Don't overwrite any existing 'froms' so that auto-discovered routes
        // do not overwrite any app/Config/Routes settings. The app
        // routes should always be the "source of truth".
        // this works only because discovered routes are added just prior
        // to attempting to route the request.
        if (isset($this->routes[$verb][$name]) && ! $overwrite)
        {
            return;
        }

        $this->routes[$verb][$name] = [
            'route' => [$from => $to],
            'options' => $options,
        ];

        $this->routesOptions[$from] = $options;

        // Is this a redirect?
        if (isset($options['redirect']) && is_numeric($options['redirect']))
        {
            $this->routes['*'][$name]['redirect'] = $options['redirect'];
        }
    }

    private function checkSubdomains($subdomains): bool
    {
        // CLI calls can't be on subdomain.
        if (! isset($this->host))
        {
            return false;
        }

        if (is_null($this->currentSubdomain))
        {
            $this->currentSubdomain = $this->determineCurrentSubdomain();
        }

        if (! is_array($subdomains))
        {
            $subdomains = [$subdomains];
        }

        // Routes can be limited to any sub-domain. In that case, though,
        // it does require a sub-domain to be present.
        if (! empty($this->currentSubdomain) && in_array('*', $subdomains))
        {
            return true;
        }

        foreach ($subdomains as $subdomain)
        {
            if ($subdomain === $this->currentSubdomain)
            {
                return true;
            }
        }

        return false;
    }


    private function determineCurrentSubdomain()
    {
        // We have to ensure that a scheme exists
        // on the URL else parse_url will mis-interpret
        // 'host' as the 'path'.
        $url = $this->host;
        if (strpos($url, 'http') !== 0)
        {
            $url = 'http://' . $url;
        }

        $parsedUrl = parse_url($url);

        $host = explode('.', $parsedUrl['host']);

        if ($host[0] === 'www')
        {
            unset($host[0]);
        }

        // Get rid of any domains, which will be the last
        unset($host[count($host)]);

        // Account for .co.uk, .co.nz, etc. domains
        if (end($host) === 'co')
        {
            $host = array_slice($host, 0, -1);
        }

        // If we only have 1 part left, then we don't have a sub-domain.
        if (count($host) === 1)
        {
            // Set it to false so we don't make it back here again.
            return false;
        }

        return array_shift($host);
    }
}
