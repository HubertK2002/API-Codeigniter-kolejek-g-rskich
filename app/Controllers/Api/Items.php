<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class Items extends ResourceController
{
	public function create()
	{
		$data = $this->request->getPost();
		return $this->respond(['method' => 'POST', 'data' => $data]);
	}

	public function update($id = null)
	{
		$data = $this->request->getRawInput();
		return $this->respond(['method' => 'PUT', 'id' => $id, 'data' => $data]);
	}

	public function delete($id = null)
	{
		return $this->respond(['method' => 'DELETE', 'id' => $id]);
	}
}

