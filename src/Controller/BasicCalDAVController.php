<?php

namespace App\Controller;

use Firebase\JWT\JWT;
use App\Entity\User;
use App\Entity\EventDto;
use App\HttpTools;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class BasicCalDAVController extends AbstractController
{

    #[IsGranted('ROLE_USER', message:'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/events', name: 'baikal_events', methods: ['GET'])]
    public function getEvents(): JsonResponse
    {
        /** @var App\Security\User */
        $user = $this->getUser();

        $client = $this->doConnect(
            $this->getParameter('baikal.srv.url').$user->getUsername().'/'.$user->getCalendar(),
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
            $this->getParameter('baikal.srv.url').$user->getUsername().'/'.$user->getCalendar(),
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
        $calID = $request->request->get('calID', '');

        $event = (new EventDto())
            ->setUid(md5(time()))
            ->setCreateAt($request->request->get('create_at', 'now'))
            ->setDateStart($request->request->get('date_start', ''))
            ->setDateEnd($request->request->get('date_end', ''))
            ->setSummary($request->request->get('summary', ''))
            ->setTimeZoneID($request->request->get('timezone', 'Europe/Berlin'));

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            return $this->json($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST);
        }

        if($event->getDateEnd() <= $event->getDateStart())
            return $this->json('Invalide date', Response::HTTP_BAD_REQUEST);

        //normalement il faut aussi tester le format
        if(strlen(trim($calID)) == 0) 
            return $this->json(["Bad calID"], Response::HTTP_BAD_REQUEST);

        /** @var App\Security\User */
        $user = $this->getUser();

        $client = $this->doConnect(
            $this->getParameter('baikal.srv.url').$user->getUsername().'/'.$user->getCalCollectionName(),
            $user->getUsername(), 
            $user->getPassword()
        );        

        $arrayOfCalendars = $client->findCalendars();
        
        $client->setCalendar($arrayOfCalendars[$calID]);

        //add event
        $newEventOnServer = $client->create($event);

        // get kafka api token
        $response = (new HttpTools($this->getParameter('key.cloak.url')))
            ->post('/realms/nest-example/protocol/openid-connect/token', [
                'grant_type'=>'password', 
                'scope'=>'openid', 
                'username'=>'user', 
                'password'=>'user', 
                'client_id'=>'nest-api', 
                'client_secret'=>'05c1ff5e-f9ba-4622-98e3-c4c9d280546e'
            ])
            ->json();
        dd($response);

        // add kafka topic

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
            $this->getParameter('baikal.srv.url').$userDto->getUsername().'/'.$userDto->getCalCollectionName(),
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

}
