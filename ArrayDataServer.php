<?php
namespace bricksasp\concurrent;

use Workerman\Worker;

/**
 * array data server.
 */
class ArrayServer
{
    /**
     * Worker instance.
     * @var worker
     */
    protected $_worker = null;

    /**
     * All data.
     * @var array
     */
    protected $_dataArray = array();

    /**
     * Construct.
     * @param string $ip
     * @param int $port
     */
    public function __construct($ip = '0.0.0.0', $port = 2207)
    {
        $worker = new Worker("frame://$ip:$port");
        $worker->count = 1;
        $worker->name = 'arrayDataServer';
        $worker->onMessage = array($this, 'onMessage');
        $worker->reloadable = false;
        $this->_worker = $worker; 
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
                $index = binarySearch($this->_dataArray,$data['value']);
                if ($index == -1) {
                    return $connection->send('b:0;');
                }
                return $connection->send(serialize(['index'=>$index, 'value' => $this->_dataArray[$index]]));
                break;
            case 'sort':
                $this->_dataArray = merge_sort($this->_dataArray);
                return $connection->send('b:1;');
                break;
            case 'add':
                $this->_dataArray[] = $data['value'];
                return $connection->send('b:1;');
                break;
            case 'update':
                $index = binarySearch($this->_dataArray,$data['value']);
                if ($index == -1) {
                    return $connection->send('b:0;');
                }
                $this->_dataArray[$index] = $data['value'];
                return $connection->send('b:1;');
                break;
            case 'delete':
                $index = binarySearch($this->_dataArray,$data['value']);
                if ($index == -1) {
                    return $connection->send('b:0;');
                }
                unset($this->_dataArray[$index]);
                return $connection->send('b:1;');
                break;
            case 'show':
                return $connection->send(serialize(array_slice($this->_dataArray,0,1000)));
                break;
            default:
                return $connection->close(serialize('bad cmd '. $cmd));
        }
    }
}


// 归并排序
function merge_sort($arr)
{
    if(count($arr) <= 1){
        return $arr;
    }

    $left = array_slice($arr,0,(int)(count($arr)/2));
    $right = array_slice($arr,(int)(count($arr)/2));

    $left = merge_sort($left);
    $right = merge_sort($right);

    $output = merge($left,$right);

    return $output;

}


function merge($left,$right)
{
    $result = array();

    while(count($left) >0 && count($right) > 0)
    {
        if($left[0] <= $right[0]){
            array_push($result,array_shift($left));
        }else{
            array_push($result,array_shift($right));
        }
    }

    array_splice($result,count($result),0,$left);
    array_splice($result,count($result),0,$right);

    return $result;
}

// 二分查找
function binarySearch(array $numbers, $find)
{
    $low = 0;
    $high = count($numbers) - 1;
    return search($numbers, $low, $high, $find);
}

function search(array $numbers, $low, $high, $find)
{
    if ($low > $high) {
        return -1;
    }

    /**
     * mid计算
     */
    $mid = $low + (($high - $low) >> 1);
    if ($numbers[$mid] > $find) {
        return search($numbers, $low, $mid -1, $find);
    } elseif ($numbers[$mid] < $find) {
        return search($numbers, $mid + 1, $high, $find);
    } else {
        return $mid;
    }
}

// 冒泡排序
function bubbleSort(&$arr)
{
    $length = count($arr);
    if ($length <= 1) return;

    for ($i = 0; $i < $length; $i++) {
        $flag = false;
        for ($j = 0; $j < $length - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                $tmp = $arr[$j];
                $arr[$j] = $arr[$j + 1];
                $arr[$j + 1] = $tmp;
                $flag = true;
            }
        }
        if (!$flag) {
            break;
        }
    }
}

// 插入排序
function insertionSort(&$arr)
{
    $n = count($arr);
    if ($n <= 1) return;

    for ($i = 1; $i < $n; ++$i) {
        $value = $arr[$i];
        $j = $i - 1;
        // 查找插入的位置
        for (; $j >= 0; --$j) {
            if ($arr[$j] > $value) {
                $arr[$j + 1] = $arr[$j];  // 数据移动
            } else {
                break;
            }
        }
        $arr[$j + 1] = $value; // 插入数据
    }
}

// 选择排序
function selectionSort(&$arr)
{
    $length = count($arr);
    if ($length <= 1) return;

    for ($i = 0; $i < $length - 1; $i++) {
        //先假设最小的值的位置
        $p = $i;
        for ($j = $i + 1; $j < $length; $j++) {
            if ($arr[$p] > $arr[$j]) {
                $p = $j;
            }
        }
        $tmp = $arr[$p];
        $arr[$p] = $arr[$i];
        $arr[$i] = $tmp;
    }
}
