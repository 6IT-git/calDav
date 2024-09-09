<?php
namespace App\Entity;

use DateTime;
use DateTimeZone;
use Symfony\Component\Validator\Constraints as Assert;

class EventDto{

    const DEFAULT_TIME_ZONE = 'Europe/Berlin';

    private string $timeZoneID;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\DateTime]
    private string $dateStart;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\DateTime]
    private string $dateEnd;
    
    private string $uid;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 4, max: 255,
        minMessage: 'Summary must be at least {{ limit }} characters long',
        maxMessage: 'Summary cannot be longer than {{ limit }} characters',
    )]
    private string $summary;

    private string $description;

    private string $location;

    private string $rrule;

    

    #[Assert\DateTime]
    private string $createAt;
    

    public function __construct()
    {
        $this->createAt = (new DateTime())->format('Y-m-d H:i:s');
        $this->timeZoneID = self::DEFAULT_TIME_ZONE;
        $this->uid = md5(time());
        $this->description = '';
        $this->location = '';
        $this->rrule = '';
    }

    /**
     * @return string
     */
    public function getDateStart(): string
    {
        return $this->dateStart;
    }

    /**
     * @param string $dateStart
     * @return self
     */
    public function setDateStart(string $dateStart): self
    {
        $this->dateStart = $dateStart;

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

    /**
     * @param string $dateEnd
     * @return self
     */
    public function setDateEnd(string $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * Set the value of event uid
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

    /**
     * Set event created date
     *
     * @param string|null $createAt
     * @return self
     */
    public function setCreateAt(?string $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * Get event timezone
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

    /**
     * Get the value of description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of location
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @param string $location
     *
     * @return self
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of rrule
     *
     * @return string
     */
    public function getRrule(): string
    {
        return $this->rrule;
    }

    /**
     * Set the value of rrule
     *
     * @param string $rrule
     *
     * @return self
     */
    public function setRrule(string $rrule): self
    {
        $this->rrule = $rrule;

        return $this;
    }



    /**
     * @return string
     */
    public function __toString(): string
    {
        $start = self::formatDate($this->dateStart);
        $end = self::formatDate($this->dateEnd);
        $createAt = self::formatDate($this->createAt);

        return <<<EOD
        BEGIN:VCALENDAR
        PRODID:-//SomeExampleStuff//EN
        VERSION:2.0
        BEGIN:VTIMEZONE
        TZID:$this->timeZoneID
        X-LIC-LOCATION:$this->location
        BEGIN:DAYLIGHT
        TZOFFSETFROM:+0100
        TZOFFSETTO:+0200
        TZNAME:CEST
        DTSTART:$start
        RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
        END:DAYLIGHT
        BEGIN:STANDARD
        TZOFFSETFROM:+0200
        TZOFFSETTO:+0100
        TZNAME:CET
        DTSTART:$start
        RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
        END:STANDARD
        END:VTIMEZONE
        BEGIN:VEVENT
        CREATED:$createAt
        LAST-MODIFIED:20140403T091044Z
        DTSTAMP:20140416T091044Z
        UID:$this->uid
        SUMMARY:$this->summary
        DTSTART;TZID=$this->timeZoneID:$start
        DTEND;TZID=$this->timeZoneID:$end
        LOCATION:$this->location
        DESCRIPTION:$this->description
        END:VEVENT
        END:VCALENDAR
        EOD;
    }

    public static function formatDate(string $mDate){

        $date = new DateTime($mDate);

        // Convertir l'heure en UTC (si nécessaire)
        $date->setTimezone(new DateTimeZone('UTC'));

        // Formater la date et l'heure au format iCalendar (ISO 8601)
        $formattedDate = $date->format('Ymd\THis\Z');

        return $formattedDate; // Affiche quelque chose comme 20240809T101245Z
    }


    /**
     * Get the value of description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of location
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @param string $location
     *
     * @return self
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of rrule
     *
     * @return string
     */
    public function getRrule(): string
    {
        return $this->rrule;
    }

    /**
     * Set the value of rrule
     *
     * @param string $rrule
     *
     * @return self
     */
    public function setRrule(string $rrule): self
    {
        $this->rrule = $rrule;

        return $this;
    }
}