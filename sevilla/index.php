<?php
require_once 'WebInterface.php';
require_once '../config.php';

$wi = new WebInterface($_POST);
if (isset($_POST['command'])) {
    $wi->perform();

    if (!$wi->errno) {
        $error = array(
            'type' => $wi->error['type'],
            'obj' => isset($wi->error['vkobject']) ? $wi->error['vkobject'] : $wi->error['sqlerror']
        );
    }
}

include 'markup/webinterface.phtml';