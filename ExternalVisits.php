<?php
namespace bricksasp\concurrent;

use Workerman\Worker;

/**
 * 外部http请求
 */
class ExternalVisits
{
    public $timeout = 5;

    public $pingInterval = 30;

    /**
     * Worker instance.
     * @var worker
     */
    protected $_worker = null;

    public static $connection = null;

    public $autoSave = false;

    public static $fp = null;

    /**
     * Construct.
     * @param string $ip
     * @param int $port
     */
    public function __construct($ip = '0.0.0.0', $port = 2209, $server = '127.0.0.1:2207')
    {
        Worker::$eventLoopClass = 'Workerman\Events\Swoole';
        $worker = new Worker("http://$ip:$port");
        $worker->count = 4;
        $worker->name = 'ExternalVisits';
        $worker->onMessage = array($this, 'onMessage');

        if (empty(self::$connection)){
		    $connection = stream_socket_client("tcp://{$server}", $code, $msg, $this->timeout);
		    stream_set_timeout($connection, $this->timeout);
        	self::$connection = $connection;

            $timer_id = \Workerman\Lib\Timer::add($this->pingInterval, function($connection)use(&$timer_id)
            {
                $buffer = pack('N', 8)."ping";
                if(strlen($buffer) !== @fwrite($connection, $buffer))
                {
                    @fclose($connection);
                    \Workerman\Lib\Timer::del($timer_id);
                }
            }, array($connection));
        }

    	if ($this->autoSave) {
    		self::$fp = fopen(dirname(__FILE__) . '/data/queue.data','a+');
    		// var_export(self::$fp);exit;
    	}
        $this->_worker = $worker;
    }
    
    protected function getConnection()
    {
    	return self::$connection;
    }

    public function onMessage($connection, $buffer)
    {
    	if ($buffer['server']['REQUEST_METHOD'] !== 'POST') {
    		return $connection->close('REQUEST_METHOD NOT ALLOW');
    	}
    	$params = $buffer['post'];
    	if (empty($params['job'])) {
    		return $connection->close('REQUEST_DATA ERROR');
    	}

		// job结构['job'=>'标识' , 'parama1'=>1 ,'parama2'=>2 ,...]
    	$this->writeToRemote(['cmd' => 'enqueue', 'value' => $params], self::$connection);
    	if ($this->autoSave) {
	    	go(function () use($params)
	        {
	            fwrite(self::$fp, serialize($params) . PHP_EOL);
	        });
    	}
        return $connection->close('ok');
    }

    protected function writeToRemote($data, $connection)
    {
        $buffer = serialize($data);
        $buffer = pack('N',4 + strlen($buffer)). $buffer;
        $len = fwrite($connection, $buffer);
        if($len !== strlen($buffer))
        {
            throw new \Exception('writeToRemote fail');
        }
    }
}
