<?php namespace CodeIgniter\Config;

class Mail extends BaseConfig
{
    public function factory(string $group = 'default', $config)
    {
        // Ensure we have an class for this alias.
        if (! isset($config->$group) || ! is_array($config->$group))
        {
            throw new \InvalidArgumentException(sprintf(lang('mail.invalidGroup'), $group));
        }

        // Ensure we have a valid Handler class
        $handler = $config->handlers[$group] ?? null;
        if (empty($handler))
        {
            throw new \BadMethodCallException(sprintf(lang('mail.invalidHandlerName', $handler)));
        }

        $handler = new $handler();
    }

}
