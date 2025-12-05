<?php
// app/core/Model.php

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
