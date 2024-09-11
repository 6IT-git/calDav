<?php

namespace App\Plateform\Plateforms;

use stdClass;
use CalDAVObject;
use App\HttpTools;
use om\IcalParser;
use App\Security\User;
use App\Entity\EventDto;
use App\Plateform\Entity\CalDAVCalendar;
use Sabre\VObject\Reader;
use App\Plateform\Plateform;
use App\Plateform\Entity\CalDAVEvent;
use App\Plateform\Entity\CalendarCalDAV;
use App\Plateform\Entity\EventCalDAV;
use App\Plateform\PlateformUserInterface;
use CalDAVCalendar as GlobalCalDAVCalendar;
use Symfony\Component\HttpFoundation\Request;
use Sabre\HTTP\Request as Http;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class GoogleUser implements PlateformUserInterface
{

    private string $token;

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getToken(): string
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

    /* private string $scope;
    private string $redirectUri;
    private string $clientID; */

    public function __construct(private readonly ParameterBagInterface $parameters)
    {
        $this->srvUrl = $parameters->get('google.srv.url');
        $this->calDAVUrl = $parameters->get('google.caldav.url');
        // $this->certPath = $parameter->get('certificate.path');

        /* $this->scope = $parameters->get('scope');
        $this->redirectUri = $parameters->get('google.redirect.uri');
        $this->clientID = $parameters->get('google.redirect.uri'); */
    }

    public function getOAuthUrl(): string
    {
        return "https://accounts.google.com/o/oauth2/v2/auth?scope=" .
            $this->parameters->get('google.scope') . "&access_type=offline&include_granted_scopes=true&response_type=code&redirect_uri=" .
            $this->parameters->get('google.redirect.uri') . "&client_id=" .
            $this->parameters->get('google.client.id');
    }

    public function kokokoo(Request $request): PlateformUserInterface
    {
        /**@var GoogleUser $user */
        $user = (new GoogleUser())
            ->setToken($request->request->get('token'));

        return $user;
    }

    public function calendar(string $credentials, string $calID): CalendarCalDAV
    {
        $client = new \Sabre\HTTP\Client();
        
        $client->addCurlSetting(CURLOPT_SSL_VERIFYHOST, 0);
        $client->addCurlSetting(CURLOPT_SSL_VERIFYPEER, 0);

        $request = new Http('GET', $this->srvUrl."calendars/$calID", [
            'Authorization' => 'Bearer ' . $credentials
        ]);
        
        $response = $client->send($request);

        $json = json_decode($response->getBodyAsString(), true);
        dd($json);

        return (new CalendarCalDAV($calID));
    }

    public function xxcalendar(string $credentials, string $calID): CalendarCalDAV
    {
        return (new HttpTools($this->srvUrl))
            ->get($calID, [], [
                'Authorization' => "Bearer " . $credentials
            ])
            ->json();

        return $calendars;
    }

    public function calendars(string $credentials): array
    {
        $client = new \Sabre\HTTP\Client();
        
        $client->addCurlSetting(CURLOPT_SSL_VERIFYHOST, 0);
        $client->addCurlSetting(CURLOPT_SSL_VERIFYPEER, 0);

        $request = new Http('GET', $this->srvUrl.'users/me/calendarList', [
            'Authorization' => 'Bearer ' . $credentials
        ]);
        
        $response = $client->send($request);

        /** @var array */
        $json = json_decode($response->getBodyAsString(), true);

        return $json;
    }

    public function xxcalendars(string $credentials): array
    {
        $calendars = (new HttpTools($this->srvUrl))
            ->get('users/me/calendarList', [], [
                'Authorization' => "Bearer " . $credentials
            ])
            ->json();

        return $calendars;
    }

    public function createCalendar(string $credentials, string $calID, string $description, string $displayName = '') {}

    public function deleteCalendar(string $credentials, string $calID) {}

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

    public function createEvent(string $credentials, EventCalDAV $event): EventCalDAV
    {
        return new EventCalDAV();
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
