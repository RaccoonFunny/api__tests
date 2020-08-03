<?php


namespace AmoCRM\OAuth;


use League\OAuth2\Client\Token\AccessTokenInterface;

class OAuthService implements OAuthServiceInterface
{

    /**
     * @inheritDoc
     */
    public function saveOAuthToken(AccessTokenInterface $accessToken, string $baseDomain): void
    {
        // TODO: Implement saveOAuthToken() method.
    }
}