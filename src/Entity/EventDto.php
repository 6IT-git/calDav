<?php
namespace App\Entity;

use DateTime;
use DateTimeZone;
use Symfony\Component\Validator\Constraints as Assert;

class EventDto{


    private string $timeZoneID = 'Europe/Berlin';

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\DateTime]
    private string $dateStart;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\DateTime]
    private string $dateEnd;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $uid;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 4, max: 255,
        minMessage: 'Your summary must be at least {{ limit }} characters long',
        maxMessage: 'Your summary cannot be longer than {{ limit }} characters',
    )]
    private string $summary;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\DateTime]
    private string $createAt;


    public function getDateStart(): string
    {
        return $this->dateStart;
    }

    public function setDateStart(string $dateStart): self
    {
        $this->dateStart = $this->formatDate($dateStart);

        return $this;
    }

    /**
     * Get the value of dateEnd
     *
     * @return string
     */
    public function getDateEnd(): string
    {
        return $this->dateEnd;
    }

    public function setDateEnd(string $dateEnd): self
    {
        $this->dateEnd = $this->formatDate($dateEnd);

        return $this;
    }

    /**
     * Get the value of uid
     *
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * Set the value of uid
     *
     * @param string $uid
     *
     * @return self
     */
    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get the value of summary
     *
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * Set the value of summary
     *
     * @param string $summary
     *
     * @return self
     */
    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get the value of createAt
     *
     * @return string
     */
    public function getCreateAt(): string
    {
        return $this->createAt;
    }

    public function setCreateAt(string $createAt): self
    {
        $this->createAt = $this->formatDate($createAt);

        return $this;
    }

    /**
     * Get the value of timeZoneID
     *
     * @return string
     */
    public function getTimeZoneID(): string
    {
        return $this->timeZoneID;
    }

    /**
     * Set the value of timeZoneID
     *
     * @param string $timeZoneID
     *
     * @return self
     */
    public function setTimeZoneID(string $timeZoneID): self
    {
        $this->timeZoneID = $timeZoneID;

        return $this;
    }

    public function __toString(): string
    {
        return <<<EOD
        BEGIN:VCALENDAR
        PRODID:-//SomeExampleStuff//EN
        VERSION:2.0
        BEGIN:VTIMEZONE
        TZID:$this->timeZoneID
        X-LIC-LOCATION:$this->timeZoneID
        BEGIN:DAYLIGHT
        TZOFFSETFROM:+0100
        TZOFFSETTO:+0200
        TZNAME:CEST
        DTSTART:$this->dateStart
        RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
        END:DAYLIGHT
        BEGIN:STANDARD
        TZOFFSETFROM:+0200
        TZOFFSETTO:+0100
        TZNAME:CET
        DTSTART:$this->dateStart
        RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
        END:STANDARD
        END:VTIMEZONE
        BEGIN:VEVENT
        CREATED:$this->createAt
        LAST-MODIFIED:20140403T091044Z
        DTSTAMP:20140416T091044Z
        UID:$this->uid
        SUMMARY:$this->summary
        DTSTART;TZID=$this->timeZoneID:$this->dateStart
        DTEND;TZID=$this->timeZoneID:$this->dateEnd
        LOCATION:ExamplePlace2
        DESCRIPTION:$this->summary
        END:VEVENT
        END:VCALENDAR
        EOD;
    }

    private function formatDate(string $mDate){

        $date = new DateTime($mDate);

        // Convertir l'heure en UTC (si nécessaire)
        $date->setTimezone(new DateTimeZone('UTC'));

        // Formater la date et l'heure au format iCalendar (ISO 8601)
        $formattedDate = $date->format('Ymd\THis\Z');

        return $formattedDate; // Affiche quelque chose comme 20240809T101245Z
    }

}