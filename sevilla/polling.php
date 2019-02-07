<?php

class Poll
{
    private $db;

    function __construct()
    {
        $this->db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $this->db->set_charset("utf8");
    }

    function get($table, $whatToGet = '*', $somethingElse = '')
    {
        $res = $this->db->query("SELECT {$whatToGet} FROM `{$table}` {$somethingElse}");
        if ($res === false) {
            echo '<b>Oops!</b> It looks like  our database died. Try again in a hour. MySQL error: ' . $this->db->error;
        }
        if (strcmp($whatToGet, '*') != 0) {
            $whatToGet = explode(', ', $whatToGet);
        }
        $out = array();
        while ($arr = $res->fetch_assoc()) {
            for ($i = 0; $i < count($whatToGet); $i++) {
                $out[$whatToGet[$i]][] = $arr[$whatToGet[$i]];
            }
        }
        return $out;
    }

}