<?php

namespace App\Plateform\Plateforms;

use App\Security\User;
use SimpleCalDAVClient;
use App\Entity\EventDto;
use App\Plateform\CalDAVEvent;
use App\Plateform\Plateform;
use App\Plateform\PlateformUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ZimbraUser implements PlateformUserInterface
{

   private string $username;
   private string $password;
   private string $calID;

   /**
    * Get the value of username
    *
    * @return string
    */
   public function getUsername(): string
   {
      return $this->username;
   }

   /**
    * Set the value of username
    *
    * @param string $username
    * @return self
    */
   public function setUsername(string $username): self
   {
      $this->username = $username;

      return $this;
   }

   /**
    * Get the value of password
    *
    * @return string
    */
   public function getPassword(): string
   {
      return $this->password;
   }

   /**
    * Set the value of password
    *
    * @param string $password
    * @return self
    */
   public function setPassword(string $password): self
   {
      $this->password = $password;

      return $this;
   }

   /**
    * Get the value of calID
    *
    * @return string
    */
   public function getCalID(): string
   {
      return $this->calID;
   }

   /**
    * Set the value of calID
    *
    * @param string $calID
    * @return self
    */
   public function setCalID(string $calID): self
   {
      $this->calID = $calID;

      return $this;
   }

   public function __toString(): string
   {
      return $this->username . ';' . $this->password . ';' . $this->calID;
   }
}

class Zimbra extends Plateform
{

   /** @var SimpleCalDAVClient */
   private $client;

   public function __construct(ParameterBagInterface $parameter)
   {
      $this->srvUrl = $parameter->get('baikal.srv.url');
   }


   /**
    * Undocumented function
    *
    * @param Request $request
    * @return ZimbraUser
    */
   public function kokokoo(Request $request): PlateformUserInterface
   {
      /**@var BaikalUser $user */
      $user = (new BaikalUser())
         ->setUsername($request->request->get('username'))
         ->setPassword($request->request->get('password'))
         ->setCalID($request->request->get('cal_name'));

      return $user;
   }


   /**
    * Undocumented function
    *
    * @param PlateformUserInterface $user
    * @return array
    */
   public function calendars(string $credentials): array
   {
      return [];
   }



   public function events(string $credentials, string $calID): array
   {
      return [];
   }


   public function createEvent(string $credentials, CalDAVEvent $event): CalDAVEvent
   {
      return new CalDAVEvent();
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

   public function createCalendar(string $credentials, string $name, string $description, string $displayName = '')
   {

      $user = self::parseCredentials($credentials);
      $username = $user->getUsername();
      $password = $user->getPassword();

      $url = $this->srvUrl . $username;

      // XML pour créer un nouveau calendrier
      $xmlData = '<?xml version="1.0" encoding="utf-8"?>
      <d:mkcol xmlns:d="DAV:" xmlns:cs="http://calendarserver.org/ns/">
        <d:set>
          <d:prop>
            <d:resourcetype>
              <d:collection/>
              <cs:calendar/>
            </d:resourcetype>
            <d:displayname>' . $displayName ?? $name . '</d:displayname>
            <cs:calendar-description>' . $description . '</cs:calendar-description>
            <cs:supported-calendar-component-set>
              <cs:comp name="VEVENT"/>
            </cs:supported-calendar-component-set>
          </d:prop>
        </d:set>
      </d:mkcol>';

      // Initialiser cURL
      $ch = curl_init();

      // Configurer les options de la requête cURL
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'MKCOL');
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
         'Content-Type: application/xml; charset=utf-8',
         'Depth: 1'
      ));

      // Exécuter la requête cURL
      $response = curl_exec($ch);

      // Vérifier si des erreurs se sont produites
      if (curl_errno($ch)) {
         echo 'Erreur cURL : ' . curl_error($ch);
      } else {
         // Afficher la réponse du serveur
         echo 'Réponse du serveur : ' . $response;
      }

      // Fermer la session cURL
      curl_close($ch);
   }

   private static function parseCredentials(string $credentials): ZimbraUser
   {
      $tmp = explode(';', $credentials);
      return (new ZimbraUser())
         ->setUsername($credentials[0])
         ->setPassword($credentials[1])
         ->setCalID($credentials[2]);
   }
}
