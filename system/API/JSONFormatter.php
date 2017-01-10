<?php namespace CodeIgniter\API;

class JSONFormatter implements FormatterInterface
{
    /**
     * The error strings to use if encoding hits an error.
     *
     * @var array
     */
    protected $errors = [
        JSON_ERROR_NONE           => 'No error has occurred',
        JSON_ERROR_DEPTH          => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX         => 'Syntax error',
        JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded',
    ];

    //--------------------------------------------------------------------

    /**
     * Takes the given data and formats it.
     *
     * @param $data
     *
     * @return mixed
     */
    public function format(array $data)
    {
        $options = ENVIRONMENT == 'production'
            ? JSON_NUMERIC_CHECK
            : JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT;

        $result = json_encode($data, 512, $options);

        // If result is NULL, then an error happened.
        // Let them know.
        if ($result === null)
        {
            throw new \RuntimeException($this->errors[json_last_error()]);
        }

        return utf8_encode($result);
    }

    //--------------------------------------------------------------------
}
