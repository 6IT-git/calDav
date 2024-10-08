<?php

namespace App\Controller;

use App\JwtTool;
use App\HttpTools;
use App\Entity\EventDto;
use App\Plateform\Entity\CalDAVEvent;
use App\Plateform\Entity\EventCalDAV;
use App\Plateform\Plateform;
use App\Plateform\Plateforms\Google;
use App\Plateform\Plateforms\GoogleUser;
use App\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApiController extends AbstractController
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    #[Route('/google', name: 'google_code', methods: ['GET'])]
    public function index(): JsonResponse
    {
        /** @var Google */
        $plateformInstance = Plateform::create('google', $this->params);

        return $this->json([
            'url' => urldecode($plateformInstance->getOAuthUrl()),
            'message' => 'In your browser go to url above'
        ], Response::HTTP_OK);
    }

    #[Route('/oauth2callback.php', name: 'google_callback', methods: ['GET'])]
    public function callback(Request $request): JsonResponse
    {
        $json = (new HttpTools('https://oauth2.googleapis.com'))
            ->post('/token', [
                'code' => $request->query->get('code'),
                'client_id' => $this->getParameter('google.client.id'),
                'client_secret' => $this->getParameter('google.client.secret'),
                'redirect_uri' => $this->getParameter('google.redirect.uri'),
                'grant_type' => 'authorization_code',
            ], ['Content-Type' => 'application/x-www-form-urlencoded'])
            ->json();

        return $this->json($json);
    }

    #[Route('/{plateform}/login', name: 'login', methods: ['POST'])]
    public function login(string $plateform, Request $request): JsonResponse
    {
        $plateformInstance = Plateform::create($plateform, $this->params);

        $credentials = $plateformInstance->kokokoo($request);

        $user = (new User)
            ->setCredentials($credentials->__toString());

        // gen jwt token
        $jwt = JwtTool::encode($this->getParameter('jwt.api.key'), $user);

        return $this->json([
            'token' => $jwt,
            'calendars' => $plateformInstance->calendars($credentials)
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/{plateform}/calendar/{cal_id}', name: 'api_calendar', methods: ['GET'])]
    public function getCalendar(string $plateform, string $cal_id): JsonResponse
    {
        /** @var \App\Security\User */
        $user = $this->getUser();

        $plateformInstance = Plateform::create($plateform, $this->params);

        return $this->json([
            'token' => 'token',
            'calendars' => $plateformInstance->calendar($user->getCredentials(), $cal_id)
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/{plateform}/calendars', name: 'api_calendars', methods: ['GET'])]
    public function getCalendars(string $plateform): JsonResponse
    {
        /** @var \App\Security\User */
        $user = $this->getUser();

        $plateformInstance = Plateform::create($plateform, $this->params);

        return $this->json([
            'token' => 'token',
            'calendars' => $plateformInstance->calendars($user->getCredentials())
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/{plateform}/events/{calID}/{limit}/{offset}', name: 'api_events', methods: ['GET'])]
    public function getEvents(string $plateform, string $calID, int $limit, int $offset = 0): JsonResponse
    {
        /** @var \App\Security\User */
        $user = $this->getUser();

        if ($limit <= $offset)
            return $this->json('Invalide interval date', Response::HTTP_BAD_REQUEST);

        $plateformInstance = Plateform::create($plateform, $this->params);

        $events = $plateformInstance->events($user->getCredentials(), $calID);

        return $this->json([
            'cal_id' => $calID,
            'events' => $events,
            'token' => $user->getUserIdentifier()
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'access denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/{plateform}/add/event/{calID}', 'api_add', methods: ['POST'])]
    public function addEvent(string $plateform, string $calID, Request $request, ValidatorInterface $validator, SerializerInterface $serializer): JsonResponse
    {
        /** @var App\Security\User */
        $user = $this->getUser();

        $event = (new EventCalDAV())
            ->setDateStart($request->request->get('date_start'))
            ->setDateEnd($request->request->get('date_end'))
            ->setSummary($request->request->get('summary', 'ginov test list event'));

        $errors = $validator->validate($event);

        if (count($errors) > 0) {
            $this->parseError($errors);
            return $this->json($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST);
        }

        if ($event->getDateEnd() <= $event->getDateStart())
            return $this->json('Invalide date', Response::HTTP_BAD_REQUEST);

        $plateformInstance = Plateform::create($plateform, $this->params);
        $newEventOnServer = $plateformInstance->createEvent($user->getCredentials(), $event);

        return $this->json([
            'cal_id' => $calID,
            'event' => $newEventOnServer,
            'token' => $user->getUserIdentifier()
        ], Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/{plateform}/add/calendar', 'api_add_cal', methods: ['POST'])]
    public function addCalendar(string $plateform, Request $request): JsonResponse
    {
        /** @var \App\Security\User */
        $user = $this->getUser();

        $plateformInstance = Plateform::create($plateform, $this->params);

        $calendar = $plateformInstance->createCalendar(
            $user->getCredentials(),
            $request->request->get('cal_name'),
            $request->request->get('cal_description'),
            $request->request->get('cal_display_name', ''),
            $request->request->get('cal_timezone')
        );

        return $this->json([
            'token' => 'token',
            'calendars' => $calendar
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/{plateform}/del/calendar', 'api_del_cal', methods: ['POST'])]
    public function delCalendar(string $plateform, Request $request): JsonResponse
    {
        /** @var \App\Security\User */
        $user = $this->getUser();

        $plateformInstance = Plateform::create($plateform, $this->params);

        $calendar = $plateformInstance->deleteCalendar(
            $user->getCredentials(),
            $request->request->get('cal_name')
        );

        return $this->json([
            'token' => 'token',
            'calendar' => []
        ], Response::HTTP_OK);
    }

    private function parseError(ConstraintViolationListInterface $errors): array
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $errorMessages;
    }
}
