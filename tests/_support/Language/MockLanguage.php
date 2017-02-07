<?php namespace CodeIgniter\Language;

class MockLanguage extends Language
{
	/**
	 * Stores the data that should be
	 * returned by the 'requireFile()' method.
	 *
	 * @var mixed
	 */
	protected $data;

	//--------------------------------------------------------------------

	/**
	 * Sets the data that should be returned by the
	 * 'requireFile()' method to allow easy overrides
	 * during testing.
	 *
	 * @param $data
	 *
	 * @return $this
	 */
	public function setData($data)
	{
	    $this->data = $data;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides an override that allows us to set custom
	 * data to be returned easily during testing.
	 *
	 * @param string $path
	 *
	 * @return array|mixed
	 */
	protected function requireFile(string $path): array
	{
	    return $this->data ?? [];
	}

	//--------------------------------------------------------------------

}
