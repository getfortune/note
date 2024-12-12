<?php
namespace  P\Kfaka;
use P\Kfaka\Consumer\AdminConsumer;
use P\Kfaka\Producer\AdminProducer;
use RdKafka\Conf;
use RdKafka\Producer;

require_once "../vendor/autoload.php";
class index {
    public $producer;

    public $topic;

    public function __construct()
    {
        $conf = new Conf();
        $conf->set('bootstrap.servers', 'kafka2:9093,kafka3:9094,kafka:9092');
        $this->producer  = new Producer($conf);
//        $this->producer->addBrokers("localhost:9092");
    }

    public function newTopic(string $topicName)
    {
        // newTopic 第二个参数 是用来自定义副本分区策略的
        $this->topic = $this->producer->newTopic($topicName);
    }

    public function createMessage(string $topicName, string $message, int $partition = RD_KAFKA_PARTITION_UA, int $msgSign = 0): int
    {
        $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, '123123');
        return $this->producer->flush(1000);
    }
}

class KafkaProducer {
    public $producer;

    public function __construct() {
        $conf = new \RdKafka\Conf();
        $conf->set('bootstrap.servers', 'kafka2:9093,kafka3:9094,kafka:9092');
        $this->producer = new \RdKafka\Producer($conf);
    }

    public function produce($topic, $message) {
//        $this->producer->addBrokers("localhost:9092");
        $topica = $this->producer->newTopic($topic);
        $topica->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    }

    public function flush($timeout = 1000) {
        return $this->producer->flush($timeout);
    }
}

$admin = new KafkaProducer();
$admin->produce('test', '123123');
$a = $admin->flush(10000);
echo $a;

// 下面这个是可以的
// 创建 Kafka 配置对象
//$conf = new Conf();
//
//// 设置 Kafka 集群的连接信息
//$conf->set('bootstrap.servers', 'kafka2:9093,kafka3:9094,kafka:9092'); // 使用 Docker Compose 服务名称连接 Kafka  这里需要使用实际的主机名不能使用 localhost
////$conf->setErrorCb(function ($kafka, $err, $reason) {
////    printf("Kafka error: %s (reason: %s)\n", rd_kafka_err2str($err), $reason);
////});
//$rk  = new \RdKafka\Producer($conf);
////$rk->addBrokers("localhost:9092");
//$topic =  $rk->newTopic('1111');
//$value = json_encode('111111');
//$topic->produce(RD_KAFKA_PARTITION_UA, 0, $value);
//// flush重试3次
//for ($flushRetries = 0; $flushRetries < 3; $flushRetries++) {
//    $result = $rk->flush(10000);
//    if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
//        break;
//    }
//}
//
//// 如果发送失败，则抛出异常。如果不关心消息发送结果，可以捕获异常并忽略。
//if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
//    throw new \RuntimeException('Was unable to flush, messages might be lost!');
//}
////$a = $rk->flush(1000); // 等待消息发送完成
//echo $result;