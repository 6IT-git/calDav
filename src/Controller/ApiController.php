<?php

namespace App\Controller;

use App\PlateformInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/caldav/{plateform}/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse 
    {
        return $this->json([], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/caldav/{plateform}/calendars', name: 'api_calendars', methods: ['GET'])]
    public function getCalendars(string $plateform): JsonResponse
    {
        /** @var App\Security\User */
        $user = $this->getUser();

        $classname = 'App\\'.ucfirst($plateform);

        /**  @var App\PlateformInterface */
        $plateformClass = new $classname($user->getUsername(), $user->getPassword());

        if($plateformClass instanceof PlateformInterface){
            throw new \Exception('plateform error');
        }

        $calendars = $plateformClass->getCalendars();

        dd($calendars);

        return $this->json([], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'Acces denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/events/{calID}', name: 'api_events', methods: ['POST'])]
    public function getEvents(): JsonResponse 
    {
        return $this->json([], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER', message: 'access denied', statusCode: Response::HTTP_UNAUTHORIZED)]
    #[Route('/add/{calID}', 'api_add', methods: ['POST'])]
    public function addEvent(): JsonResponse 
    {
        return $this->json([], Response::HTTP_CREATED);
    }
}
