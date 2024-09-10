<?php

namespace App\Plateform\Plateforms;

use Sabre\DAV\Client;
use App\Security\User;
use SimpleCalDAVClient;
use App\Entity\EventDto;
use App\Plateform\Plateform;
use App\Plateform\CalDAVEvent;
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
      /**@var ZimbraUser $user */
      $user = (new ZimbraUser())
         ->setUsername($request->request->get('username'))
         ->setPassword($request->request->get('password'))
         ->setCalID($request->request->get('cal_name'));

      return $user;
   }


   public function calendars(string $credentials): array
   {
      // Set up the CalDAV client
      $client = new \Sabre\DAV\Client([
         'baseUri' => 'http://localhost:8443/dav.php/calendars/chancel/',
         'userName' => 'chancel',
         'password' => 'chancelpass'
      ]);

      // Make the PROPFIND request to get the list of calendars
      $response = $client->propFind('', [
         '{DAV:}displayname',
         '{http://calendarserver.org/ns/}getctag',
         '{urn:ietf:params:xml:ns:caldav}calendar',
      ], 1);

      // Output the list of calendars
      foreach ($response as $calendarUrl => $props) {
         echo "Calendar URL: " . $calendarUrl . "\n";
         echo "Display Name: " . ($props['{DAV:}displayname'] ?? 'Unnamed') . "\n";
         echo "CTag: " . ($props['{http://calendarserver.org/ns/}getctag'] ?? 'No CTag') . "\n";
         echo "\n";
      }
      return $response;
   }

   public function __calendars(string $credentials): array
   {

      // Informations d'authentification
      $username = "chancel";
      $password = "chancelpass";

      // CalDAV server URL (à adapter)
      $caldav_url = "http://localhost:8443/dav.php/calendars/$username/";

      // cURL initialization
      $curl = curl_init();

      // Construire la requête PROPFIND
      $xml_request = <<<XML
      <?xml version="1.0" encoding="UTF-8"?>
      <d:propfind xmlns:d="DAV:" xmlns:cs="http://calendarserver.org/ns/">
        <d:prop>
          <d:displayname/>
          <cs:getctag/>
          <d:resourcetype/>
        </d:prop>
      </d:propfind>
      XML;

      // Configuration des options cURL
      curl_setopt_array($curl, array(
         CURLOPT_URL => $caldav_url,
         CURLOPT_CUSTOMREQUEST => "PROPFIND",
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,  // Authentification Digest
         CURLOPT_USERPWD => "$username:$password",
         CURLOPT_POSTFIELDS => $xml_request,
         CURLOPT_HTTPHEADER => array(
            "Depth: 1",  // Récupère les collections au niveau 1 (calendriers)
            "Content-Type: text/xml",
         ),
         CURLOPT_SSL_VERIFYPEER => false,  // Ignorer la vérification SSL pour l'environnement de test local
         CURLOPT_SSL_VERIFYHOST => false,
      ));

      // Exécuter la requête
      $response = curl_exec($curl);

      $this->koukaratcha($response);

      // Fermer la connexion cURL
      curl_close($curl);

      return [];
   }

   private function koukaratcha($response)
   {
      // Charger la réponse XML avec SimpleXML
      $xml = simplexml_load_string($response, null, LIBXML_NOCDATA);

      // Espaces de nom utilisés dans le XML
      $xml->registerXPathNamespace('d', 'DAV:');
      $xml->registerXPathNamespace('cal', 'urn:ietf:params:xml:ns:caldav');
      $xml->registerXPathNamespace('cs', 'http://calendarserver.org/ns/');

      // Parcourir chaque calendrier dans la réponse
      foreach ($xml->xpath('//d:response') as $response) {
         // Récupérer l'URL du calendrier (href)
         $href = (string) $response->xpath('d:href')[0];

         // Récupérer le displayname (nom du calendrier)
         $displayname = $response->xpath('d:propstat/d:prop/d:displayname');
         if (count($displayname) > 0) {
            $displayname = (string) $displayname[0];
         } else {
            $displayname = 'Nom non trouvé';
         }

         // Récupérer le ctag (getctag)
         $ctag = $response->xpath('d:propstat/d:prop/cs:getctag');
         if (count($ctag) > 0) {
            $ctag = (string) $ctag[0];
         } else {
            $ctag = 'CTag non trouvé';
         }

         // Afficher les informations
         echo "URL du calendrier : $href\n";
         echo "Nom du calendrier : $displayname\n";
         echo "CTag : $ctag\n\n";
      }
   }

   public function events(string $credentials, string $calID): array
   {
      return [];
   }


   public function createEvent(string $credentials, CalDAVEvent $event): CalDAVEvent
   {
      return new CalDAVEvent();
   }

   public function createCalendar(string $credentials, string $name, string $description, string $displayName = '')
   {
      // Set up the CalDAV client
      $client = new \Sabre\DAV\Client([
         'baseUri' => 'https://localhost:8443/dav.php/calendars/chancel/',
         'userName' => 'chancel',
         'password' => 'chancelpass'
      ]);

      // The URL where the new calendar will be created
      $newCalendarUrl = 'https://localhost:8443/dav.php/calendars/chancel/new-calendar/';

      // Define the XML body for the MKCALENDAR request
      $mkcalendarBody = <<<XML
      <?xml version="1.0" encoding="utf-8" ?>
      <C:mkcalendar xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
          <D:set>
              <D:prop>
                  <D:displayname>My New Calendar</D:displayname>
                  <C:calendar-description>This is a new calendar created via Sabre/DAV</C:calendar-description>
                  <C:supported-calendar-component-set>
                      <C:comp name="VEVENT"/>
                      <C:comp name="VTODO"/>
                  </C:supported-calendar-component-set>
              </D:prop>
          </D:set>
      </C:mkcalendar>
      XML;

      // Send the MKCALENDAR request
      $response = $client->request('MKCALENDAR', $newCalendarUrl, $mkcalendarBody, [
         'Content-Type' => 'application/xml; charset=utf-8'
      ]);

      // Check the response status
      if ($response['statusCode'] === 201) {
         echo "Calendar successfully created!\n";
      } else {
         echo "Failed to create calendar. Status code: " . $response['statusCode'] . "\n";
         print_r($response);
      }
   }

   public function __createCalendar(string $credentials, string $name, string $description, string $displayName = '')
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
