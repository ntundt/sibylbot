<?php

class WebInterface
{

    public $errno;
    public $error;
    public $code;

    function __construct($params)
    {
        define('CMD_EXEC', 1);
        define('CMD_SQL_RUN', 2);
        define('CMD_BOTINTERFACE_REDIRECT', 3);

        $this->errno = true;
        $this->error = array(
            'type' => '',
            'code' => 100,
            'obj' => array()
        );

        $this->code = isset($params['command']) ? $params['command'] : '';
        if (isset($params['whatToDo'])) {
            switch ($params['whatToDo']) {
                case 'exec':
                    $this->do = CMD_EXEC;
                    break;
                case 'sqlr':
                    $this->do = CMD_SQL_RUN;
                    break;
            }
        }
    }

    function user_is_admin($uid)
    {
        require_once('polling.php');
        $poll = new Poll();
        $result = $poll->get('members', 'admin', ' WHERE user_id = ' . $uid);
        return (isset($result['admin'][0])) ? ($result['admin'][0] == 1 ? true : false) : false;
    }

    function perform()
    {
        require_once 'performer.php';
        require_once 'finder.php';
        require_once 'polling.php';

        if ($this->do == CMD_EXEC) {
            require_once 'request.php';

            $request = new Request(ACCESS_TOKEN);
            $request->setMethod('execute');
            $request->addParameter('code', $this->code);
            $response = $request->perform();

            if ($request->errno) {
                $this->response = $response;
            } else {
                $this->errno = false;
                $this->error = array(
                    'type' => 'vk',
                    'vkobject' => $request->error
                );
            }
        } else if ($this->do == CMD_SQL_RUN) {
            require_once 'polling.php';
        }
    }
}