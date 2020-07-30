<?php


namespace AmoCRM\OAuth;


class OAuthConfig implements OAuthConfigInterface
{

    public $integrationId;
    public $secretKey;
    public $redirectDomain;

    public function setIntegrationId($integrationId)
    {
        $this->integrationId =$integrationId;
    }

    public function setSecretKey($secretKey)
    {
        $this->secretKey =$secretKey;
    }

    public function setRedirectDomain($redirectDomain)
    {
        $this->redirectDomain =$redirectDomain;
    }

    /**
     * @return string
     */
    public function getIntegrationId(): string
    {
        return $this->integrationId;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * @return string
     */
    public function getRedirectDomain(): string
    {
        return $this->redirectDomain;
    }
}