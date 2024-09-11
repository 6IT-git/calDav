<?php

namespace App\Plateform;

use App\Plateform\Entity\CalDAVCalendar as EntityCalDAVCalendar;
use App\Plateform\Entity\CalDAVEvent;
use App\Plateform\Entity\CalendarCalDAV;
use App\Plateform\Entity\EventCalDAV;
use App\Plateform\PlateformUserInterface;
use Symfony\Component\HttpFoundation\Request;

interface PlateformInterface
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return PlateformUserInterface
     */
    public function kokokoo(Request $request): PlateformUserInterface;

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @param string $calID
     * @return CalDAV
     */
    public function calendar(string $credentials, string $calID): CalendarCalDAV;

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @return array
     */
    public function calendars(string $credentials): array;

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @param string $calID
     * @param string $description
     * @param string $displayName
     * @return void
     */
    public function createCalendar(string $credentials, string $calID, string $description, string $displayName = '');

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @param string $calID
     * @return void
     */
    public function deleteCalendar(string $credentials, string $calID);    

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @param string $idCal
     * @return array
     */
    public function events(string $credentials, string $idCal): array;

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @param CalDAVEvent $event
     * @return EventCalDAV
     */
    public function createEvent(string $credentials, EventCalDAV $event):EventCalDAV;

}
