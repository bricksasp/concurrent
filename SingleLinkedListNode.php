<?php
namespace bricksasp\concurrent;

/**
 * 单链表节点
 */
class SingleLinkedListNode
{
    /**
     * 节点中的数据域
     *
     * @var null
     */
    public $data;

    /**
     * 节点中的指针域，指向下一个节点
     *
     * @var SingleLinkedListNode
     */
    public $next;

    /**
     * SingleLinkedListNode constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        $this->data = $data;
        $this->next = null;
    }
}