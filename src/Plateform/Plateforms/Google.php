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

    public function calendars(string $credentials): array
    {

        $calendars = (new HttpTools($this->srvUrl))
            ->get('users/me/calendarList', [], [
                'Authorization' => "Bearer " . $credentials
            ])
            ->json();

        return $calendars;
    }

    public function events(string $credentials, string $idCal): array
    {
        $events = (new HttpTools($this->calDAVUrl, $this->certPath))
            ->get("$idCal/events", [], [
                "Content-Type" => "application/json",
                'Authorization' => "Bearer " . $credentials
            ])
            ->brut()
            ->getBody();

        return $this->parse((string)$events);
    }

    /**
     * Undocumented function
     *
     * @param string $user
     * @param CalDAVEvent $event
     * @return CalDAVEvent
     */
    public function createEvent(string $credentails, CalDAVEvent $event):CalDAVEvent
    {
        return new CalDAVEvent();
    }

    public function createCalendar(string $credentials, string $name, string $description, string $displayName = '')
    {
        
    }    

    private static function parse($icalendarData): array
    {
        $vcalendar = Reader::read($icalendarData);

        $results = [];

        foreach ($vcalendar->VEVENT as $event) {

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

        return $results;
    }
}
