<?php namespace CodeIgniter\API;

interface FormatterInterface
{
    /**
     * Takes the given data and formats it.
     *
     * @param $data
     *
     * @return mixed
     */
    public function format(array $data);
}
