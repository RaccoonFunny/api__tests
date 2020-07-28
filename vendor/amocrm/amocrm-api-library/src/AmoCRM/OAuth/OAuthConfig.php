<?php


namespace AmoCRM\OAuth\;


class OAuthConfig implements \AmoCRM\OAuth\OAuthConfigInterface
{
    protected $access_Token;

    public function getIntegrationId(): string

    {
        return "7c2fc1ac-4f40-477b-8d15-bc307350293e";
    }

    public function getSecretKey(): string
    {
        return "l400pgDgV1rlR09A7Oj8JQVZpF8Q3x5hnHf6Ro8OwiioXCyIoeosDTYxvCIw8GnD";
    }

    public function getRedirectDomain(): string
    {
        return $this->access_Token;
    }

    public function __construct($access_t)
    {
        $this->access_Token = $access_t;
    }

}