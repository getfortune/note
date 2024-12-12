<?php

namespace P\Kfaka\Consumer;

use RdKafka;
use RdKafka\Exception;
use RdKafka\KafkaConsumerTopic;

class AdminConsumer
{
    public function getConf(): RdKafka\Conf
    {
        $conf = new RdKafka\Conf();
//        $conf->set('log_level', (string)LOG_DEBUG);
//        $conf->set('debug', 'all');
        $conf->set('bootstrap.servers', 'kafka2:9093,kafka3:9094,kafka:9092'); // 设置bootstrap.servers参数
//        $conf->set('auto.offset.reset', 'earliest');
//        $conf->set('enable.auto.commit', 0);
        $conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    echo "Assign: ";
                    var_dump($partitions);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    echo "Revoke: ";
                    var_dump($partitions);
                    $kafka->assign(NULL);
                    break;

                default:
                    throw new \Exception($err);
            }
        });

        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        // 使用 KafkaConsumer 需要group.id
        $conf->set('group.id', 'myConsumerGroup');

        // Initial list of Kafka brokers
//        $conf->set('metadata.broker.list', '127.0.0.1');

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'earliest': start from the beginning
        $conf->set('auto.offset.reset', 'earliest');

        // Emit EOF event when reaching the end of a partition
        $conf->set('enable.partition.eof', 'true');

        return $conf;
    }

    /**
     * @param $callback
     * @param string $topicName
     * @param int $partition
     * @param int $offset  没有只当offset 就是从头开始消费当前 topic
     * @return void
     */
    public function consumerTopic($callback, string $topicName, int $partition = 0, int $offset = RD_KAFKA_OFFSET_BEGINNING)
    {
        $consumer = new RdKafka\Consumer($this->getConf());
        $topic = $consumer->newTopic($topicName);
        $topic->consumeStart(0, RD_KAFKA_OFFSET_BEGINNING);
        while (true) {
            $msg = $topic->consume(0, 1000);
            if (null === $msg || $msg->err === RD_KAFKA_RESP_ERR__PARTITION_EOF) {
                continue;
            } elseif ($msg->err) {
                echo $msg->errstr(), "\n";
                break;
            } else {
                $status = call_user_func($callback, $msg->payload);
                if (!$status) {
                    echo "消费失败\n";
                } else {
                    echo "消费成功\n";
                }
                break;
            }
        }
        echo "消费完成\n";
        $topic->consumeStop(0);
    }

    /**
     * 从上一个offset 继续向后消费
     * @param $callback
     * @param array $topicName
     * @param int $partition
     * @param int $offset
     * @return mixed
     * @throws Exception
     */
    public function subscribeTopic($callback, array $topicName, int $partition = 0, int $offset = RD_KAFKA_OFFSET_BEGINNING)
    {
        $kafkaConsumer = new RdKafka\KafkaConsumer($this->getConf());
        $kafkaConsumer->subscribe($topicName);
        while (true) {
            $message = $kafkaConsumer->consume(120*1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $status = call_user_func($callback, $message->payload);
                    if ($status) {
                        $kafkaConsumer->commit();
                    }
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "No more messages; will wait for more\n";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "Timed out\n";
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }
}