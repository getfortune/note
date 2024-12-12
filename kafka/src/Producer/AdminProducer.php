<?php

namespace P\Kfaka\Producer;

use RdKafka;
use RdKafka\Exception;
use RdKafka\Producer;
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
//        $conf->set('log_level', (string)LOG_DEBUG);
//        $conf->set('debug', 'all');
        $conf->set('bootstrap.servers', 'kafka2:9093,kafka3:9094,kafka:9092'); // 使用 Docker Compose 服务名称连接 Kafka
        /**
         * 用于确定当消费者在一个新的消费组中或者消费的偏移量无效的情况下应该从哪里开始读取消息。
         * 当设置为earliest时，消费者会从最早的可用消息开始消费，即从最早的偏移量开始消费。
         * 当设置为latest时，消费者会从最新的消息开始消费，即从最新的偏移量开始消费。
         */
//        $conf->set('auto.offset.reset', 'earliest');  // 作用于消费者
        /**
         * 参数用于控制是否启用自动提交消费位移（offset）功能。
         * 当设置为0时，自动提交消费位移功能被禁用，消费者需要手动提交位移以确保消息被正确消费。
         * 当设置为1时，启用自动提交消费位移功能，消费者会自动提交位移，但可能会导致消息重复处理或消息丢失的情况。
         * 禁用自动提交位移可以确保消息在被正确处理后才提交位移，从而确保消息不会丢失或重复处理。
         */
//        $conf->set('enable.auto.commit', 0);
//        $conf->set('log.replication', 3); // 指定副本   如果需要针对不同的分区设置不同的副本那么需要根据多个Conf 创建多个 producer
        // 配合 KafkaConsumer 使用  group.id 对于 producer 无效
//        $conf->set('group.id', 'myConsumerGroup');
        $this->producer  = new Producer($conf);
    }

    /**
     * @param string $topicName 不推荐使用 . 和 _ 防止和kafka 产生冲突
     * @return void
     */
    public function newTopic(string $topicName)
    {
        // newTopic 第二个参数 是用来自定义副本分区策略的
        $this->topic[$topicName] = $this->producer->newTopic($topicName);
    }

    /**
     * @param string $topicName 主题名称
     * @param string $message 消息
     * @param int $partition 分区
     * @param int $msgSign 信息标志
     * @return int
     * @throws Exception
     */
    public function createMessage(string $topicName, string $message, int $partition = RD_KAFKA_PARTITION_UA, int $msgSign = 0): int
    {
        $this->topic[$topicName]->produce($partition, $msgSign, $message);
        return $this->producer->flush(1000);
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

//$conf = new RdKafka\Conf();
//$conf->set('metadata.broker.list', 'localhost:9092');
//
//$producer = new RdKafka\Producer($conf);
//$producer->addBrokers('localhost:9092');
//
//$topic = $producer->newTopic('test');
//
//for ($i = 0; $i < 10; $i++) {
//    $topic->produce(RD_KAFKA_PARTITION_UA, 0, 'Message ' . $i);
//}
//
//$producer->poll(0);
//
//while ($producer->getOutQLen() > 0) {
//    $producer->poll(50);
//}
//
//echo "Messages sent successfully\n";

