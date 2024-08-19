<?php
use Interop\Queue\Message;
use Interop\Queue\Context;
use Interop\Queue\Processor;
use Enqueue\Client\TopicSubscriberInterface;

class EventProcessor implements Processor, TopicSubscriberInterface
{
    const DEFAULT_TOPIC = 'caldav_events';

    public function process(Message $message, Context $session)
    {
        echo 'toto';
        dd($message);
        echo $message->getBody();

        return self::ACK;
        // return self::REJECT; // when the message is broken
        // return self::REQUEUE; // the message is fine but you want to postpone processing
    }

    public static function getSubscribedTopics()
    {
        return [self::DEFAULT_TOPIC];
    }
}
