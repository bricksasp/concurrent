<?php
namespace bricksasp\concurrent;

use bricksasp\concurrent\TreeNode;

class Tree
{

    /**
     * 树的根节点
     * @var [type]
     */
    public $head = null;

    public $count = 0;

    /**
     * @param TreeNode|null
     */
    public function __construct($headData = null)
    {
        if ($headData != null) {
            $this->head = new TreeNode($headData);
        }
    }

    /**
     * 查找数据
     * @param  [type] $data [数据]
     * @return TreeNode|null
     */
    public function find($data)
    {
        if ($this->head == null) {
            return null;
        }

        $node = $this->head;

        while ($node != null) {
            if ($node->data == $data) {
                return $node;
            } elseif ($data > $node->data) {
                $node = $node->right;
            } else {
                $node = $node->left;
            }
        }

        return null;
    }

    /**
     * 插入数据
     * @param  [type] $data [数据]
     * @return bool
     */
    public function add($data)
    {
        if ($this->head == null) {
            $this->head = new TreeNode($data);
            return true;
        }

        $node = $this->head;

        while ($node != null) {
            if ($data > $node->data) {
                if ($node->right == null) {
                    $node->right = new TreeNode($data);
                    ++$this->count;
                    return true;
                }
                $node = $node->right;
            } else {
                if ($node->left == null) {
                    $node->left = new TreeNode($data);
                    ++$this->count;
                    return true;
                }
                $node = $node->left;
            }
        }
    }

    /**
     * 删除节点
     * @param  [type] $data [节点]
     * @return bool
     */
    public function delete($data)
    {
        // 找到需要删除节点
        $node = $this->head;
        $pnode = null;
        while ($node != null) {
            if ($node->data == $data) {
                break;
            } elseif ($data > $node->data) {
                $pnode = $node;
                $node = $node->right;
            } else {
                $pnode = $node;
                $node = $node->left;
            }
        }        
        if ($node == null) {
            return false;
        }
        // 要删除的节点有两个子节点
        // 查找右子树中最小节点
        if ($node->left != null && $node->right != null) {
            $minPP = $node;
            $minP = $node->right;
            while ($minP->left != null) {
                $minPP = $minP;
                $minP = $minP->left;
            }
            $node->data = $minP->data;
            $node = $minP;
            // 删除掉右子树中的最小节点
            $minPP->left = null;
        }

        if ($node->left != null) {
            $child = $node->left;
        } elseif ($node->right != null) {
            $child = $node->right;
        } else {
            $child = null;
        }

        if ($pnode == null) {
            // 删除的是根节点
            $node = $child;
        } elseif ($pnode->left == $node) {
            $pnode->left = $child;
        } else {
            $pnode->right = $child;
        }
        --$this->count;
        return true;
    }

    /**
     * 前序遍历
     * @return 
     */
    public function preOrder($node, $left=true)
    {
        if ($node == null) {
            return ;            
        }
        if ($left) {
            return '->' . $node->data . $this->preOrder($node->left,$left);
        }else {
            return '->' . $node->data . $this->preOrder($node->right,$left);
        }
    }
}