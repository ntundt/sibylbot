<?php

namespace sevilla;

class Bot
{
    private $object;
    public $command;
    public $cmd_type;

    function __construct($object)
    {
        // Объект сообщения из ВКонтакте
        $this->object = $object;
        //Удаление лишних пробелов, приведение строки к нижнему регистру, разделение по словам
        $this->command = $this->strHandler($object['text'] ?? '');

    }

    /**
     * Эй, гайс, у меня всё найс!
     */
    public function ok()
    {
        ob_start();
        echo 'ok';
        $length = ob_get_length();
        // magic
        header('Connection: close');
        header("Content-Length: " . $length);
        header("Content-Encoding: none");
        header("Accept-Ranges: bytes");
        ob_end_flush();
        ob_flush();
        flush();
    }

    /**
     * Удаление лишних пробелов, приведение строки к нижнему регистру, разделение по словам
     * @param $text
     * @return array
     */
    public function strHandler($text)
    {
        // Избавляемся от знаков препинания
        $symbols = [',', '.', '?', '!', ':', ';', '(', ')', '—', '–'];
        $text = str_replace($symbols, ' ', $text);

        //Удаляем лишние пробелы
        $text = preg_replace('/\s\s+/', ' ', $text);

        //Удаляем пробелы (или другие символы) из начала и конца строки
        $text = trim($text);

        // Переводим строку в нижний регистр
        $text = mb_strtolower($text);

        //Разбиваем строку на слова
        $text = explode(' ', $text);

        return $text;
    }



}