<?php namespace CodeIgniter\Config;

class Mail extends BaseConfig
{
    public function factory(string $group = 'default', $config)
    {
        // Ensure we have an class for this alias.
        if (! isset($config->groups[$group]) || ! is_array($config->groups[$group]))
        {
            throw new \InvalidArgumentException(sprintf(lang('mail.invalidGroup'), $group));
        }

        // Ensure we have a valid Handler class
        $handler = $config->groups[$group]['handler'] ?? null;
        if (empty($handler))
        {
            throw new \BadMethodCallException(sprintf(lang('mail.invalidHandlerName'), $handler));
        }

        $handler = $config->availableHandlers[$handler];

        // Make sure we pass the group config settings into the handler here.
        $handler = new $handler($config->groups[$group]);

        return $handler;
    }

}
