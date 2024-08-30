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

    /**
     * Login
     * 
     * @param Request $request
     * @return User
     */
    public function login(Request $request):User
    {
        $userDto = (new User())
            ->setUsername($request->request->get('username'))
            ->setPassword($request->request->get('password'))
            ->setCalCollectionName($request->request->get('cal_name'));
        
        // $this->client = $this->doConnect($userDto->getUsername(), $userDto->getPassword());

        return $userDto;
    }

    /**
     * Undocumented function
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    public function getCalendars(string $username, string $password, int $offset=0, int $limit=20):array
    {

        $calendars = [];

        $client = $this->doConnect($username, $password);
        
        $results = $client->findCalendars();

        foreach($results as $result){
            $calendars[] = $result;
        }

        return $calendars;
    }

    /**
     * Undocumented function
     *
     * @param string $username
     * @param string $password
     * @param string $calID
     * @param EventDto $event
     * @return string
     */
    public function addEvent(string $username, string $password, string $calID, EventDto $event):string
    {
        $client = $this->doConnect($username, $password);

        $arrayOfCalendars = $this->client->findCalendars();
        $client->setCalendar($arrayOfCalendars[$calID]);
        
        $result = $client->create($event);

        return $result->__toString();
    }

    /**
     * Undocumented function
     *
     * @param string $username
     * @param string $password
     * @param string $calID
     * @param EventDto $event
     * @return array
     */
    public function getEvents(string $username, string $password, string $calID, EventDto $event):array
    {

        $calendars = $this->client->findCalendars();
        $this->client->setCalendar($calendars[$calID]);

        $results = $this->client->getEvents(
            EventDto::formatDate($event->getDateStart()),
            EventDto::formatDate($event->getDateEnd())
        );

        return [];
    }

    
    private function _____doConnect(string $username, string $password)
    {
        if(!$this->client){
            $this->client = new SimpleCalDAVClient();
            $this->client->connect($this->srvUrl, $username, $password);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $username
     * @param string $password
     * @return SimpleCalDAVClient
     */
    private function doConnect(string $username, string $password): SimpleCalDAVClient
    {
        $client = new SimpleCalDAVClient();
        $client->connect($this->srvUrl, $username, $password);
        return $client;
    }

}