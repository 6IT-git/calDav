<?php

namespace App;

use SimpleCalDAVClient;
use App\Entity\EventDto;
use App\PlateformInterface;

class Baikal implements PlateformInterface{

    const SRV_URL = 'http://localhost:8001/cal.php/calendars/';

    /** @var SimpleCalDAVClient */
    private $client;

    public function __construct(string $username, string $password){
        $this->client = (new SimpleCalDAVClient())->connect(self::SRV_URL, $username, $password);
    }

    private function doConnect(string $username, string $password): SimpleCalDAVClient
    {
        $client = new SimpleCalDAVClient();
        $client->connect(self::SRV_URL, $username, $password);
        return $client;
    }

    public function login(string $username, string $password):bool
    {
        return true;
    }

    public function addEvent(string $calID, EventDto $event):string{

        $arrayOfCalendars = $this->client->findCalendars();
        $this->client->setCalendar($arrayOfCalendars[$calID]);

        $this->client->create($event);
        return '';
    }

    public function getCalendars():array
    {
        $result = $this->client->findCalendars();

        return [];
    }

    public function getEvents(string $calID, EventDto $event):array
    {

        $calendars = $this->client->findCalendars();
        $this->client->setCalendar($calendars[$calID]);

        $results = $this->client->getEvents(
            EventDto::formatDate($event->getDateStart()),
            EventDto::formatDate($event->getDateEnd())
        );

        return [];
    }

}