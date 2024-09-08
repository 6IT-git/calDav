<?php

namespace App;

use App\Plateform\PlateformUserInterface;
use App\Security\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtTool
{

   const ENCODER = 'HS256';
   const EXP_DELAY = 3600;

   public static function encode(string $secret, User $userDto): string
   {
      // dd($userDto);
      return JWT::encode([
         'username' => $userDto->getUsername(),
         'password' => $userDto->getPassword(),
         'calendar_name' => $userDto->getCalCollectionName(),
         'credentials' => $userDto->getCredentials(),
         'exp' => time() + self::EXP_DELAY
      ], $secret, self::ENCODER);
   }

   /* public static function encode(string $secret, PlateformUserInterface $user): string
   {
      return JWT::encode([
         'payload' => $user->__toString(),
         'exp' => time() + self::EXP_DELAY
      ], $secret, self::ENCODER);
   } */

   public static function decode(string $jwt, string $secret)
   {
      return JWT::decode(
         $jwt,
         new Key($secret, self::ENCODER)
      );
   }
}
