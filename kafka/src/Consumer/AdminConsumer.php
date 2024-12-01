<?php

namespace P\Kfaka\Consumer;

use RdKafka;

class AdminConsumer
{
    public $consumer;

    public function __construct()
    {
        $conf = new RdKafka\Conf();
        $conf->set('log_level', (string)LOG_DEBUG);
        $conf->set('debug', 'all');
        $this->consumer = new RdKafka\Consumer($conf);
        $this->consumer->addBrokers("localhost:9092,localhost:9093,localhost:9094");
    }

    public function consumerTopic(string $topic, int $partition = 0, int $offset = RD_KAFKA_OFFSET_BEGINNING)
    {
        $topic = $this->consumer->newTopic($topic);

        // The first argument is the partition to consume from.
        // The second argument is the offset at which to start consumption. Valid values
        // are: RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED.
        $topic->consumeStart($partition, $offset);
    }

    public function consumerAllMessage(string $topic)
    {
        $topic = $this->consumer->newTopic($topic);
        while (true) {
            // The first argument is the partition (again).
            // The second argument is the timeout.
            $msg = $topic->consume(0, 1000);
            if (null === $msg || $msg->err === RD_KAFKA_RESP_ERR__PARTITION_EOF) {
                // Constant check required by librdkafka 0.11.6. Newer librdkafka versions will return NULL instead.
                continue;
            } elseif ($msg->err) {
                echo $msg->errstr(), "\n";
                break;
            } else {
                echo $msg->payload, "\n";
            }
        }
    }
}