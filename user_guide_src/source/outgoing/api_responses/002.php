<?php

// Generic response method
$this->respond($data, 200);

// Generic failure response
$this->fail($errors, 400);

// Item created response
$this->respondCreated($data);

// Item successfully deleted
$this->respondDeleted($data);

// Command executed by no response required
$this->respondNoContent($message);

// Client isn't authorized
$this->failUnauthorized($description);

// Forbidden action
$this->failForbidden($description);

// Resource Not Found
$this->failNotFound($description);

// Data did not validate
$this->failValidationError($description);

// Resource already exists
$this->failResourceExists($description);

// Resource previously deleted
$this->failResourceGone($description);

// Client made too many requests
$this->failTooManyRequests($description);
