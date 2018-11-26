<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Event;

use eArc\EventTree\Exceptions\PayloadOverwriteException;
use eArc\EventTree\Tree\Node;
use Psr\Container\ContainerInterface;

/**
 * Defines a payload container and registers a psr compatible container for
 * later use.
 */
class PayloadContainer extends Node
{
    /** @var array */
    protected $payload = [];

    /** @var null|ContainerInterface */
    protected $container;

    /**
     * PayloadContainer constructor.
     * @param PayloadContainer|null $parent
     * @param bool $inheritPayload
     * @param null|ContainerInterface $container
     */
    public function __construct(
        ?PayloadContainer $parent = null,
        bool $inheritPayload = false,
        ?ContainerInterface $container = null
    ) {
        if ($inheritPayload && $parent) {
            $this->payload = $parent->getPayload();
        }
        $this->container = $container;
        parent::__construct($parent);
    }

    /**
     * Add a payload to the instance.
     *
     * @param string $key
     * @param $payload
     * @param bool $overwrite
     */
    public function setPayload(string $key, $payload, $overwrite = false): void
    {
        if (!$overwrite && isset($this->payload[$key]))
        {
            throw new PayloadOverwriteException("Key `$key` is already used.");
        }

        $this->payload[$key] = $payload;
    }

    /**
     * Get a payload by its key or the complete payload.
     *
     * @param null|string $key
     * @return array|mixed
     */
    public function getPayload(?string $key = null)
    {
        if (null === $key)
        {
            return $this->payload;
        }

        return $this->payload[$key];
    }

    /**
     * Checks whether a specific payload exists.
     *
     * @param string $key
     * @return bool
     */
    public function hasPayload(string $key): bool
    {
        return isset($this->payload[$key]);
    }

    /**
     * Unset a specific payload or reset the whole payload container.
     *
     * @param null|string $key
     */
    public function unsetPayload(?string $key = null): void
    {
        if (null === $key)
        {
            $this->payload = [];
            return;
        }

        unset($this->payload[$key]);
    }

    /**
     * Get the registered psr compatible container or null if none was
     * registered.
     *
     * @return null|ContainerInterface
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }
}
