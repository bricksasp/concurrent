<?php
namespace bricksasp\concurrent;

use Workerman\Worker;
use bricksasp\concurrent\Tree;

/**
 * Tree data server.
 */
class TreeDataServer
{
    /**
     * Worker instance.
     * @var worker
     */
    protected $_worker = null;

    /**
     * All data.
     * @var Tree
     */
    protected $_tree = null;

    /**
     * Construct.
     * @param string $ip
     * @param int $port
     */
    public function __construct($ip = '0.0.0.0', $port = 2207)
    {
        $worker = new Worker("frame://$ip:$port");
        $worker->count = 1;
        $worker->name = 'TreeDataServer';
        $worker->onMessage = array($this, 'onMessage');
        $worker->reloadable = false;
        $this->_worker = $worker;
        if ($this->_tree == null) {
        	$this->_tree = new Tree();
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
            case 'find':
                $node = $this->_tree->find($data['value']);
                return $connection->send(serialize($node));
                break;
            case 'add':
                $this->_tree->add($data['value']);
                return $connection->send(serialize($this->_tree->count));
                return $connection->send('b:1;');
                break;
            case 'delete':
                $this->_tree->delete($data['value']);
                return $connection->send('b:1;');
                break;
            case 'show':
                return $connection->send(serialize($this->_tree->head->data . ':' . $this->_tree->preOrder($this->_tree->head, $data['value'] ?? true)));
                break;
            default:
                return $connection->close(serialize('bad cmd '. $cmd));
        }
    }
}

$worker = new TreeDataServer('127.0.0.1', 2207);


Worker::runAll();