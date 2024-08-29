<?php

namespace App;

use App\HttpTools;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Enqueue\Client\TopicSubscriberInterface;

class EventProcessor implements Processor, TopicSubscriberInterface
{
    const DEFAULT_TOPIC = 'caldav_events';
    

    /**
     * @return string|object
     */
    public function process(Message $message, Context $session)
    {
        // Traitement du message
        $eventData = $message->getBody();

        $payload = $this->parsePayload($eventData);
        $token = $this->getToken();

        $response = (new HttpTools('https://apitest.viabber.com:8003/'))
            ->postJson('api/notification', $payload, [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token['access_token']
            ])
            ->json();
   

        return ($response) ? self::ACK : self::REQUEUE;
        // return ($response->getStatusCode() == 200) ? self::ACK : self::REQUEUE;
        // return self::REJECT; // when the message is broken
        // return self::REQUEUE; // the message is fine but you want to postpone processing
        //$ ./bin/console enqueue:consume --setup-broker -vvv
    }

    /**
     * @return string|array
     */    
    public static function getSubscribedTopics()
    {
        return [self::DEFAULT_TOPIC];
    }

    private function parsePayload($data):array
    {
        return [
            "eventId" => "ginov@".md5(time()),
            "type_event" => "agenda",
            "subType" => "calendar-invit-new",
            "to" => [
                "groups" => [],
                "members" => ["9137a8b2-bdaf-4fb5-a039-dc69e63fd99f"]
            ],
            "actions" => [
                [
                    "type" => "url",
                    "name" => "string",
                    "key" => "calendar-invit-new-action-yes",
                    "content" => "https://apitest.viabber.com:8003/api/subscriber",
                    "mode" => "open"
                ],
                [
                    "type" => "url",
                    "name" => "string",
                    "key" => "calendar-invit-new-action-no",
                    "content" => "https://apitest.viabber.com:8003/api/subscriber",
                    "mode" => "open"
                ],
                [
                    "type" => "url",
                    "name" => "string",
                    "key" => "calendar-invit-new-action-maybe",
                    "content" => "https://apitest.viabber.com:8003/api/subscriber",
                    "mode" => "open"
                ]
            ],
            "message" => [
                "variables" => ["event" => "Evenement agenda"]
            ],
            "sender" => [
                "idSender" => "5cc51b83-0860-4315-804a-12b14eb44c71"
            ]
        ];
    }

    private function getToken(): array
    {
        $token = (new HttpTools('https://login.dev1.dev-qa.interstis.fr/'))
            ->post('realms/nest-example/protocol/openid-connect/token', [
                'grant_type' => 'client_credentials',
                'client_id' => 'postman',
                'client_secret' => 'dtci7E4KRuSME7KEAvB1JAotBHgDqVgv'
            ])
            ->json();

        return $token;
    }
}
