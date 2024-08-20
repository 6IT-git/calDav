<?php

namespace App\Plateform\Plateforms;

use App\Security\User;
use App\Entity\userDto;
use SimpleCalDAVClient;
use App\Entity\EventDto;
use App\Plateform\Plateform;
use App\Plateform\PlateformInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Baikal extends Plateform{

    /** @var SimpleCalDAVClient */
    private $client;

    public function __construct(ParameterBagInterface $parameter){
        $this->srvUrl = $parameter->get('baikal.srv.url');
    }

    public function login(Request $request):User
    {
        $userDto = (new User())
            ->setUsername($request->request->get('username'))
            ->setPassword($request->request->get('password'))
            ->setCalCollectionName($request->request->get('cal_name'));
        
        $this->client = (new SimpleCalDAVClient())->connect($this->srvUrl, $userDto->getUsername(), $userDto->getPassword());

        return $userDto;
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