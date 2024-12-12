<?php

namespace P\Kfaka\Consumer;

use RdKafka\Conf;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

class RetryConsumer
{
    function processMessage($message)
    {
        // 实际的消息处理逻辑
        echo "处理消息: ", $message->payload, "\n";
    }

    /**
     * 手动提交偏移量 + 重试功能
     * @return void
     * @throws Exception
     */
    public function useManualCommit() {
        $conf = new Conf();
        $conf->set('bootstrap.servers', 'localhost:9092');
        $conf->set('group.id', 'test-group');

        // 关闭自动提交偏移量
        $conf->set('enable.auto.offset.store', 'false');

        $consumer = new KafkaConsumer($conf);
        $consumer->subscribe(['your-topic']);

        $maxRetries = 3;

        while (true) {
            $message = $consumer->consume(120 * 1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $success = false;
                    $attempts = 0;

                    while (!$success && $attempts < $maxRetries) {
                        $attempts++;
                        try {
                            // 处理消息
                            $this->processMessage($message);
                            $success = true;
                        } catch (Exception $e) {
                            // 处理失败，记录错误
                            echo "处理失败，尝试 $attempts 次: ", $e->getMessage(), "\n";
                        }
                    }

                    if ($success) {
                        // 手动提交偏移量
                        $consumer->commit($message);
                    } else {
                        // 如果达到最大重试次数仍然失败，可以选择记录日志或将消息发送到死信队列
                        // 死信队列
                        $this->sendToDLQ($message);
                        // 记录日志
                        echo "消息处理失败，超过最大重试次数: ", $message->payload, "\n";
                    }
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    // 没有更多的消息
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // 超时
                    break;
                default:
                    // 其他错误
                    throw new Exception($message->errstr(), $message->err);
                    break;
            }
        }
    }


    /**
     * 死信队列，专门有个consumer 来进行消费
     * @param $message
     * @return void
     * @throws Exception
     */
    function sendToDLQ($message)
    {
//        // 在处理消息失败后调用
//        sendToDLQ($message);
        $producer = new Producer();
        $producer->addBrokers("localhost:9092");
        $topic = $producer->newTopic("dead-letter-queue");
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message->payload);
        $producer->flush(10000);
    }


}