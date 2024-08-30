<?php

namespace App\Plateform\Plateforms;

use App\HttpTools;
use App\Security\User;
use App\Entity\userDto;
use App\Entity\EventDto;
use App\Plateform\Plateform;
use App\plateform\PlateformInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Google extends Plateform
{

    private string $token;

    public function __construct(ParameterBagInterface $parameter){
        $this->srvUrl = $parameter->get('google.srv.url');
    }

    public function login(Request $request): User
    {
        $userDto = (new User())
            ->setUsername('goolge')
            ->setPassword($request->request->get('token'))
            ->setCalCollectionName('google');

        $this->token = $userDto->getPassword();

        return $userDto;
    }


    public function getCalendars(string $username, string $password): array
    {
        $calendars = (new HttpTools($this->srvUrl))
        ->get('/users/me/calendarList', [], [
           'Authorization' => "Bearer " . $this->token
        ])
        ->json();

        return $calendars;
    }

    public function getEvents(string $username, string $password, string $idCal, EventDto $event): array
    {
        $events = (new HttpTools($this->srvUrl))
           ->get("/calendars/$idCal/events", [], [
              'Authorization' => "Bearer " . $this->token
           ])
           ->json();
        return [];
    }

    public function addEvent(string $username, string $password, string $calID, EventDto $event): string
    {
        return '';
    }
}
