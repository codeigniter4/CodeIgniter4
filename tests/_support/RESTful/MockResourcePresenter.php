<?php
namespace Tests\Support\RESTful;

use CodeIgniter\RESTful\ResourcePresenter;

class MockResourcePresenter extends ResourcePresenter
{

	public function getModel()
	{
		return $this->model;
	}

	public function getModelName()
	{
		return $this->modelName;
	}

	public function getFormat()
	{
		return $this->format;
	}

}
