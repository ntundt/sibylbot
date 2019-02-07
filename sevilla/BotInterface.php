<?php

class BotInterface
{
    public $object;
    public $command;
    public $object_id;
    public $use_You;
    public $comment;
    public $payload;
    public $multi;
    public $multifactor;
    public $balance;
    public $procedure;
    public $request_id;
    public $cmd_type;
    private $from_id;

    function __construct($object)
    {
        // Объект сообщения из ВКонтакте
        $this->object = $object;
        //id отправителя
        $this->from_id = $object['from_id'];
        // Комментарий к дейcтвию
        $this->comment = '';
        // Будет ли бот использовать местомение "Вы" при обращении к пользователю
        $this->use_You = false;
        // Несколько раз по одному и тому же пункту
        $this->multi = false;
        $this->request_id = 0;
        //Удаление лишних пробелов, приведение строки к нижнему регистру, разделение по словам
        $this->command = $this->strHandler($object['text'] ?? '');

        if (isset($this->object['payload'])) {
            $this->payload = json_decode($this->object['payload'], true);
        }

        if (isset($this->payload['request_id'])) {
            $this->request_id = $this->payload['request_id'];
            switch ($this->payload['action']) {
                case 'verify':
                    $this->cmd_type = CMD_REQUEST_VERIFY;
                    break;
                case 'reject':
                    $this->cmd_type = CMD_REQUEST_REJECT;
                    break;
                case 'next':
                    $this->cmd_type = CMD_REQUEST_NEXT;
                    break;
                default:
                    $this->cmd_type = CMD_NOT_FOUND;
                    break;
            }
        }
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

    /**
     * Поиск совпадений с одной из команд
     */
    public function handlerCommand()
    {
        for ($i = 0; $i < count($this->command); $i++) {
            switch ($this->command[$i]) {
                case 'у':
                case 'баллов':
                case 'севилла':
                case 'сгенерируй':
                case 'дай':
                case 'с':
                case 'балансом':
                case '':
                    break;
                case 'мне': // Местоимения, указывающие на самого пользователя
                case 'меня':
                case 'моего':
                case 'моим':
                    $this->object_id = $this->from_id;
                    break;
                case 'пункт': // После слова "пункт" обычно следует номер самого пункта
                case 'пункту':
                    $this->procedure = intval($this->command[$i + 1]);
                    $this->cmd_type = CMD_BALANCE_CHANGE_BY_PARAGRAPH;
                    break;
                case 'по': // После слова "по" обычно следует слово "пункт"
                    if (strcmp($this->command[$i + 1], 'пункту') == 0) {
                        $this->procedure = intval($this->command[$i + 2]);
                    }
                    break;
                case 'сколько': // Пользователь хочет узнать, сколько чего-нибудь у кого-нибудь
                    $this->cmd_type = CMD_BALANCE_SHOW;
                    if (strcmp($this->command[$i + 1], 'баллов') == 0) {
                        if (strcmp($this->command[$i + 2], 'у') == 0) {
                            $this->object_id = $this->user_find($this->command[$i + 3]);
                        }
                    } else if (strcmp($this->command[$i + 1], 'у') == 0) {
                        if (strcmp($this->command[$i + 3], 'баллов') == 0) {
                            if (strcmp($this->command[$i + 2], 'меня') == 0) {
                                $this->object_id = $this->from_id;
                                $this->use_You = true;
                            } else {
                                $this->object_id = $this->user_find($this->command[$i + 2]);
                            }
                        }
                    }
                    break;
                // Пользователь хочет созерцать список операций с его балансом
                case 'выписка':
                case 'выписку':
                case 'операции':
                    $this->cmd_type = CMD_GENERATE_EXTRACTION;
                    $this->object_id = $this->object['from_id'];
                    $this->use_You = true;
                    break;
                case 'добавь':
                case 'добавить':
                    $this->object_id = $this->command[$i + 1];
                    $this->cmd_type = CMD_NEW_MEMBER;
                    break;
                case 'отчёт':
                    $this->object_id = $this->object['from_id'];
                    $this->cmd_type = CMD_REQUEST_ACCEPT;
                    break;
                case 'отчёты':
                    $this->cmd_type = CMD_REQUEST_NEXT;
                    break;
                case 'одобрить':
                    if (!isset($this->payload['request_id'])) {
                        $this->cmd_type = CMD_REQUEST_VERIFY;
                        if (strcmp(preg_split('//u', $this->command[$i + 1], null, PREG_SPLIT_NO_EMPTY)[0], '#') == 0) {
                            $cmd = preg_replace("/[^0-9]/", '', $this->command[$i + 1]);
                            if (intval($cmd) != 0) {
                                $this->request_id = intval($cmd);
                            }
                        }
                    }
                    break;
                case 'отклонить':
                    if (!isset($this->payload['request_id'])) {
                        $this->cmd_type = CMD_REQUEST_REJECT;
                        if (strcmp(preg_split('//u', $this->command[$i + 1], null, PREG_SPLIT_NO_EMPTY)[0], '#') == 0) {
                            $cmd = preg_replace("/[^0-9]/", '', $this->command[$i + 1]);
                            if (intval($cmd) != 0) {
                                $this->request_id = intval($cmd);
                            }
                        }
                    }
                    break;
                //Если такого слова не нашлось, смотрим, является ли оно числовым, есть ли оно в списке имён и фамилий и не ассоциируется ли оно с каким-либо другим действием
                default:
                    if ((strcmp(preg_split('//u', $this->command[$i], null, PREG_SPLIT_NO_EMPTY)[0], 'x') == 0) or (strcmp(preg_split('//u', $this->command[$i], null, PREG_SPLIT_NO_EMPTY)[0], 'х') == 0)) {
                        $cmd = preg_replace("/[^0-9]/", '', $this->command[$i]);
                        if (intval($cmd) != 0) {
                            $this->multi = true;
                            $this->multifactor = intval($cmd);
                        }
                    } else if (is_numeric($this->command[$i]) and ((strcmp(preg_split('//u', $this->command[$i], null, PREG_SPLIT_NO_EMPTY)[0], '+') == 0) or (strcmp(preg_split('//u', $this->command[$i], null, PREG_SPLIT_NO_EMPTY)[0], '-') == 0))) {
                        $this->balance = intval($this->command[$i]);
                        $this->cmd_type = CMD_BALANCE_CHANGE_BY_ARBITRARY_VALUE;
                        $this->procedure = 0;
                    } else {
                        $mb_user = $this->user_find($this->command[$i]);
                        if (isset($mb_user['user_id'][0])) {
                            $this->object_id = $mb_user['user_id'][0];
                        } else {
                            $items = json_decode(file_get_contents('sevilla/procedures.json'), true);
                            foreach ($items as $key => $elem) {
                                foreach ($elem['associations'] as $str) {
                                    if (strcmp($str, $this->command[$i]) == 0) {
                                        $this->procedure = $key + 1;
                                        $this->cmd_type = CMD_BALANCE_CHANGE_BY_PARAGRAPH;
                                    }
                                }
                            }
                        }
                    }
            }
        }

    }

    private function json_fix_cyr($json_str)
    {
        $cyr_chars = array(
            '\u0430' => 'а', '\u0410' => 'А',
            '\u0431' => 'б', '\u0411' => 'Б',
            '\u0432' => 'в', '\u0412' => 'В',
            '\u0433' => 'г', '\u0413' => 'Г',
            '\u0434' => 'д', '\u0414' => 'Д',
            '\u0435' => 'е', '\u0415' => 'Е',
            '\u0451' => 'ё', '\u0401' => 'Ё',
            '\u0436' => 'ж', '\u0416' => 'Ж',
            '\u0437' => 'з', '\u0417' => 'З',
            '\u0438' => 'и', '\u0418' => 'И',
            '\u0439' => 'й', '\u0419' => 'Й',
            '\u043a' => 'к', '\u041a' => 'К',
            '\u043b' => 'л', '\u041b' => 'Л',
            '\u043c' => 'м', '\u041c' => 'М',
            '\u043d' => 'н', '\u041d' => 'Н',
            '\u043e' => 'о', '\u041e' => 'О',
            '\u043f' => 'п', '\u041f' => 'П',
            '\u0440' => 'р', '\u0420' => 'Р',
            '\u0441' => 'с', '\u0421' => 'С',
            '\u0442' => 'т', '\u0422' => 'Т',
            '\u0443' => 'у', '\u0423' => 'У',
            '\u0444' => 'ф', '\u0424' => 'Ф',
            '\u0445' => 'х', '\u0425' => 'Х',
            '\u0446' => 'ц', '\u0426' => 'Ц',
            '\u0447' => 'ч', '\u0427' => 'Ч',
            '\u0448' => 'ш', '\u0428' => 'Ш',
            '\u0449' => 'щ', '\u0429' => 'Щ',
            '\u044a' => 'ъ', '\u042a' => 'Ъ',
            '\u044b' => 'ы', '\u042b' => 'Ы',
            '\u044c' => 'ь', '\u042c' => 'Ь',
            '\u044d' => 'э', '\u042d' => 'Э',
            '\u044e' => 'ю', '\u042e' => 'Ю',
            '\u044f' => 'я', '\u042f' => 'Я',

            '\r' => '',
            '\n' => '<br />',
            '\t' => ''
        );

        foreach ($cyr_chars as $cyr_char_key => $cyr_char) {
            $json_str = str_replace($cyr_char_key, $cyr_char, $json_str);
        }
        return $json_str;
    }

    private function image_local_save($url)
    {
        $vars = json_decode(file_get_contents('vars.json'), true);
        $vars['img_id']++;

        $ch = curl_init($url);
        $fp = fopen('images/' . $vars['img_id'] . '.png', 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        file_put_contents('vars.json', json_encode($vars));
        return $vars['img_id'] . '.png';
    }

    //private function image_upload($file_name, $url)
    //{
    //    $curl = curl_init($url);
    //    curl_setopt($curl, CURLOPT_POST, true);
    //    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //    curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CURLfile($file_name)));
    //    $json = curl_exec($curl);
    //    curl_close($curl);
    //    return json_decode($json, true);
    //}

    /**
     * Проверка в БД, является ли пользователь администратором
     * @return bool
     */
    private function user_is_admin()
    {
        require_once 'polling.php';
        $poll = new Poll();
        return $poll->get(
                'members',
                'admin',
                'WHERE `user_id` = ' . $this->object['from_id']
            )
            ['admin'][0] ?? '' == 1 ? true : false;
    }

    private function user_find($idword)
    { // Найти пользователя в БД
        require_once 'polling.php'; // Импортируем класс для запросов к БД
        $db = new Poll();
        $comparation_queue = array('last_name', 'lname_gen', 'lname_acc', 'lname_dat', 'first_name', 'fname_gen', 'fname_acc', 'fname_dat', 'user_id'); // Столбцы, проходясь по которым, ищем совпадения с неизвестным словом
        for ($i = 0; $i < count($comparation_queue); $i++) {
            $users = $db->get('members', 'user_id', 'WHERE ' . $comparation_queue[$i] . ' = \'' . $idword . '\''); // Конечно, такая конструкция может вернуть несколько пользователей, но мы надеемся, что таковой будет один
            if (isset($users['user_id'][0])) {
                return $users;
            }
        }
    }

    /**
     * Обработка команды
     */
    function perform()
    { // Обрабатываем команду
        require_once 'performer.php';
        require_once 'finder.php';
        require_once 'polling.php';
        require_once 'notifier.php';
        require_once 'keyboard.php';

        // Этот перформанс ответственен за изменение базы данных в каком-то месте
        $performance = new Performer();

        // Уведомление пользователя
        $notification = new Notifier(
            $this->object['id'],
            (isset($this->object['peer_id']) ? $this->object['peer_id'] : $this->object['from_id']));

        // Опрос базы данных
        $poll = new Poll();

        // Смотрим на тип команды
        switch ($this->cmd_type) {
            case CMD_BALANCE_CHANGE_BY_PARAGRAPH:
                if ($this->user_is_admin()) {
                    // Если указанный в команде пользователь был найден в БД
                    if (isset($this->object_id)) {
                        $procedures = json_decode(file_get_contents('sevilla/procedures.json'), true); // Открываем список пунктов и переводим его из JSON в обычный ассоциативный массив
                        $finder = new Finder($procedures);
                        // Ищем пункт с таким-то id
                        $proc = $finder->find('procedure_id', $this->procedure);
                        if (!$proc['excluded']) {
                            $data = $poll->get('members', 'user_id, balance, fname_gen', 'WHERE user_id = ' . $this->object_id); // Получаем текущий счёт и имя пользователя в родительном падеже
                            $current_balance = $data['balance'][0]; // Текущий баланс
                            // Заменяем значение столбца balance там, где user_id равен object_id
                            $performance->replace(
                                'members',
                                'balance',
                                $current_balance + ($this->multi ? $proc['balance'] * $this->multifactor : $proc['balance']),
                                'user_id = ' . $this->object_id
                            );
                            // Таймстамп
                            $timestamp = time();
                            $final = $current_balance + ($this->multi ? $proc['balance'] * $this->multifactor : $proc['balance']); // Новый баланс
                            $result = ($this->multi ? $proc['balance'] * $this->multifactor : $proc['balance']);
                            // Добавляем в архив операций соответствующую запись
                            $performance->append(
                                'operations',
                                "DEFAULT, {$this->procedure}, {$timestamp}, {$this->object['from_id']}, {$this->object_id}, '{$this->comment}', {$result}, '{$proc['description']}', {$final}");

                            $notification->notify('Поняла. ' . ($proc['balance'] < 0 ? '' : '+') . $proc['balance'] . ' баллов ' . ($this->multi ? $this->multifactor . ' раз ' : '') . '(' . $proc['description_ru'] . '). Теперь количество баллов у [id' . $data['user_id'][0] . '|' . ($this->use_You ? 'Вас' : $data['fname_gen'][0]) . '] на счету составляет ' . ($current_balance + ($this->multi ? $proc['balance'] * $this->multifactor : $proc['balance'])) . '.'); // Уведомляем пользователя
                        } else {
                            $notification->notify('Этот пункт был исключён. Действия по нему запрашивать больше нельзя.');
                        }
                    } // Если пользователь не указан или не найден
                    else {
                        $notification->notify('Вы либо не указали пользователя, либо указали пользователя, которого нет у меня в базе.');
                    }
                } // Если горе-взломщик админом не явялется
                else {
                    $notification->notify('У Вас нет прав администратора.');
                }
                break;
            case CMD_BALANCE_CHANGE_BY_ARBITRARY_VALUE: // Практически тоже самое, что и при cmd_type == 0, но на произвольное количество баллов
                if ($this->user_is_admin()) {
                    if (isset($this->object_id)) {
                        $data = $poll->get('members', 'user_id, balance, fname_gen', 'WHERE user_id = ' . $this->object_id);
                        $current_balance = $data['balance'][0];
                        $performance->replace(
                            'members',
                            'balance',
                            $current_balance + $this->balance,
                            'user_id = ' . $this->object_id
                        );

                        $timestamp = time();
                        $final = $current_balance + $this->balance;
                        $performance->append('operations', "DEFAULT, {$this->procedure}, {$timestamp}, {$this->object['from_id']}, {$this->object_id}, '{$this->comment}', {$this->balance}, '{$this->comment}', {$final}");

                        $notification->notify('Поняла. ' . ($this->balance < 0 ? '' : '+') . $this->balance . ' баллов. Теперь количество баллов у [id' . $data['user_id'][0] . '|' . ($this->use_You ? 'Вас' : $data['fname_gen'][0]) . '] на счету составляет ' . ($current_balance + $this->balance) . '.');
                    } else {
                        $notification->notify('Вы либо не указали пользователя, либо указали пользователя, которого нет у меня в базе.');
                    }
                } else {
                    $notification->notify('У Вас нет прав администратора.');
                }
                break;
            case CMD_BALANCE_SHOW:
                $data = $poll->get('members', 'user_id, balance, fname_gen', 'WHERE user_id = ' . $this->object_id); // Получаем из БД участника с таким-то id
                $notification->notify('У [id' . $data['user_id'][0] . '|' . ($this->use_You ? 'Вас' : $data['fname_gen'][0]) . '] сейчас ' . $data['balance'][0] . ' баллов.'); // Уведомляем пользователя
                break;
            case CMD_GENERATE_EXTRACTION:
                $data = $poll->get('operations', 'id, type, time, executor, object, comment, result, description, new_balance', 'WHERE object = ' . $this->object_id); // Получаем из БД операции, у которых столбец object_id равен id пользователя
                $filename = date('Y-m-d_H-i-s') . '_' . $this->object_id . '.html'; // Имя будущего файла

                $content = '<html lang="en">
                    <head>
                    <title></title>
              
                        <style>
                        td {
                            border: 1px solid black
                        }
                        </style>
                    </head>
                    <body><table class="wide">
                        <thead>
                            <tr><td>Id</td><td>Тип</td><td>Дата, время (МСК)</td><td>Исполнитель</td><td>Объект</td><td>Комментарий</td><td>Результат</td><td>Описание</td><td>Новый баланс</td></tr>
                        </thead>
                        <tbody>';
                $keys = array_keys($data);
                for ($i = 0; $i < count($data['id']); $i++) { //Компонуем это в HTML-таблицу
                    $content .= '<tr>';
                    for ($j = 0; $j < count($keys); $j++) {
                        switch ($keys[$j]) {
                            case 'time':
                                $content .= '<td>' . date('d.m.Y H:i:s', $data['time'][$i]) . '</td>';
                                break;
                            case 'object':
                            case 'executor':
                                $content .= '<td><a href="https://vk.com/id' . $data[$keys[$j]][$i] . '">' . $data[$keys[$j]][$i] . '</a></td>';
                                break;
                            default:
                                $content .= '<td>' . $data[$keys[$j]][$i] . '</td>';
                                break;
                        }
                    }
                    $content .= '</tr>';
                }
                $content .= '</tbody></table></body></html>';

                $file = fopen('datasheets/' . $filename, 'w');
                fwrite($file, $content);
                fclose($file);

                $notification->notify('Выписка с операциями: https://www.redcomm.tk/datasheets/' . $filename); // Уведомляем пользователя
                break;
            case CMD_NEW_MEMBER:
                if ($this->user_is_admin()) {
                    $code = "var user_id = {$this->object_id};\n" .
                        'API.messages.send({"message": "Сделано. Наверное.", "peer_id": ' . $this->object['peer_id'] . '});' . "\n" .
                        'return {
                        "gen": API.users.get({"user_id": user_id, "name_case": "gen"})[0],
                        "acc": API.users.get({"user_id": user_id, "name_case": "acc"})[0],
                        "dat": API.users.get({"user_id": user_id, "name_case": "dat"})[0],
                        "nom": API.users.get({"user_id": user_id, "name_case": "nom"})[0]
                    };';
                    $request = new Request(ACCESS_TOKEN);
                    $request->setMethod('execute');
                    $request->addParameter('code', $code);
                    $request->perform();
                    $response = json_decode($request->responsestr, true)['response'];

                    $performance->append('members', "
                        {$this->object_id}, 
                        0, 
                        0, 
                        '{$response['nom']['first_name']}', 
                        '{$response['nom']['last_name']}', 
                        DEFAULT, 
                        '{$response['gen']['first_name']}',
                        '{$response['gen']['last_name']}',
                        '{$response['dat']['first_name']}',
                        '{$response['dat']['last_name']}',
                        '{$response['acc']['first_name']}',
                        '{$response['acc']['last_name']}'");
                } else {
                    $notification->notify('У Вас нет прав администратора.');
                }
                break;
            case CMD_REQUEST_ACCEPT:
                if ($this->object['from_id'] != $this->object['peer_id']) {
                    $ids = '';
                    for ($i = 0; $i < count($this->object['attachments']); $i++) {
                        $filepath = $this->image_local_save(array_pop($this->object['attachments'][$i]['photo']['sizes'])['url']);
                        $ids .= $filepath . (isset($this->object['attachments'][$i + 1]['photo']) ? ';' : '');
                    }
                    $performance->append('requests', "DEFAULT, {$this->object['id']}, DEFAULT, 0, {$this->object_id}, '{$ids}'");
                } else {
                    $performance->append('requests', "DEFAULT, {$this->object['id']}, DEFAULT, 0, {$this->object_id}, ''");
                }
                $notification->notify('Добавила Ваш отчёт в базу. Его обработают в ближайшее время.');
                break;
            case CMD_REQUEST_VERIFY:
                if ($this->user_is_admin()) {
                    $finder = new Finder(json_decode(file_get_contents('sevilla/procedures.json'), true));
                    $item = $finder->find('procedure_id', 4);
                    $timestamp = time();

                    $request = $poll->get('requests', 'request_id, from_id', 'WHERE request_id = ' . $this->request_id);

                    $current = $poll->get('members', 'balance', 'WHERE user_id = ' . $request['from_id'][0]);

                    $final = $current['balance'][0] + $item['balance'];

                    $performance->replace('members', 'balance', $final, 'user_id = ' . $request['from_id'][0]);
                    $performance->append('operations',
                        "DEFAULT, 
                            4, 
                            {$timestamp}, 
                            {$this->object['from_id']}, 
                            {$request['from_id'][0]}, 
                            'Request #{$this->request_id} is verified now', 
                            {$item['balance']}, 
                            '{$item['description']}', 
                            {$final}"
                    );
                    $performance->replace('requests', 'confirmed', 1, 'request_id = ' . $request['request_id'][0]);
                    $next_request = $poll->get('requests', 'request_id, message_id, from_id, photos', 'WHERE confirmed = 0');

                    if (isset($next_request['request_id'][0])) {
                        $keyboard = new BotKeyboardBuilder();

                        $payload = array(
                            'request_id' => $next_request['request_id'][0],
                            'action' => 'verify'
                        );
                        $keyboard->addKey('Одобрить', json_encode($payload), 0, 'positive');
                        $payload['action'] = 'reject';
                        $keyboard->addKey('Отклонить', json_encode($payload), 0, 'negative');
                        $payload['action'] = 'next';
                        //$keyboard->addKey('Пропустить', json_encode($payload), 1);

                        if (strcmp($next_request['photos'][0], '') == 0) {
                            $notification->notify('Oтчёт с id: #' . $next_request['request_id'][0], array('forward_messages' => $next_request['message_id'][0], 'keyboard' => $this->json_fix_cyr($keyboard->getKeyboardJSON())));
                        } else {
                            $images = explode(';', $next_request['photos'][0]);
                            $images_str = '';

                            for ($i = 0; $i < count($images); $i++) {
                                $images_str .= 'https://www.redcomm.tk/images/' . $images[$i] . (isset($images[$i + 1]) ? "\n" : '');
                            }

                            $notification->notify('Одобряю. Следующий отчёт с id: #' . $next_request['request_id'][0] . "\n" .
                                'Просмотр картинок из чата в личной переписке пока что не поддерживается. Но Вы можете ознакомиться с необходимыми фотокарточками по этим ссылкам: ' . "\n" . $images_str,
                                array('keyboard' => $this->json_fix_cyr($keyboard->getKeyboardJSON()))
                            );
                        }
                    } else {
                        $notification->notify('Одобряю. Больше отчётов нет.');
                    }
                } else {
                    $notification->notify('У Вас нет прав администратора.');
                }
                break;
            case CMD_REQUEST_REJECT:
                if ($this->user_is_admin()) {
                    $performance->replace('requests', 'confirmed', 1, 'request_id = ' . $this->request_id);
                    $next_request = $poll->get('requests', 'request_id, message_id, from_id, photos', 'WHERE confirmed = 0');

                    if (isset($next_request['request_id'][0])) {
                        $keyboard = new BotKeyboardBuilder();

                        $payload = array(
                            'request_id' => $next_request['request_id'][0],
                            'action' => 'verify'
                        );
                        $keyboard->addKey('Одобрить', json_encode($payload), 0, 'positive');
                        $payload['action'] = 'reject';
                        $keyboard->addKey('Отклонить', json_encode($payload), 0, 'negative');
                        $payload['action'] = 'next';
                        //$keyboard->addKey('Пропустить', json_encode($payload), 1);

                        if (strcmp($next_request['photos'][0], '') == 0) {
                            $notification->notify('Oтчёт с id: #' . $next_request['request_id'][0], array('forward_messages' => $next_request['message_id'][0], 'keyboard' => $this->json_fix_cyr($keyboard->getKeyboardJSON())));
                        } else {
                            $images = explode(';', $next_request['photos'][0]);
                            $images_str = '';

                            for ($i = 0; $i < count($images); $i++) {
                                $images_str .= 'https://www.redcomm.tk/images/' . $images[$i] . (isset($images[$i + 1]) ? "\n" : '');
                            }

                            $notification->notify('Отклоняю. Следующий отчёт с id: #' . $next_request['request_id'][0] . "\n" .
                                'Просмотр картинок из чата в личной переписке пока что не поддерживается. Но Вы можете ознакомиться с необходимыми фотокарточками по этим ссылкам: ' . "\n" . $images_str,
                                array('keyboard' => $this->json_fix_cyr($keyboard->getKeyboardJSON()))
                            );
                        }
                    } else {
                        $notification->notify('Отклоняю. Больше отчётов нет.');
                    }
                } else {
                    $notification->notify('У Вас нет прав администратора.');
                }
                break;
            case CMD_REQUEST_NEXT:
                if ($this->user_is_admin()) {
                    $next_request = $poll->get('requests', 'request_id, message_id, from_id, photos', 'WHERE confirmed = 0');

                    if (isset($next_request['request_id'][0])) {
                        $keyboard = new BotKeyboardBuilder();

                        $payload = array(
                            'request_id' => $next_request['request_id'][0],
                            'action' => 'verify'
                        );
                        $keyboard->addKey('Одобрить', json_encode($payload), 0, 'positive');
                        $payload['action'] = 'reject';
                        $keyboard->addKey('Отклонить', json_encode($payload), 0, 'negative');
                        $payload['action'] = 'next';
                        //$keyboard->addKey('Пропустить', json_encode($payload), 1);

                        if (strcmp($next_request['photos'][0], '') == 0) {
                            $notification->notify('Oтчёт с id: #' . $next_request['request_id'][0], array('forward_messages' => $next_request['message_id'][0], 'keyboard' => $this->json_fix_cyr($keyboard->getKeyboardJSON())));
                        } else {
                            $images = explode(';', $next_request['photos'][0]);
                            $images_str = '';

                            for ($i = 0; $i < count($images); $i++) {
                                $images_str .= 'https://www.redcomm.tk/images/' . $images[$i] . (isset($images[$i + 1]) ? "\n" : '');
                            }

                            $notification->notify('Oтчёт с id: #' . $next_request['request_id'][0] . "\n" .
                                'Просмотр картинок из чата в личной переписке пока что не поддерживается. Но Вы можете ознакомиться с необходимыми фотокарточками по этим ссылкам: ' . "\n" . $images_str,
                                array('keyboard' => $this->json_fix_cyr($keyboard->getKeyboardJSON()))
                            );
                        }
                    } else {
                        $notification->notify('Больше отчётов нет.');
                    }
                } else {
                    $notification->notify('У Вас нет прав администратора.');
                }
                break;
            case CMD_NOT_FOUND:
                $notification->notify("Не могу понять, чего Вы от меня хотите. Возможно, что-то из этого Вам поможет:\n\nНе знаете, как составить команду?\nhttps://vk.com/page-173076069_54615607\n\nНашли баг или просто хотите пообщаться с разработчиком?\nhttps://vk.me/id165054978");
                break;
            case PHRASE_BADWORD_FOUND:
                $finder = new Finder(json_decode(file_get_contents('sevilla/procedures.json'), true));
                $item = $finder->find('procedure_id', 6);
                $timestamp = time();


                $data = $poll->get('members', 'user_id, balance, local_id, first_name, last_name', 'WHERE user_id = ' . $this->object_id);

                if (isset($data['user_id'])) {
                    $current_balance = $data['balance'][0]; // Текущий баланс

                    $final = $current_balance + $item['balance'];

                    $performance->replace(
                        'members',
                        'balance',
                        $final,
                        'user_id = ' . $this->object_id
                    );

                    $performance->append('operations', "DEFAULT, {$item['procedure_id']}, {$timestamp}, 1, {$this->object_id}, '{$this->object['text']}', {$item['balance']}, '{$item['description']}', {$final}");

                    $name_first_letter = preg_split('//u', $data['first_name'][0], null, PREG_SPLIT_NO_EMPTY)[0];
                    $notification->notify($data['last_name'][0] . '! №' . $data['local_id'][0] . '! ' . $data['last_name'][0] . ' ' . $name_first_letter . '.! Да, [id' . $this->object['from_id'] . '|Вы]! Минус балл Вам! Выражайтесь культурно!');
                } else {
                    $notification->notify('[id' . $this->object['from_id'] . '|Вас] нет у меня в базе, но я это Ваше высказывание запомню.');
                }
                break;
        }
    }
}