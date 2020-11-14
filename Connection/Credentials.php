<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Connection;

use Mautic\IntegrationsBundle\Auth\Provider\ApiKey\Credentials\HeaderCredentialsInterface;

class Credentials
{
    private $key;
    private $secret;

    public function __construct(string $key, string $secret)
    {
        $this->key = $key;
        $this->secret  = $secret;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getApiSecret(): ?string
    {
        return $this->secret;
    }
}
