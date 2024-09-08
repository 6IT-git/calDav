<?php

namespace App;

use Enqueue\RdKafka\RdKafkaConnectionFactory;

class Kafka
{

    const DEFAULT_TOPIC = 'caldav_events';

    private $context;

    public function __construct()
    {

        $connectionFactory = new RdKafkaConnectionFactory([
            'global' => [
                'group.id' => uniqid('', true),
                'metadata.broker.list' => 'ec2-13-51-154-148.eu-north-1.compute.amazonaws.com:29092',
                'enable.auto.commit' => 'false',
            ],
            'topic' => [
                'auto.offset.reset' => 'beginning',
            ],
        ]);

        $this->context = $connectionFactory->createContext();
    }

    public function send(string $message)
    {
        $message = $this->context->createMessage($message);

        $topic = $this->context->createTopic(self::DEFAULT_TOPIC);

        $this->context->createProducer()->send($topic, $message);
    }

    public function cosume()
    {
        $fooQueue = $this->context->createQueue('foo');

        $consumer = $this->context->createConsumer($fooQueue);

        // Enable async commit to gain better performance (true by default since version 0.9.9).
        //$consumer->setCommitAsync(true);

        $message = $consumer->receive();

        // process a message

        $consumer->acknowledge($message);
        // $consumer->reject($message);
    }
}
