<?php

class Performer
{
    private $db;

    function __construct()
    {
        $this->db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $this->db->set_charset("utf8");
    }

    function append($table, $data)
    {
        return $this->db->query("INSERT INTO `{$table}` VALUES ( {$data} )");
    }

    function replace($table, $row, $value, $exp = -1)
    {
        return $this->db->query("UPDATE `{$table}` SET {$row} = {$value}" . ($exp != -1 ? " WHERE {$exp}" : ''));
    }
}
