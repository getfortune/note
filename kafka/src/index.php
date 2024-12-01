<?php

use P\Kfaka\Producer\AdminProducer;

require_once "../vendor/autoload.php";

$producer = new AdminProducer();
$producer->newTopic('test1');
$producer->createMessage('test1', '测试admin');