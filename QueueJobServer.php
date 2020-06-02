<?php
namespace bricksasp\concurrent;

use Workerman\Worker;

/**
 * Queue job server.
 */
class QueueJobServer
{
    /**
     * Timeout.
     * @var int
     */
    public $timeout = 5;
    
    /**
     * Heartbeat interval.
     * @var int
     */
    public $pingInterval = 0.00005;

    /**
     * Worker instance.
     * @var worker
     */
    protected $_worker = null;
    
    public static $connection = null;

    /**
     * Construct.
     * @param string $ip
     * @param int $port
     */
    public function __construct($ip = '0.0.0.0', $port = 8999, $server = '127.0.0.1:2207')
    {
        if(empty($server))
        {
            throw new \Exception('server empty');
        }
        Worker::$eventLoopClass = 'Workerman\Events\Swoole';
        $worker = new Worker("frame://$ip:$port");
        $worker->count = 4;
        $worker->name = 'QueueJobServer';
        $worker->onMessage = array($this, 'onMessage');
        $worker->reloadable = false;

        if (empty(self::$connection)){
            $connection = stream_socket_client("tcp://{$server}", $code, $msg, $this->timeout);
            stream_set_timeout($connection, $this->timeout);
            self::$connection = $connection;

            $worker->onWorkerStart = function ($worker)use ($connection)
            {
                go(function () use($connection)
                {
                    while (1) {
                        writeToRemote(array(
                            'cmd' => 'dequeue',
                        ), $connection);
                        $data = readFromRemote($connection);
                        if ($data) {
                            jobEvent($data);
                        }
                        usleep(500);
                    }
                });
            };
        }
        $this->_worker = $worker;
    }

    public function onMessage($connection, $buffer)
    {
        $data = unserialize($buffer);
        if(!$buffer || !isset($data['cmd']))
        {
            return $connection->close(serialize('bad request'));
        }
        $cmd = $data['cmd'];
        switch($cmd)
        {
            case 'show':
                return $connection->send(serialize('show'));
                break;
            default:
                return $connection->close(serialize('bad cmd '. $cmd));
        }
    }
}

// job结构['job'=>'标识' , 'parama1'=>1 ,'parama2'=>2 ,...]
function jobEvent($data)
{
    if(!isset($data['job']))
    {
        var_export($data);
        return false;
    }
    $job = $data['job'];
    switch($job)
    {
        case 'show':
            return $connection->send(serialize('show'));
            break;
        default:
            return false;
    }
}


function writeToRemote($data, $connection)
{
    $buffer = serialize($data);
    $buffer = pack('N',4 + strlen($buffer)). $buffer;
    $len = fwrite($connection, $buffer);
    if($len !== strlen($buffer))
    {
        throw new \Exception('writeToRemote fail');
    }
}
    
function readFromRemote($connection)
{
    $all_buffer = '';
    $total_len = 4;
    $head_read = false;
    while(1)
    {
        $buffer = fread($connection, 8192);
        if($buffer === '' || $buffer === false)
        {
            return false;
        }
        $all_buffer .= $buffer;
        $recv_len = strlen($all_buffer);
        if($recv_len >= $total_len)
        {
            if($head_read)
            {
                break;
            }
            $unpack_data = unpack('Ntotal_length', $all_buffer);
            $total_len = $unpack_data['total_length'];
            if($recv_len >= $total_len)
            {
                break;
            }
            $head_read = true;
        }
    }
    return unserialize(substr($all_buffer, 4));
}