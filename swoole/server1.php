<?php

class

HttpServer
{

    public $serv;


    public function __construct()
    {
        $this->serv = new swoole_http_server('0.0.0.0', 9502);
        $this->serv->set([
            'worker_num'      => 2, //开启2个worker进程
            'max_request'     => 4, //每个worker进程 max_request设置为4次
            'task_worker_num' => 4, //开启4个task进程
            'dispatch_mode'   => 2,//数据包分发策略 - 固定模式
        ]);

        $this->serv->on('request', [$this, 'onRequest']);
        $this->serv->on("Task", [$this, 'onTask']);
        $this->serv->on("Finish", [$this, 'onFinish']);

        $this->serv->start();

    }

    public function OnRequest($request, $response)
    {

        $response->end("<h1>Hello Swoole. #" . rand(1000, 9999) . "</h1>");
    }

    public function OnTask()
    {

    }

    public function OnFinish()
    {

    }
}

/*var_dump('ss33');
$http = new HttpServer();

var_dump($http->serv->worker_id, 'ss    ');
$http->start();*/


class Server
{

    private $serv;


    public function __construct()
    {
        $this->serv = new swoole_server('0.0.0.0', 9501);
        $this->serv->set([
            'worker_num'      => 2, //开启2个worker进程
            'max_request'     => 4, //每个worker进程 max_request设置为4次
            'task_worker_num' => 4, //开启4个task进程
            'dispatch_mode'   => 2,//数据包分发策略 - 固定模式
        ]);

        $this->serv->on('Start', [$this, 'onStart']);
        $this->serv->on('Connect', [$this, 'onConnect']);
        $this->serv->on("Receive", [$this, 'onReceive']);
        $this->serv->on("Close", [$this, 'onClose']);
        $this->serv->on("Task", [$this, 'onTask']);
        $this->serv->on("Finish", [$this, 'onFinish']);
        $this->serv->start();

    }


    public function onStart($serv)
    {
        echo "#### onStart ####" . PHP_EOL;
        echo "SWOOLE " . SWOOLE_VERSION . " 服务已启动" . PHP_EOL;
        echo "master_pid: {$serv->master_pid}" . PHP_EOL;
        echo "manager_pid: {$serv->manager_pid}" . PHP_EOL;
        echo "########" . PHP_EOL . PHP_EOL;
    }


    public function onConnect($serv, $fd)
    {
        echo "#### onConnect ####" . PHP_EOL;
        echo "客户端:" . $fd . " 已连接" . PHP_EOL;
        echo "########" . PHP_EOL . PHP_EOL;

    }


    public function onReceive($serv, $fd, $from_id, $data)
    {
        echo "#### onReceive ####" . PHP_EOL;
        echo "worker_pid: {$serv->worker_pid}" . PHP_EOL;
        echo "客户端:{$fd} 发来的Email:{$data}" . PHP_EOL;
        echo $from_id;
        $param = ['fd' => $fd, 'email' => $data];
        $rs    = $serv->task(json_encode($param));

        if ($rs === false) {
            echo "任务分配失败 Task " . $rs . PHP_EOL;

        } else {
            echo "任务分配成功 Task " . $rs . PHP_EOL;
        }
        echo "########" . PHP_EOL . PHP_EOL;

    }


    public function onTask($serv, $task_id, $from_id, $data)
    {
        var_dump($data);
        echo "#### onTask ####" . PHP_EOL;
        echo "#{$serv->worker_id} onTask: [PID={$serv->worker_pid}]: task_id={$task_id}" . PHP_EOL;


//业务代码

        for ($i = 1; $i <= 5; $i++) {
            sleep(2);
            echo "Task {$task_id} 已完成了 {$i}/5 的任务" . PHP_EOL;
        }

        $data_arr = json_decode($data, true);
        $serv->send($data_arr['fd'], 'Email:' . $data_arr['email'] . ',发送成功');
        $serv->finish($data);
        echo "########" . PHP_EOL . PHP_EOL;

    }


    public function onFinish($serv, $task_id, $data)
    {
        echo "#### onFinish ####" . PHP_EOL;
        echo "Task {$task_id} 已完成" . PHP_EOL;
        echo "########" . PHP_EOL . PHP_EOL;
    }


