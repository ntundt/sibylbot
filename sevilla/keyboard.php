<?php

class BotKeyboardBuilder
{
    public $keyboard;

    function __construct($one_time = true)
    {
        $this->keyboard = array('one_time' => $one_time, 'buttons' => array());
    }

    function addKey($text, $payload, $line, $color = 'default')
    {
        $this->keyboard['buttons'][$line][] = array(
            'action' => array(
                'type' => 'text',
                'payload' => $payload,
                'label' => $text
            ),
            'color' => $color
        );
    }

    function getKeyboardJSON()
    {
        return json_encode($this->keyboard);
    }
}
