<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Validation\Validator;

abstract class StatisticAbstract
{

    protected $db;
    protected $validator;
    
    public function __construct(Connection $db, Validator $validator) {
        $this->db = $db;
        $this->validator = $validator;
    }
    
    abstract public function getData() : array;
}
