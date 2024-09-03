<?php

namespace App\Plateform\Plateforms;

use App\Security\User;
use SimpleCalDAVClient;
use App\Entity\EventDto;
use App\Plateform\Plateform;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Baikal extends Plateform{

    /** @var SimpleCalDAVClient */
    private $client;

    public function __construct(ParameterBagInterface $parameter){
        $this->srvUrl = $parameter->get('baikal.srv.url');
    }

    public function kokoko(string $password, string $username='baikal', string $calID='baikal'): User
    {
        return (new User())
            ->setUsername($username)
            ->setPassword($password)
            ->setCalCollectionName($calID);
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

        return $userDto;
    }

    /**
     * Undocumented function
     *
     * @param string $password
     * @param string $username
     * @param integer $offset
     * @param integer $limit
     * @return array
     */
    public function getCalendars(string $password, string $username='baikal', int $offset=0, int $limit=20):array
    {

        $calendars = [];

        $client = $this->doConnect($username, $password);
        
        $results = $client->findCalendars();

        foreach($results as $result){
            $calendars[] = $result;
        }

        return $calendars;
    }
    

    public function getEvents(string $idCal, string $password, string $username='baikal', int $dateStart= 0, int $dateEnd=20):array
    {

        $client = $this->doConnect($username, $password);

        $calendars = $client->findCalendars();
        $client->setCalendar($calendars[$idCal]);

        $results = $client->getEvents(
            EventDto::formatDate(date('Y-m-d H:i:s', $dateStart)),
            EventDto::formatDate(date('Y-m-d H:i:s', $dateEnd))
        );

        $events = [];

        foreach($results as $result){
            $events[] = $result;
        }

        return $events;
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
    public function addEvent(string $username, string $password, string $calID, EventDto $event):EventDto
    {
        $client = $this->doConnect($username, $password);

        $arrayOfCalendars = $client->findCalendars();
        $client->setCalendar($arrayOfCalendars[$calID]);
        
        $event = $client->create($event);

        return $event->getData();
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