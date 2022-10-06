<?php

class User
{
    public $id;
    public $email;
    public $username;

    protected $last_login;

    public function lastLogin($format)
    {
        return $this->lastLogin->format($format);
    }

    public function __set($name, $value)
    {
        if ($name === 'lastLogin') {
            $this->lastLogin = DateTime::createFromFormat('!U', $value);
        }
    }

    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }
}
