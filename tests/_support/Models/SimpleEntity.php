<?php namespace Tests\Support\Models;

/**
 * Class SimpleEntity
 *
 * Simple Entity-type class for testing creating and saving entities
 * in the model so we can support Entity/Repository type patterns.
 *
 * @package Tests\Support\Models
 */
class SimpleEntity
{
    protected $id;
    protected $name;
    protected $description;

    public function __get($key)
    {
        if (isset($this->$key))
        {
            return $this->$key;
        }
    }

    //--------------------------------------------------------------------

    public function __set($key, $value)
    {
        if (isset($this->$key))
        {
            $this->$key = $value;
        }
    }


}
