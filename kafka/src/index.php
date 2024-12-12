<?php
namespace  P\Kfaka;
use P\Kfaka\Consumer\AdminConsumer;
use P\Kfaka\Producer\AdminProducer;
use RdKafka\Conf;
use RdKafka\Exception;
use RdKafka\Producer;

require_once "../vendor/autoload.php";

$topicName = '1111';
//$producer = new AdminProducer();
//$producer->newTopic($topicName);
//try {
//    $result = $producer->createMessage($topicName, '我生产了一条消息-consumer');
//    echo $result;
//} catch (Exception $e) {
//}
$consumer = new AdminConsumer();
$consumer->consumerTopic(function ($message) {
    echo '我收到了一条消息：' . $message . "\n";
    if ($message == '我生产了一条消息-consumer') {
        return true;
    } else {
        return false;
    }
}, $topicName);

//$consumer->subscribeTopic(function ($message) {
//    echo '我收到了一条消息：' . $message;
//    if ($message == '我生产了一条消息') {
//        return true;
//    } else {
//        return false;
//    }
//}, [$topicName]);

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