<?php
namespace bricksasp\concurrent;

/**
 *  concurrent data client test.
 */
class TestClient 
{
    public static $connection = null;

    public function __construct($server, $timeout = 5)
    {
        if(empty($server))
        {
            throw new \Exception('server empty');
        }
        if (empty(self::$connection)){
		    $connection = stream_socket_client("tcp://{$server}", $code, $msg, $timeout);
		    stream_set_timeout($connection, $timeout);
        	self::$connection = $connection;
        }
    }

    protected function getConnection()
    {
    	return self::$connection;
    }

    public function find($value)
    {
        $connection = $this->getConnection();
        $this->writeToRemote(array(
           	'cmd' => 'find',
        	'value' => $value,
        ), $connection);
        return $this->readFromRemote($connection);
    }
    
    public function sort()
    {
        $connection = $this->getConnection();
        $this->writeToRemote(array(
            'cmd' => 'sort',
        ), $connection);
        return $this->readFromRemote($connection);
    }

    public function add($value)
    {
        $connection = $this->getConnection();
        $this->writeToRemote(array(
            'cmd' => 'add',
            'value' => $value,
        ), $connection);
        return $this->readFromRemote($connection);
    }

    public function update($value)
    {
        $connection = $this->getConnection();
        $this->writeToRemote(array(
           	'cmd' => 'update',
        ), $connection);
        return $this->readFromRemote($connection);
    }

    public function delete($value)
    {
        $connection = $this->getConnection();
        $this->writeToRemote(array(
           	'cmd' => 'delete',
            'value' => $value,
        ), $connection);
        return $this->readFromRemote($connection);
    }

    public function show($v=true)
    {
        $connection = $this->getConnection();
        $this->writeToRemote(array(
           	'cmd' => 'show',
           	'value' => $v,
        ), $connection);
        return $this->readFromRemote($connection);
    }

    public function enqueue($value)
    {
        $connection = $this->getConnection();
        $this->writeToRemote(array(
            'cmd' => 'enqueue',
            'value' => $value,
        ), $connection);
        return $this->readFromRemote($connection);
    }

    public function dequeue()
    {
        $connection = $this->getConnection();
        $this->writeToRemote(array(
            'cmd' => 'dequeue',
        ), $connection);
        return $this->readFromRemote($connection);
    }
    
    public function printQueue()
    {
        $connection = $this->getConnection();
        $this->writeToRemote(array(
            'cmd' => 'printQueue',
        ), $connection);
        return $this->readFromRemote($connection);
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
    
    protected function readFromRemote($connection)
    {
        $all_buffer = '';
        $total_len = 4;
        $head_read = false;
        while(1)
        {
            $buffer = fread($connection, 8192);
            if($buffer === '' || $buffer === false)
            {
                throw new \Exception('readFromRemote fail');
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
}

$c = new TestClient('127.0.0.1:2207');
// $t1 = time();

// for ($i=0; $i < 100000; $i++){
// 	$c->add(rand(1,600000));
// }
// $t2 = time();
// var_export($t2-$t1);
// var_export($c->add(rand(1,600000)));

// var_export($c->sort());
// echo PHP_EOL;
// print_r($c->find(599968));
// var_export($c->show());

/*
$a = new \SplFixedArray(2);
$a[1] = 1;
$a[0] = 0;
print_r($a);
*/

 

/*go(function () use($c)
{
    $t1 = time();
    for ($i=0; $i < 10000; $i++){
        $c->enqueue(['a'=>rand(1,600000),'b'=>rand(1,600000)]);
    }
    $t2 = time();
    var_export('入队时间：' . ($t2-$t1) . PHP_EOL);
});*/

/*go(function () use($c)
{
    $t1 = time();
    for ($i=0; $i < 600000; $i++){
        if ($data = $c->dequeue()) {
            // var_export($data . PHP_EOL);
        }else{
            break;
        }
        
    }


    $t2 = time();
    var_export('出队时间：' . ($t2-$t1) . PHP_EOL);
});*/

// var_export($c->enqueue(rand(1,600000)));
// var_export($c->enqueue(rand(1,600000)));
// var_export($c->printQueue());

// var_export($c->dequeue());
var_export($c->printQueue());

// echo PHP_EOL;






