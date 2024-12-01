<?php

namespace P\Kfaka\Producer;

use RdKafka;
use RdKafka\Exception;
use RdKafka\ProducerTopic;

class AdminProducer
{
    public $producer;

    /**
     * @var ProducerTopic[]
     */
    public $topic = [];

    public function __construct()
    {
        $conf = new RdKafka\Conf();
        $conf->set('log_level', (string)LOG_DEBUG);
        $conf->set('debug', 'all');
        $this->producer = new RdKafka\Producer($conf);
        $this->producer->addBrokers("localhost:9092,localhost:9093,localhost:9094");
    }

    /**
     * @param string $topicName 不推荐使用 . 和 _ 防止和kafka 产生冲突
     * @return void
     */
    public function newTopic(string $topicName)
    {
        $this->topic[$topicName] = $this->producer->newTopic($topicName);
    }

    /**
     * @param string $topicName 主题名称
     * @param string $message 消息
     * @param int $partition 分区
     * @param int $msgSign 信息标志
     * @return void
     * @throws Exception
     */
    public function createMessage(string $topicName, string $message, int $partition = RD_KAFKA_PARTITION_UA, int $msgSign = 0)
    {
        $this->topic[$topicName]->produce($partition, $msgSign, $message);
    }

    /**
     * @param int $timeout_ms 多少毫秒后删除 producer里面的消息
     * @return void
     */
    public function flushProducer(int $timeout_ms = 600)
    {
        // Forget messages that are not fully sent yet
//        $this->producer->purge(RD_KAFKA_PURGE_F_QUEUE);
//
//        $this->producer->flush($timeout_ms);

        $this->producer->flush($timeout_ms);
    }
}