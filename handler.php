<?php

    setlocale(LC_ALL, 'ru_RU');

    if (!isset($_SERVER)) {
        return;
    }
    
    define('MAIN_CHAT', 1); //defult peer_id, if peer_id wasn't defined automatically
    //define('ADMIN_CHAT', '');
    define('DATABASE_LOGIN', 'your-database-login');
    define('DATABASE_PASSWORD', 'your-database-password');
    define('DATABASE_HOST', 'localhost'); //your database password
    define('CONFIRMATION_TOKEN', '0000000'); //community confirmation token
    define('ACCESS_TOKEN', 'replace-me-with-real-access-token');
    
    $echok = true;
    $input = json_decode(file_get_contents('php://input'), true);
    
    date_default_timezone_set('Europe/Minsk'); //timezone used by default
    
    switch ($input['type']) {
        case 'confirmation':
            echo CONFIRMATION_TOKEN;
            $echok = false;
            break;
        case 'message_new':
            require_once 'sevilla/finder.php';
            $processed_ids = json_decode(file_get_contents('processed_ids.json'), true);
            $finder = new Finder($processed_ids);
            if($finder->find_elem($input['object']['date']) === -1) {
                $message = $input['object']['text'];
                $fword = explode(' ', mb_strtolower(str_replace(',', '', $message), 'UTF-8'));
                switch($fword[0]) {
                    case 'севилла':
                    case '[club173076069|севилла]':
                    case '[club173076069|*club173076069]':
                    case '[club173076069|*sevilcounter]':
                    case '[club173076069|@club173076069]':
                    case '[club173076069|@sevilcounter]':
                    case '*sevilcounter':
                    case '@sevilcounter':
                    case '*club173076069':
                    case '@club173076069':
                    case 'отклонить':
                    case 'одобрить':
                    case 'пропустить':
                        require_once 'sevilla/BotInterface.php';
                        $inf = new BotInterface($input['object']);
                        $inf->perform();
                        break;
                }
                require_once 'sevilla/wordfilter.php';
                $wfp = new WordFilterProcessing();
                if($wfp->isBlacklisted($input['object']['text'])) {
                    require_once 'sevilla/BotInterface.php';
                    $inf = new BotInterface($input['object']);
                    $inf->cmd_type = 11;
                    $inf->object_id = $input['object']['from_id'];
                    $inf->perform();
                }
                for($i = 0; $i < count($processed_ids) - 1; $i++) {
                    $processed_ids[$i] = $processed_ids[$i + 1];
                }
                $processed_ids[count($processed_ids) - 1] = $input['object']['date'];
                file_put_contents('processed_ids.json', json_encode($processed_ids));
            }
            break;
    }
    
    if ($echok) {
        echo 'ok';
    }

?>