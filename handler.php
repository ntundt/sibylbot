<?php
require_once 'config.php';
if (!isset($_SERVER)) {
    return;
}
$input = json_decode(file_get_contents('php://input'), true);
switch ($input['type']) {
    case 'confirmation':
        echo CONFIRMATION_TOKEN;
        break;
    case 'message_new':
        echo 'ok';
        require_once 'sevilla/finder.php';
        $processed_ids = json_decode(file_get_contents('processed_ids.json'), true);
        $finder = new Finder($processed_ids);
        if ($finder->find_elem($input['object']['date']) === -1) {
            $message = $input['object']['text'];
            $fword = explode(' ', mb_strtolower(str_replace(',', '', $message), 'UTF-8'));
            switch ($fword[0]) {
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
            if ($wfp->isBlacklisted($input['object']['text'])) {
                require_once 'sevilla/BotInterface.php';
                $inf = new BotInterface($input['object']);
                $inf->cmd_type = 11;
                $inf->object_id = $input['object']['from_id'];
                $inf->perform();
            }
            for ($i = 0; $i < count($processed_ids) - 1; $i++) {
                $processed_ids[$i] = $processed_ids[$i + 1];
            }
            $processed_ids[count($processed_ids) - 1] = $input['object']['date'];
            file_put_contents('processed_ids.json', json_encode($processed_ids));
        }
        break;
}