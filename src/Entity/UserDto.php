<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class UserDto{

   #[Assert\NotNull]
   #[Assert\NotBlank]
   #[Assert\Length(
       min: 4,
       max: 255,
       minMessage: 'Your username must be at least {{ limit }} characters long',
       maxMessage: 'Your username cannot be longer than {{ limit }} characters',
   )]
   private ?string $username;

   #[Assert\NotNull]
   #[Assert\NotBlank]
   #[Assert\Length(
       min: 4,
       max: 255,
       minMessage: 'Your password must be at least {{ limit }} characters long',
       maxMessage: 'Your password cannot be longer than {{ limit }} characters',
   )]
   private ?string $password;


   #[Assert\NotNull]
   #[Assert\NotBlank]
   #[Assert\Length(
       min: 4,
       max: 255,
       minMessage: 'Your calName must be at least {{ limit }} characters long',
       maxMessage: 'Your calName cannot be longer than {{ limit }} characters',
   )]
   private ?string $calName;



   /**
    * Get the value of username
    */ 
   public function getUsername():string
   {
      return $this->username;
   }

   /**
    * @param string $username
    * @return self
    */
   public function setUsername(string $username):self
   {
      $this->username = $username;

      return $this;
   }

   /**
    * @return string
    */
   public function getPassword():string
   {
      return $this->password;
   }

   /**
    * @param string $password
    * @return self
    */
   public function setPassword(string $password):self
   {
      $this->password = $password;

      return $this;
   }

   /**
    * @return string
    */
   public function getCalName():string
   {
      return $this->calName;
   }

   /**
    * @param string $calName
    * @return self
    */
   public function setCalName(string $calName):self
   {
      $this->calName = $calName;

      return $this;
   }
}