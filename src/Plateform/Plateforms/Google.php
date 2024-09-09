<?php

namespace App\Plateform\Plateforms;

use stdClass;
use CalDAVObject;
use App\HttpTools;
use om\IcalParser;
use App\Security\User;
use App\Entity\EventDto;
use Sabre\VObject\Reader;
use App\Plateform\Plateform;
use App\Plateform\CalDAVEvent;
use App\Plateform\PlateformUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class GoogleUser implements PlateformUserInterface{
    
    private string $token;

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getToken():string
    {
        return $this->token;
    }

    public function __toString(): string
    {
        return $this->token;
    }
}

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

    public function kokokoo(Request $request): PlateformUserInterface
    {
        /**@var GoogleUser $user */
        $user = (new GoogleUser())
            ->setToken($request->request->get('token'));

        return $user;
    }

    public function calendars(PlateformUserInterface $user): array
    {
        /** @var GoogleUser */
        $user = $user;

        // dd($user->getToken());

        $calendars = (new HttpTools($this->srvUrl))
            ->get('users/me/calendarList', [], [
                'Authorization' => "Bearer " . $user->getToken()
            ])
            ->json();

        return $calendars;
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

        return $calendars;
    }

    public function events(PlateformUserInterface $user): array
    {
        return [];
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
            ->brut()
            ->getBody();

        return $this->parse((string)$events);
    }

    /**
     * Undocumented function
     *
     * @param PlateformUserInterface $user
     * @param CalDAVEvent $event
     * @return CalDAVEvent
     */
    public function createEvent(PlateformUserInterface $user, CalDAVEvent $event):CalDAVEvent
    {
        return new CalDAVEvent();
    }

    private static function parse($icalendarData): array
    {
        $vcalendar = Reader::read($icalendarData);

        $results = [];

        foreach ($vcalendar->VEVENT as $event) {

            // $event = $tmp->serialize();

            $results[] = (new EventDto())
                ->setSummary($event->SUMMARY)
                ->setDescription($event->DESCRIPTION)
                ->setLocation($event->LOCATION)
                ->setDateStart($event->DTSTART)
                ->setDateEnd($event->DTEND)
                ->setTimeZoneID($vcalendar->VTIMEZONE->TZID)
                ->setRrule($event->RRULE)
                ->setUid($event->UID);
        }
        // dd($result);
        return $results;
    }
}
