<?php namespace CodeIgniter\API;

class XMLFormatter implements FormatterInterface
{
    /**
     * Takes the given data and formats it.
     *
     * @param $data
     *
     * @return mixed
     */
    public function format(array $data)
    {
        $result = null;

        // SimpleXML is installed but default
        // but best to check, and then provide a fallback.
        if (! extension_loaded('simplexml'))
        {
            throw new \RuntimeException('The SimpleXML extension is required to format XML.');
        }

        $output = new \SimpleXMLElement("<?xml version=\"1.0\"?><response></response>");

        $this->arrayToXML($data, $output);

        return $output->asXML();
    }

    //--------------------------------------------------------------------

    /**
     * A recursive method to convert an array into a valid XML string.
     *
     * Written by CodexWorld. Received permission by email on Nov 24, 2016 to use this code.
     *
     * @see http://www.codexworld.com/convert-array-to-xml-in-php/
     *
     * @param array $data
     * @param       $output
     */
    protected function arrayToXML(array $data, &$output)
    {
        foreach ($data as $key => $value)
        {
            if (is_array($value))
            {
                if (! is_numeric($key))
                {
                    $subnode = $output->addChild("$key");
                    $this->arrayToXML($value, $subnode);
                } else
                {
                    $subnode = $output->addChild("item{$key}");
                    $this->arrayToXML($value, $subnode);
                }
            } else
            {
                $output->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    //--------------------------------------------------------------------
}
