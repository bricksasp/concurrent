<?php
namespace bricksasp\concurrent;

use bricksasp\concurrent\SingleLinkedListNode;

/**
 * 队列 链表实现
 */
class QueueOnLinkedList
{
    /**
     * 队列头节点
     *
     * @var SingleLinkedListNode
     */
    public $head;

    /**
     * 队列尾节点
     *
     * @var null
     */
    public $tail;

    /**
     * 队列长度
     *
     * @var int
     */
    public $length = 0;

    /**
     * QueueOnLinkedList constructor.
     */
    public function __construct()
    {
        $this->head = new SingleLinkedListNode();
        $this->tail = $this->head;
    }

    /**
     * 入队
     *
     * @param $data
     */
    public function enqueue($data)
    {
        $newNode = new SingleLinkedListNode();
        $newNode->data = $data;

        $this->tail->next = $newNode;
        $this->tail = $newNode;

        $this->length++;
    }

    /**
     * 出队
     *
     * @return SingleLinkedListNode|bool|null
     */
    public function dequeue()
    {
        if (0 == $this->length) {
            return false;
        }

        $node = $this->head->next;
        $this->head->next = $this->head->next->next;

        $this->length--;
        if (0 == $this->length) {
            $this->tail = $this->head;
        }

        return $node->data;
    }

    /**
     * 获取队列长度
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * 打印队列
     */
    public function printQueue()
    {
        // print_r($this->head);
        // print_r($this->tail);
        // print_r($this->length);
        if (0 == $this->length) {
            return 'empty queue' . PHP_EOL;
            
        }

        $curNode = $this->head;
        $str = '';
        $node = $this->head->next;
        while ($node) {
            $str .= var_export($node->data,true) . PHP_EOL;

            $node = $node->next;
        }
        return $str;
    }
}