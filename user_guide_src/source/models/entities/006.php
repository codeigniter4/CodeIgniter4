<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

class User extends Entity
{
    public function setPassword(string $pass)
    {
        $this->attributes['password'] = password_hash($pass, PASSWORD_BCRYPT);

        return $this;
    }

    public function setCreatedAt(string $dateString)
    {
        $this->attributes['created_at'] = new Time($dateString, 'UTC');

        return $this;
    }

    public function getCreatedAt(string $format = 'Y-m-d H:i:s')
    {
        // Convert to CodeIgniter\I18n\Time object
        $this->attributes['created_at'] = $this->mutateDate($this->attributes['created_at']);

        $timezone = $this->timezone ?? app_timezone();

        $this->attributes['created_at']->setTimezone($timezone);

        return $this->attributes['created_at']->format($format);
    }
}
