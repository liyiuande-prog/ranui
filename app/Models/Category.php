<?php

namespace App\Models;

use Core\Model;

class Category extends Model
{
    protected $table = 'categories';

    public function getAll()
    {
        return $this->findAll();
    }
}
