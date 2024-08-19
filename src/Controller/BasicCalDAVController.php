<?php

namespace App\Controller;

use App\Command\ConsumerCommand;
use Firebase\JWT\JWT;
use App\Security\User;
use App\Entity\EventDto;
use App\Entity\userDto;
use App\HttpTools;
use App\JwtTool;
use App\Kafka;
use Enqueue\Client\ProducerInterface;
use EventProcessor;
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

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/events/{calID}', name: 'baikal_events', methods: ['POST'])]
    public function getEvents(
        string $calID,
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        /** @var App\Security\User */
        $user = $this->getUser();

        $event = (new EventDto())
            ->setDateStart($request->request->get('date_start'))
            ->setDateEnd($request->request->get('date_end'))
            ->setSummary($request->request->get('summary', 'ginov test list event'));

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            return $this->json($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST);
        }

        if ($event->getDateEnd() <= $event->getDateStart())
            return $this->json('Invalide date', Response::HTTP_BAD_REQUEST);

        // Get all events for baikal ------------------------
        $client = $this->doConnect(
            $this->getParameter('baikal.srv.url') . $user->getUsername() . '/' . $user->getCalCollectionName(),
            $user->getUsername(),
            $user->getPassword()
        );

        // get all calendars on server
        $calendars = $client->findCalendars();
        $client->setCalendar($calendars[$calID]);

        $events = $client->getEvents(
            EventDto::formatDate($event->getDateStart()),
            EventDto::formatDate($event->getDateEnd())
        );
        //---------------------------------------------------

        return $this->json([
            'cal_id' => $calID,
            'events' => $events,
            'token' => $user->getUserIdentifier()
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/calendars', name: 'baikal_calendars', methods: ['GET'])]
    public function getCalendars(): JsonResponse
    {
        /** @var App\Security\User */
        $user = $this->getUser();

        //getcalendar------------------------
        $client = $this->doConnect(
            $this->getParameter('baikal.srv.url') . $user->getUsername() . '/' . $user->getCalCollectionName(),
            $user->getUsername(),
            $user->getPassword()
        );

        // get all calandars on server
        $calendars = $client->findCalendars();
        //-------------------------------------

        return $this->json([
            'calendars' => $calendars,
            'token' => $user->getUserIdentifier()
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'access denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/add/{calID}', 'baikal_add', methods: ['POST'])]
    public function addEvent(
        string $calID,
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        ProducerInterface $producer
    ): JsonResponse {
        $event = (new EventDto())
            ->setUid(md5(time()))
            ->setDateStart($request->request->get('date_start'))
            ->setDateEnd($request->request->get('date_end'))
            ->setSummary($request->request->get('summary', ''))
            ->setTimeZoneID($request->request->get('timezone', 'Europe/Berlin'));

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            return $this->json($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST);
        }

        if ($event->getDateEnd() <= $event->getDateStart())
            return $this->json('Invalide date', Response::HTTP_BAD_REQUEST);

        /** @var App\Security\User */
        $user = $this->getUser();

        // add event for baikal ------------
        $client = $this->doConnect(
            $this->getParameter('baikal.srv.url') . $user->getUsername() . '/' . $user->getCalCollectionName(),
            $user->getUsername(),
            $user->getPassword()
        );
        $arrayOfCalendars = $client->findCalendars();
        $client->setCalendar($arrayOfCalendars[$calID]);
        //add event
        $newEventOnServer = $client->create($event);
        //----------------------------------

        // add kafka topic
        // $kafka = (new Kafka())->send('Enqueue bundle test 1');
        // $producer->sendEvent(EventProcessor::DEFAULT_TOPIC, 'Enqueue bundle test 1');

        // send command to ONE consumer
        // $producer->sendCommand('event_processor', 'Something has happened');

        return $this->json([
            'cal_id' => $calID,
            'event' => $newEventOnServer,
            'token' => $user->getUserIdentifier()
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'baikal_login', methods: ['POST'])]
    public function login(
        Request $request,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {

        $userDto = (new user())
            ->setUsername($request->request->get('username'))
            ->setPassword($request->request->get('password'))
            ->setCalCollectionName($request->request->get('cal_name'));

        $errors = $validator->validate($userDto);
        if (count($errors) > 0) {
            return $this->json($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST);
        }

        // get all calendars on server for baikal ----------------
        $client = $this->doConnect(
            $this->getParameter('baikal.srv.url') . $userDto->getUsername() . '/' . $userDto->getCalCollectionName(),
            $userDto->getUsername(),
            $userDto->getPassword()
        );
        $calendars = $client->findCalendars();
        //--------------------------------------------------------

        // gen jwt token
        $jwt = JwtTool::encode($this->getParameter('jwt.api.key'), $userDto);

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
