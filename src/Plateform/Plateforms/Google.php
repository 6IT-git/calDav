<?php

namespace App\Plateform\Plateforms;

use App\HttpTools;
use App\Security\User;
use App\Entity\EventDto;
use App\Plateform\Plateform;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Google extends Plateform
{
    private string $calDAVUrl;
    private string $certPath;

    public function __construct(ParameterBagInterface $parameter)
    {
        $this->srvUrl = $parameter->get('google.srv.url');
        $this->calDAVUrl = $parameter->get('google.caldav.url');
        $this->certPath = $parameter->get('certificate.path');
    }

    /**
     * Undocumented function
     *
     * @param string $apitoken
     * @return User
     */
    public function kokoko(string $password): User
    {
        return (new User())
            ->setUsername('goolge')
            ->setPassword($password)
            ->setCalCollectionName('google');
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return User
     */
    public function login(Request $request): User
    {
        $userDto = (new User())
            ->setUsername('goolge')
            ->setPassword($request->request->get('token'))
            ->setCalCollectionName('google');

        return $userDto;
    }


    /**
     * Undocumented function
     *
     * @param string $password
     * @return array
     */
    public function getCalendars(string $password): array
    {
        $calendars = (new HttpTools($this->srvUrl))
            ->get('users/me/calendarList', [], [
                'Authorization' => "Bearer " . $password
            ])
            ->json();


        // Get all calandars on server
        /* $calendars = (new HttpTools('https://www.googleapis.com/calendar/v3/'))
            ->get('users/me/calendarList', [], [
                'Authorization' => "Bearer " . $user->getPassword()
            ])
            ->json(); */

        return $calendars;
    }

    /**
     * Undocumented function
     *
     * @param string $idCal
     * @param string $password
     * @return array
     */
    public function getEvents(string $idCal, string $password): array
    {
        $events = (new HttpTools($this->calDAVUrl, $this->certPath))
            ->get("$idCal/events", [], [
                "Content-Type" => "application/json",
                'Authorization' => "Bearer " . $password
            ])
            ->brut();

        dd($events->getBody()->json());

        return [];
    }

    public function addEvent(string $username, string $password, string $calID, EventDto $event): EventDto
    {
        return new EventDto();
    }
}
