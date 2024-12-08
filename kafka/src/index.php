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
        $conf->set('bootstrap.servers', 'localhost:9092');
        $this->producer  = new Producer($conf);
        $this->producer->addBrokers("localhost:9092");
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
        $conf->set('bootstrap.servers', 'localhost:9092');
        $this->producer = new \RdKafka\Producer($conf);
    }

    public function produce($topic, $message) {
        $this->producer->addBrokers("localhost:9092");
        $topica = $this->producer->newTopic($topic);
        $topica->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    }

    public function flush($timeout = 1000) {
        return $this->producer->flush($timeout);
    }
}

$admin = new KafkaProducer();
$admin->produce('test', '123123');
$a = $admin->flush(1);
echo $a;

// 下面这个是可以的
// 创建 Kafka 配置对象
//$conf = new Conf();
//
//// 设置 Kafka 集群的连接信息
//$conf->set('bootstrap.servers', 'localhost:9092'); // 使用 Docker Compose 服务名称连接 Kafka
//
//$rk  = new \RdKafka\Producer($conf);
//$rk->addBrokers("localhost:9092"); //kafka服务器地址
//$topic =  $rk->newTopic('2323221233');
//$value ='23333';
//$topic->produce(RD_KAFKA_PARTITION_UA, 0, $value);
//$a = $rk->flush(1000); // 等待消息发送完成
//echo $a;