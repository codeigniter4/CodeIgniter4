<?php

$user = $userModel->delete($id);

return $this->respondDeleted(['id' => $id]);
