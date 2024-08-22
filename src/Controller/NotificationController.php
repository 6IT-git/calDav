<?php

namespace App\Controller;

use App\Kafka;
use App\HttpTools;
use GuzzleHttp\Client;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{
    #[Route('/notify', name: 'notification_token', methods: ['GET'])]
    public function notify(): JsonResponse
    {
        // add kafka topic
        /* $kafka = (new Kafka())->send('Enqueue bundle test 1');

        return $this->json([]); */

        $token = $this->getToken();
        $payload = [
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

        $client = new Client([
            'verify' => false
        ]);

        $response = $client->post('https://apitest.viabber.com:8003/api/notification', [
            'json' => $payload,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token['access_token']
            ],
        ]);
        
        // Récupère le corps de la réponse      
        $body = $response->getBody();

        return $this->json(json_decode($body, true));
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
