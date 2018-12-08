<?php

    class Performer {
        
        function __construct() {
            $this->db = new mysqli(DATABASE_HOST, DATABASE_LOGIN, DATABASE_PASSWORD, DATABASE_LOGIN);
        }
        
        function append($table, $data) {
            return $this->db->query("INSERT INTO `{$table}` VALUES ( {$data} )");
        }
        
        function replace($table, $row, $value, $exp = -1) {
            return $this->db->query("UPDATE `{$table}` SET {$row} = {$value}" . ($exp!=-1?" WHERE {$exp}":''));
        }
        
    }

?>