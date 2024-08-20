<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class UserDto{

    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $username;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $password;

    private string $calCollectionName;


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
     *
     * @return self
     */
    public function setUsername(?string $username): self
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
     *
     * @return self
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of calCollectionName
     *
     * @return string
     */
    public function getCalCollectionName(): string
    {
        return $this->calCollectionName;
    }

    /**
     * Set the value of calCollectionName
     *
     * @param string $calCollectionName
     *
     * @return self
     */
    public function setCalCollectionName(?string $calCollectionName): self
    {
        $this->calCollectionName = $calCollectionName;

        return $this;
    }
}