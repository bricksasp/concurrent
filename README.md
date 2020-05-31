# concurrent

## 简介
并发队列消息php实现

安装
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bricksasp/concurrent: "~1.0"
```

or add

```json
"bricksasp/concurrent": "~1.0"
```

to the require section of your composer.json.


使用方法
-------------

```
-ArrayServer
  - 内存数组存储
    - 二分查找
    - 归并排序 （默认使用）
    - 冒泡排序
    - 插入排序
    - 选择排序
-队列使用
  - 单链表+workman实现的内存队列服务
    - ExternalVisits http入队服务
    - QueueDataServer 队列服务
    - QueueJobServer  队列作业服务
-TreeDataServer
  - 二叉树存储
  - 重复元素查找

```