    public function onClose($serv, $fd)
    {
        echo "Client Close." . PHP_EOL;

    }
}

//$server = new Server();


/*class WebsocketTest {
    public $server;
    public function __construct() {
        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

        $this->server->on('start', function () {
           var_dump("启动");
        });

        $this->server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $server->push($frame->fd, "this is server");
        });
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });
        $this->server->on('request', function ($request, $response) {
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
            foreach ($this->server->connections as $fd) {
                // 需要先判断是否是正确的websocket连接，否则有可能会push失败
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });
        $this->server->start();
    }
}
new WebsocketTest();*/


class WServer
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_websocket_server("0.0.0.0", 9501);
        $this->serv->set([
            'worker_num'      => 2, //开启2个worker进程
            'max_request'     => 4, //每个worker进程 max_request设置为4次
            'task_worker_num' => 4, //开启4个task进程
            'dispatch_mode'   => 4, //数据包分发策略 - IP分配
            'daemonize'       => false, //守护进程(true/false)
        ]);

        $this->serv->on('Start', [$this, 'onStart']);
        $this->serv->on('Open', [$this, 'onOpen']);
        $this->serv->on("Message", [$this, 'onMessage']);
        $this->serv->on("Close", [$this, 'onClose']);
        $this->serv->on("Task", [$this, 'onTask']);
        $this->serv->on("Finish", [$this, 'onFinish']);

        $this->serv->start();
    }

    // 启动
    public function onStart($serv)
    {
        echo "#### onStart ####" . PHP_EOL;
        echo "SWOOLE " . SWOOLE_VERSION . " 服务已启动" . PHP_EOL;
        echo "master_pid: {$serv->master_pid}" . PHP_EOL;
        echo "manager_pid: {$serv->manager_pid}" . PHP_EOL;
        echo "########" . PHP_EOL . PHP_EOL;
    }

    // 有人连接
    public function onOpen($serv, $request)
    {
        echo "#### onOpen ####" . PHP_EOL;
        echo "server: handshake success with fd{$request->fd}" . PHP_EOL;
        /* $serv->task([
             'type' => 'login'
         ]);*/
        echo "########" . PHP_EOL . PHP_EOL;
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        var_dump($data);
        echo "#### onTask ####" . PHP_EOL;
        echo "#{$serv->worker_id} onTask: [PID={$serv->worker_pid}]: task_id={$task_id}" . PHP_EOL;
        $msg = '';
        switch ($data['type']) {
            case 'join':
                $msg = 'join:欢迎' . $data['msg'] . '加入聊天群';
                break;
            case 'talk':
                $msg = 'talk:' . $data['msg'];
                break;
        }
        var_dump($msg);
        foreach ($serv->connections as $fd) {
            if ($fd != $data['fd'] || $data['type'] == 'join') {
                $connectionInfo = $serv->connection_info($fd);
                if ($connectionInfo['websocket_status'] == 3) {
                    $serv->push($fd, $msg); //长度最大不得超过2M
                }
            }
        }
        $serv->finish($data);
        echo "########" . PHP_EOL . PHP_EOL;
    }

    public function onMessage($serv, $frame)
    {
        echo "#### onMessage ####" . PHP_EOL;
        echo "receive from fd{$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}" . PHP_EOL;
        $data    = $frame->data;
        $dataArr = explode(':', $data);
        $type    = $dataArr[0];
        $content = $dataArr[1];

        $data = ['type' => $type, 'msg' => $content, 'fd' => $frame->fd];

        var_dump('yuyan', $data);

        $serv->task($data);
        echo "########" . PHP_EOL . PHP_EOL;
    }

    public function onFinish($serv, $task_id, $data)
    {
        echo "#### onFinish ####" . PHP_EOL;
        echo "Task {$task_id} 已完成" . PHP_EOL;
        echo "########" . PHP_EOL . PHP_EOL;
    }

    public function onClose($serv, $fd)
    {
        echo "#### onClose ####" . PHP_EOL;
        echo "client {$fd} closed" . PHP_EOL;
        echo "########" . PHP_EOL . PHP_EOL;
    }
}

$server = new WServer();