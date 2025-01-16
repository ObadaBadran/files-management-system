<?php

namespace App\Contracts;

interface TransactionManagerInterface{
    public function start();
    public function commit();
    public function rollback();
}

