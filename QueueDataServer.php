<?php
namespace bricksasp\concurrent;

use Workerman\Worker;
use bricksasp\concurrent\QueueOnLinkedList;

/**
 * Queue data server.
 */
class QueueDataServer
{
    /**
     * Worker instance.
     * @var worker
     */
    protected $_worker = null;

    /**
     * All data.
     * @var QueueOnLinkedList
     */
    protected $_queue = null;

    /**
     * Construct.
     * @param string $ip
     * @param int $port
     */
    public function __construct($ip = '0.0.0.0', $port = 2207)
    {
        $worker = new Worker("frame://$ip:$port");
        $worker->count = 1;
        $worker->name = 'QueueDataServer';
        $worker->onMessage = array($this, 'onMessage');
        $worker->reloadable = false;
        $this->_worker = $worker;
        if ($this->_queue == null) {
            $this->_queue = new QueueOnLinkedList();
        }
    }
    
    public function onMessage($connection, $buffer)
    {
        if($buffer === 'ping')
        {
            return;
        }
        $data = unserialize($buffer);
        if(!$buffer || !isset($data['cmd']))
        {
            return $connection->close(serialize('bad request'));
        }
        $cmd = $data['cmd'];
        switch($cmd)
        {
            case 'enqueue':
                $this->_queue->enqueue($data['value']);
                return $connection->send(serialize($this->_queue->getLength()));
                break;
            case 'dequeue':
                return $connection->send(serialize($this->_queue->dequeue()));
                break;
            case 'printQueue':
                return $connection->send(serialize($this->_queue->printQueue()));
                break;
            default:
                return $connection->close(serialize('bad cmd '. $cmd));
        }
    }
}

$worker = new QueueDataServer('127.0.0.1', 2207);


Worker::runAll();