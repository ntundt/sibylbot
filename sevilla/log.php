<?php

class Log
{
    function __construct($message)
    {
        file_put_contents('Log.txt', file_get_contents('Log.txt') . "\n" . $message);
    }
}