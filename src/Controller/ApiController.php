<?php

namespace App\Controller;

use App\Entity\UserDto;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    const JWT_SECRET_KEY = 'ginov';

    #[Route('/events', name: 'baikal_events', methods: ['GET'])]
    public function getEvents(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/calendars', name: 'baikal_calendars', methods: ['GET'])]
    public function getCalendars(Request $request): JsonResponse
    {

        //*!!!!code de merde à supprimer
        $decoded = $this->checkToken($request);

        $client = $this->doConnect(
            'http://localhost:8001/cal.php/calendars/'.$decoded[0]->username.'/'.$decoded[0]->calendar_name,
            $decoded[0]->username, 
            $decoded[0]->password
        );
        //*!!!!code de merde à supprimer

        // get all calandars on server
        $calendars = $client->findCalendars();

        return $this->json([
            'calendars' => $calendars,
            'token' => $decoded[1]
        ]);
    }

    #[Route('/add', 'baikal_add', methods: ['POST'])]
    public function addEvent(Request $request): JsonResponse
    {

        $event = $request->request->get('event', '');
        //normalement il faut aussi tester le format
        if(strlen(trim($event)) == 0) return $this->json(["Event wrong format"], Response::HTTP_BAD_REQUEST);

        //*!!!!code de merde à supprimer
        $decoded = $this->checkToken($request);

        $client = $this->doConnect(
            'http://localhost:8001/cal.php/calendars/'.$decoded[0]->username.'/'.$decoded[0]->calendar_name,
            $decoded[0]->username, 
            $decoded[0]->password
        );
        //*!!!!code de merde à supprimer

        //add event
        $newewEventOnServer = $client->create($event);

        var_dump($newewEventOnServer); die();

        return $this->json(
            ['event' => $event, 'token' => $decoded[1]], 
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'baikal_login', methods: ['POST'])]
    public function login(
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        $userDto = (new UserDto())
            ->setUsername($request->get('username', ''))
            ->setPassword($request->get('password', ''))
            ->setCalName($request->get('calName', ''));


        $errors = $validator->validate($userDto);
        if (count($errors) > 0) {
            return $this->json($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST);
        }

        // get calDAV client
        $client = $this->doConnect(
            'http://localhost:8001/cal.php/calendars/'.$userDto->getUsername().'/'.$userDto->getCalName(),
            $userDto->getUsername(),
            $userDto->getPassword()
        );

        // get all calandars on server
        $calendars = $client->findCalendars();

        // gen jwt token
        $jwt = JWT::encode([
            'username' => $userDto->getUsername(),
            'password' => $userDto->getPassword(),
            'calendar_name' => $userDto->getCalName(),
            'exp' => time()+10*60
        ], ApiController::JWT_SECRET_KEY, 'HS256');

        return $this->json([
            'calendars' => $calendars,
            'token' => $jwt
        ]);
    }

    private function doConnect(string $url, string $username, string $password): \SimpleCalDAVClient
    {
        $client = new \SimpleCalDAVClient();
        $client->connect($url, $username, $password);
        return $client;
    }

    //*********************************************
    //**** A DELETE ABSOLUMENT ALERT CODE MERDIK */
    //*******************************************
    private function checkToken(Request $request):array{
        
        if(!$request->headers->has('Authorization') || !str_contains($request->headers->get('Authorization'), 'Bearer ')){
            return $this->json(['Access denied'], Response::HTTP_UNAUTHORIZED);
        }
        $jwt = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        $decoded = JWT::decode($jwt, new Key(ApiController::JWT_SECRET_KEY, 'HS256'));

        return [$decoded, $jwt];
    }
    //*********************************************
    //**** A DELETE ABSOLUMENT ALERT CODE MERDIK */
    //*******************************************
}
