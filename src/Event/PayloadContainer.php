<?php

namespace eArc\eventTree\Event;

use eArc\eventTree\Exceptions\PayloadOverwriteException;
use Psr\Container\ContainerInterface;

class PayloadContainer {

    protected $payload = [];
    protected $container;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function setPayload(string $key, $payload): void
    {
        if (isset($this->payload[$key]))
        {
            throw new PayloadOverwriteException("Key `$key` is already used.");
        }

        $this->payload[$key] = $payload;
    }

    public function getPayload(?string $key = null)
    {
        if (null === $key)
        {
            return $this->payload;
        }

        return $this->payload[$key];
    }

    public function hasPayload(string $key): bool
    {
        return isset($this->payload[$key]);
    }

    public function unsetPayload(?string $key = null): void
    {
        if (null === $key)
        {
            $this->payload = [];
            return;
        }

        unset($this->payload[$key]);
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }
}
