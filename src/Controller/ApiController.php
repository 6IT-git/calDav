<?php

namespace App\Controller;

use App\JwtTool;
use App\Security\User;
use App\Entity\EventDto;
use App\PlateformInterface;
use App\Plateform\Plateform;
use Ramsey\Uuid\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApiController extends AbstractController
{
    #[Route('/caldav', name: 'app_api')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/{plateform}/login', name: 'login', methods: ['POST'])]
    public function login(string $plateform, Request $request, ParameterBagInterface $params): JsonResponse
    {
        $plateformInstance = Plateform::create($plateform, $params);

        $userDto = $plateformInstance->login($request);

        // gen jwt token
        $jwt = JwtTool::encode($this->getParameter('jwt.api.key'), $userDto);

        return $this->json([
            'token' => $jwt,
            'calendars' => $plateformInstance->getCalendars(
                $userDto->getUsername(), 
                $userDto->getPassword()
            ),
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/{plateform}/calendars', name: 'api_calendars', methods: ['GET'])]
    public function getCalendars(string $plateform, ParameterBagInterface $params): JsonResponse
    {
        /** @var App\Security\User */
        $user = $this->getUser();

        $plateformInstance = Plateform::create($plateform, $params);

        $calendars = $plateformInstance->getCalendars($user->getUsername(), $user->getPassword());

        return $this->json([
            'token' => 'token',
            'calendars' => $calendars
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/{plateform}/events/{calID}', name: 'api_events', methods: ['POST'])]
    public function getEvents(#[MapRequestPayload] EventDto $event): JsonResponse
    {
        dd($event);

        if ($event->getDateEnd() <= $event->getDateStart())
            return $this->json('Invalide date', Response::HTTP_BAD_REQUEST);
        
        return $this->json([], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'access denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/{plateform}/event/{calID}/add', 'api_add', methods: ['POST'])]
    public function addEvent(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $event = (new EventDto())
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

        return $this->json([], Response::HTTP_CREATED);
    }

    /**
     * Undocumented function
     *
     * @param array $errors
     * @return array
     */
    private function parseError(array $errors):array
    {    
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $errorMessages;

    }
}
