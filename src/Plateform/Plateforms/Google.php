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
        $this->srvUrl = $parameter->get('baikal.srv.url');
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

    /**
     * get all calendars
     *
     * @return array
     */
    public function getCalendars(): array
    {
        $calendars = (new HttpTools('https://www.googleapis.com/calendar/v3'))
        ->get('/users/me/calendarList', [], [
           'Authorization' => "Bearer " . $this->token
        ])
        ->json();

        return $calendars;
    }

    /**
     * get all events
     *
     * @param string $idCal
     * @param EventDto $event
     * @return array
     */
    public function getEvents(string $idCal, EventDto $event): array
    {
        return [];
    }

    /**
     * add new event
     *
     * @param string $calID
     * @param EventDto $event
     * @return string
     */
    public function addEvent(string $calID, EventDto $event): string
    {
        return '';
    }
}
