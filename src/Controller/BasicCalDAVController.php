<?php

namespace App\Controller;

use App\Entity\EventDto;
use App\Security\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ApiController extends AbstractController
{

    const JWT_SECRET_KEY = 'ginov';


    #[IsGranted('ROLE_USER', message:'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/events', name: 'baikal_events', methods: ['GET'])]
    public function getEvents(): JsonResponse
    {
        /** @var App\Security\User */
        $user = $this->getUser();

        $client = $this->doConnect(
            'http://localhost:8001/cal.php/calendars/'.$user->getUsername().'/'.$user->getCalendar(),
            $user->getUsername(), 
            $user->getPassword
        );

        // get all calandars on server
        $calendars = $client->findCalendars();

        return $this->json([
            'message' => 'is building'
        ]);
    }

    #[IsGranted('ROLE_USER', message:'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/calendars', name: 'baikal_calendars', methods: ['GET'])]
    public function getCalendars(): JsonResponse
    {
        /** @var App\Security\User */
        $user = $this->getUser();

        $client = $this->doConnect(
            'http://localhost:8001/cal.php/calendars/'.$user->getUsername().'/'.$user->getCalendar(),
            $user->getUsername(), 
            $user->getPassword
        );

        // get all calandars on server
        $calendars = $client->findCalendars();

        return $this->json([
            'calendars' => $calendars,
            'token' => $user->getUserIdentifier()
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message:'access denied', statusCode:Response::HTTP_UNAUTHORIZED)]
    #[Route('/add', 'baikal_add', methods: ['POST'])]
    public function addEvent(Request $request, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        $event = $request->request->get('event', '');
        $calID = $request->request->get('calID', '');

        $event = (new EventDto())
            ->setUid(md5(time()))
            ->setCreateAt($request->request->get('date_start', 'now'))
            ->setDateStart($request->request->get('date_start', ''))
            ->setDateEnd($request->request->get('date_end', ''))
            ->setSummary($request->request->get('summary', ''))
            ->setTimeZoneID($request->request->get('timezone', 'Europe/Berlin'));


        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            return $this->json($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST);
        }

        //normalement il faut aussi tester le format
        if(strlen(trim($calID)) == 0) 
            return $this->json(["Bad calID"], Response::HTTP_BAD_REQUEST);

        /** @var App\Security\User */
        $user = $this->getUser();

        $client = $this->doConnect(
            'http://localhost:8001/cal.php/calendars/'.$user->getUsername().'/'.$user->getCalendar(),
            $user->getUsername(), 
            $user->getPassword
        );        

        $arrayOfCalendars = $client->findCalendars();
        
        $client->setCalendar($arrayOfCalendars[$calID]);

        //add event
        $newEventOnServer = $client->create($event);

        return $this->json(
            ['event' => $newEventOnServer, 'token' => $user->getUserIdentifier()], 
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'baikal_login', methods: ['POST'])]
    public function login(
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        /*$userDto = (new UserDto())
            ->setUsername($request->get('username', ''))
            ->setPassword($request->get('password', ''))
            ->setCalName($request->get('calName', ''));*/
        
        $userDto = (new User())
            ->setUsername($request->get('username', ''))
            ->setPassword($request->get('password', ''))
            ->setCalCollectionName($request->get('calName', ''));


        $errors = $validator->validate($userDto);
        if (count($errors) > 0) {
            return $this->json($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST);
        }

        // get calDAV client
        $client = $this->doConnect(
            'http://localhost:8001/cal.php/calendars/'.$userDto->getUsername().'/'.$userDto->getCalCollectionName(),
            $userDto->getUsername(),
            $userDto->getPassword()
        );

        // get all calandars on server
        $calendars = $client->findCalendars();

        // gen jwt token
        $jwt = JWT::encode([
            'username' => $userDto->getUsername(),
            'password' => $userDto->getPassword(),
            'calendar_name' => $userDto->getCalCollectionName(),
            'exp' => time()+60*60
        ], $this->getParameter('jwt.api.key'), $this->getParameter('jwt.encoder'));

        return $this->json([
            'calendars' => $calendars,
            'token' => $jwt
        ], Response::HTTP_OK);
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
            // return $this->json(['Access denied'], Response::HTTP_UNAUTHORIZED);
            throw new \Exception('Access denied');
        }
        $jwt = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        $decoded = JWT::decode($jwt, new Key(ApiController::JWT_SECRET_KEY, 'HS256'));

        return [$decoded, $jwt];
    }
    //*********************************************
    //**** A DELETE ABSOLUMENT ALERT CODE MERDIK */
    //*******************************************
}
