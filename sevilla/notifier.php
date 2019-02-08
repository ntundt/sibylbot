<?php

class Notifier
{
    public $request;
    function __construct($msg_id = -1, $peer = MAIN_CHAT)
    {
        require_once 'request.php';
        $this->request = new Request(ACCESS_TOKEN);
        $this->request->setMethod('messages.send');
        switch ($peer) {
            case 'main_chat':
                $this->request->addParameter('peer_id', MAIN_CHAT);
                break;
            default:
                $this->request->addParameter('peer_id', $peer);
                break;
        }
        if ($msg_id !== -1) {
            $this->request->addParameter('forward_messages', $msg_id);
        }
    }

    function notify($text, $params = [])
    {
        $this->request->addParameter('message', $text);
        $keys = array_keys($params);
        if (count(array_keys($params)) > 0) {
            for ($i = 0; $i < count($keys); $i++) {
                $this->request->addParameter($keys[$i], $params[$keys[$i]]);
            }
        }
        $this->request->perform();
        if (!$this->request->errno) {
            require_once 'log.php';
            new Log('There is an error: ' . json_encode($this->request->error));
        }
    }

}