<?php

namespace App\Plateform;

use App\Entity\EventDto;

interface PlateformInterface{

    /**
     * login function
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function login(string $username, string $password):bool;

    /**
     * get all calendars
     *
     * @return array
     */
    public function getCalendars():array;

    /**
     * get all events
     *
     * @param string $idCal
     * @param EventDto $event
     * @return array
     */
    public function getEvents(string $idCal, EventDto $event):array;

    /**
     * add new event
     *
     * @param string $calID
     * @param EventDto $event
     * @return string
     */
    public function addEvent(string $calID, EventDto $event): string;

}