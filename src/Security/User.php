<?php

namespace App\Security;

use App\Plateform\PlateformUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var string
     */
    private string $username = '';

    /**
     * @var list<string> The user roles
     */
    private $roles = [];

    /**
     * @var string The hashed password
     */
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private $password = '';

    /**
     * @var string The name of user calendar collection
     */
    private string $calCollectionName = '';

    /**
     * @var string
     */
    private string $apiToken;

    // private PlateformUserInterface $credentials;
    private string $credentials;

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     * @return static
     */
    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->apiToken;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Get the value of apiToken
     * @return  string
     */
    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    /**
     * Set the value of apiToken
     * @param  string  $apiToken
     * @return  self
     */
    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * Get the name of user calendar collection
     *
     * @return  string
     */
    public function getCalCollectionName(): string
    {
        return $this->calCollectionName;
    }

    /**
     * Set the name of user calendar collection
     *
     * @param string|null $calCollectionName
     * @return self
     */
    public function setCalCollectionName(?string $calCollectionName): self
    {
        $this->calCollectionName = $calCollectionName;

        return $this;
    }

    /**
     * Get the value of credentials
     *
     * @return string
     */
    public function getCredentials(): string
    {
        return $this->credentials;
    }

    /**
     * Undocumented function
     *
     * @param string $credentials
     * @return self
     */
    public function setCredentials(string $credentials): self
    {
        $this->credentials = $credentials;

        return $this;
    }
}
