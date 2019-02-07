<?php

require_once 'config.php';

if (!isset($_SERVER)) {
    return;
}

$data = file_get_contents('php://input');
$input = json_decode($data, true);

switch ($input['type']) {
    case 'confirmation':
        exit(CONFIRMATION_TOKEN);
        break;
    case 'message_new':
        require_once 'sevilla/BotInterface.php';
        $bot = new BotInterface($input['object']);
        $bot->ok();
        switch ($bot->command[0]) {
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
                $bot->cmd_type = 'CMD_NOT_FOUND';
                $bot->handlerCommand();
                $bot->perform();
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
        break;
}