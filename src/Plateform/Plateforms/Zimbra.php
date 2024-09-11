<?php

namespace App\Plateform\Plateforms;

use Sabre\DAV\Client;
use App\Security\User;
use SimpleCalDAVClient;
use App\Entity\EventDto;
use App\Plateform\Plateform;
use App\Plateform\Entity\CalDAVEvent;
use App\Plateform\Entity\EventCalDAV;
use App\Plateform\Entity\CalendarCalDAV;
use App\Plateform\PlateformUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ZimbraTokenUser implements PlateformUserInterface
{
   private string $token;
   private string $calID;

   public function getSettings(): array
   {
      return [
         'settings' => [],
         'header' => [
            'Authorization' => 'Bearer ' . $this->getToken(),
            'Content-Type' => 'application/xml; charset=utf-8',
            'Depth' => 1
         ]
      ];
   }

   public function __toString(): string
   {
      return $this->token . ';' . $this->calID;
   }

   /**
    * Undocumented function
    *
    * @return string|null
    */
   public function getToken(): ?string
   {
      return $this->token;
   }

   /**
    * Undocumented function
    *
    * @param string $token
    * @return self
    */
   public function setToken(string $token): self
   {
      $this->token = $token;
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
}

class ZimbraUser implements PlateformUserInterface
{
   private string $username;
   private string $password;
   private string $calID;
   private string $token;

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
      /**@var ZimbraUser $user */
      $user = (new ZimbraUser())
         // ->setToken($request->request->get('token', null))
         ->setUsername($request->request->get('username'))
         ->setPassword($request->request->get('password'))
         ->setCalID($request->request->get('cal_name'));

      return $user;
   }

   public function calendar(string $credentials, string $calID): CalendarCalDAV
   {
      $user = $this->parseCredentials($credentials);

      $settings = $this->cfg($user);

      // Create the SabreDAV client
      $client = new Client($settings['settings']);

      return new CalendarCalDAV($calID);
   }

   /**
    * Undocumented function
    *
    * @param PlateformUserInterface $user
    * @return array
    */
   public function calendars(string $credentials): array
   {
      $user = self::parseCredentials($credentials);
      $username = $user->getUsername();
      $password = $user->getPassword();

      // URL du répertoire contenant les calendriers
      $requestUrl = $this->parseUrl([$username]);

      // dd($requestUrl);

      // Initialiser cURL
      $ch = curl_init();

      // Configurer les options de la requête cURL
      curl_setopt($ch, CURLOPT_URL, $requestUrl);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
         'Content-Type: application/xml; charset=utf-8',
         'Depth: 1'
      ]);

      // XML de la requête PROPFIND pour obtenir la liste des calendriers
      $propfindData = '<?xml version="1.0" encoding="utf-8" ?>
      <d:propfind xmlns:d="DAV:" xmlns:cs="urn:ietf:params:xml:ns:caldav">
        <d:prop>
          <d:displayname/>
          <cs:calendar-description/>
          <cs:supported-calendar-component-set/>
        </d:prop>
      </d:propfind>';

      curl_setopt($ch, CURLOPT_POSTFIELDS, $propfindData);

      // Exécuter la requête cURL
      $response = curl_exec($ch);

      if (curl_errno($ch))
         throw new \Exception(curl_error($ch));

      dd($response);

      // Parse la réponse XML
      $xml = simplexml_load_string($response);
      dd($xml);
      $json = json_encode($xml);

      // Afficher la réponse en JSON
      echo $json;

      // Fermer la session cURL
      curl_close($ch);
      return [];
   }

   public function events(string $credentials, string $calID): array
   {
      return [];
   }

   public function createEvent(string $credentials, EventCalDAV $event): EventCalDAV
   {
      return new EventCalDAV();
   }

   public function createCalendar(string $credentials, string $calID, string $description, string $displayName = '')
   {
      $user = self::parseCredentials($credentials);
      $username = $user->getUsername();
      $password = $user->getPassword();

      $displayName = $displayName ?? $calID;

      $url = $this->srvUrl . $username . '/' . $calID . '/';

      $xmlData = <<<EOD
      <?xml version="1.0" encoding="utf-8" ?>
      <C:mkcalendar xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
      <D:set>
         <D:prop>
            <D:displayname>$displayName</D:displayname>
            <C:calendar-description xml:lang="en">$description</C:calendar-description>
            <C:supported-calendar-component-set>
            <C:comp name="VEVENT"/>
            </C:supported-calendar-component-set>
            <C:calendar-timezone><![CDATA[BEGIN:VCALENDAR
      PRODID:-//Example Corp.//CalDAV Client//EN
      VERSION:2.0 
      BEGIN:VTIMEZONE
      TZID:US-Eastern
      LAST-MODIFIED:19870101T000000Z
      BEGIN:STANDARD
      DTSTART:19671029T020000
      RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
      TZOFFSETFROM:-0400
      TZOFFSETTO:-0500
      TZNAME:Eastern Standard Time (US & Canada)
      END:STANDARD
      BEGIN:DAYLIGHT
      DTSTART:19870405T020000
      RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4
      TZOFFSETFROM:-0500
      TZOFFSETTO:-0400
      TZNAME:Eastern Daylight Time (US & Canada)
      END:DAYLIGHT
      END:VTIMEZONE
      END:VCALENDAR
      ]]></C:calendar-timezone>
         </D:prop>
      </D:set>
      </C:mkcalendar>
      EOD;

      // Initialiser cURL
      $ch = curl_init();

      // Configurer les options de la requête cURL
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'MKCALENDAR');
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
         'Content-Type: application/xml; charset=utf-8',
         'Depth: 1'
      ));

      // Exécuter la requête cURL
      $response = curl_exec($ch);

      if (curl_errno($ch))
         throw new \Exception(curl_error($ch));

      curl_close($ch);

      return $response;
   }

   public function deleteCalendar(string $credentials, string $calID)
   {
      $user = self::parseCredentials($credentials);
      $username = $user->getUsername();
      $password = $user->getPassword();

      $url = $this->srvUrl . $username . '/' . $calID . '/';

      // Initialiser une session cURL
      $ch = curl_init();

      // Configurer les options cURL pour une requête DELETE
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
      curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

      // Exécuter la requête cURL
      $response = curl_exec($ch);

      if (curl_errno($ch))
         throw new \Exception(curl_error($ch));

      curl_close($ch);

      return $response;
   }


   /**
    * Undocumented function
    *
    * @param string $credentials
    * @return ZimbraUser
    */
   private static function parseCredentials(string $credentials): ZimbraUser
   {
      $tmp = explode(';', $credentials);

      return (\count($tmp) == 3)
         ? (new ZimbraUser())
            ->setUsername($tmp[0])
            ->setPassword($tmp[1])
            ->setCalID($tmp[2])
         : (new ZimbraTokenUser())
            ->setCalID($tmp[0])
            ->setToken($tmp[1]);
   }

   private function cfg(ZimbraUser $user)
   {
      /* return [
         'settings' => ($user->getToken()) ? [
            'Authorization' => 'Bearer ' . $user->getToken(),
            'Content-Type' => 'application/xml; charset=utf-8',
            'Depth' => 1,
         ] : [
            'baseUri' => 'http://example.org/dav/',
            'userName' => $user->getUsername(),
            'password' => $user->getPassword()
         ],

         'headers' => ($user->getToken()) ? [
            'Authorization' => 'Bearer ' . $user->getToken(),
            'Content-Type' => 'application/xml; charset=utf-8',
            'Depth' => 1
         ] : []
      ]; */
   }

   /**
    * Undocumented function
    *
    * @param array $parts
    * @return string
    */
   private function parseUrl(array $parts): string
   {
      $url = $this->srvUrl;
      foreach ($parts as $part) {
         $url .= urlencode($part) . '/';
      }

      return $url;
   }
}
