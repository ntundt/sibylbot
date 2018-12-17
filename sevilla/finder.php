<?php

class Finder
{
    public $list;

    function __construct($list)
    {
        $this->list = $list;
    }

    function find_elem($needed_value)
    {
        for ($i = 0; $i < count($this->list); $i++) {
            if ($this->list[$i] == $needed_value) {
                return $i;
            }
        }
        return -1;
    }

    function find($parameter, $needed_value)
    {
        for ($i = 0; $i < count($this->list); $i++) {
            if ($this->list[$i][$parameter] == $needed_value) {
                return $this->list[$i];
            }
        }
        return -1;
    }

    function find_str($parameter, $needed_value, $tolower = false)
    {
        for ($i = 0; $i < count($this->list); $i++) {
            if (strcmp(($tolower ? mb_strtolower($this->list[$i][$parameter]) : $this->list[$i][$parameter]), $needed_value) == 0) {
                return $this->list[$i];
            }
        }
        return -1;
    }

    function find_in_keys_str($parameter, $needed_value, $tolower = false)
    {
        for ($i = 0; $i < count($this->list[$parameter]); $i++) {
            if (strcmp(($tolower ? mb_strtolower($this->list[$parameter][$i]) : $this->list[$parameter][$i]), $needed_value) == 0) {
                $ret = array();
                $keys = array_keys($this->list);
                for ($j = 0; $j < count($keys); $j++) {
                    $ret[$keys[$j]] = $this->list[$keys[$j]][$i];
                }
                return $ret;
            }
        }
        return -1;
    }

    function find_in_keys($parameter, $needed_value, $tolower = false)
    {
        for ($i = 0; $i < count($this->list[$parameter]); $i++) {
            if ($this->list[$parameter][$i] == $needed_value) {
                $ret = array();
                $keys = array_keys($this->list);
                for ($j = 0; $j < count($keys); $j++) {
                    $ret[$keys[$j]] = $this->list[$keys[$j]][$i];
                }
                return $ret;
            }
        }
        return -1;
    }

}