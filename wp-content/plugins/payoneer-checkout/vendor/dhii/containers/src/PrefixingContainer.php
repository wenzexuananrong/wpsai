<?php

declare (strict_types=1);
namespace Syde\Vendor\Dhii\Container;

use Syde\Vendor\Dhii\Collection\ContainerInterface;
use Syde\Vendor\Dhii\Container\Exception\ContainerException;
use Syde\Vendor\Dhii\Container\Exception\NotFoundException;
use Exception;
use Syde\Vendor\Psr\Container\ContainerInterface as PsrContainerInterface;
use Syde\Vendor\Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
/**
 * A container implementation that wraps around an inner container and prefixes its keys, requiring consumers to
 * include them when fetching or looking up data.
 *
 * @since [*next-version*]
 */
class PrefixingContainer implements ContainerInterface
{
    /**
     * @since [*next-version*]
     *
     * @var PsrContainerInterface
     */
    protected $inner;
    /**
     * @since [*next-version*]
     *
     * @var string
     */
    protected $prefix;
    /**
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $strict;
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PsrContainerInterface $container The container whose keys to prefix.
     * @param string                $prefix    The prefix to apply to the container's keys.
     * @param bool                  $strict    Whether or not to fallback to un-prefixed keys if a prefixed key does not
     *                                         exist in the inner container.
     */
    public function __construct(PsrContainerInterface $container, string $prefix, bool $strict = \true)
    {
        $this->inner = $container;
        $this->prefix = $prefix;
        $this->strict = $strict;
    }
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        if (!$this->isPrefixed($key) && $this->strict) {
            throw new NotFoundException(sprintf('Key "%s" does not exist', $key));
        }
        /**
         * @psalm-suppress InvalidCatch
         * The base interface does not extend Throwable, but in fact everything that is possible
         * in theory to catch will be Throwable, and PSR-11 exceptions will implement this interface
         */
        try {
            return $this->inner->get($this->unprefix($key));
        } catch (NotFoundExceptionInterface $nfException) {
            if ($this->strict) {
                throw $nfException;
            }
        }
        return $this->inner->get($key);
    }
    /**
     * @inheritdoc
     *
     * @since [*next-version*]
     */
    public function has($key)
    {
        $key = (string) $key;
        if (!$this->isPrefixed($key) && $this->strict) {
            return \false;
        }
        try {
            $realKey = $this->unprefix($key);
        } catch (Exception $e) {
            throw new ContainerException(sprintf('Could not unprefix key "%1$s"', $key), 0, $e);
        }
        return $this->inner->has($realKey) || !$this->strict && $this->inner->has($key);
    }
    /**
     * Retrieves the key to use for the inner container.
     *
     * @since [*next-version*]
     *
     * @param string $key The outer key.
     *
     * @return string The inner key.
     */
    protected function unprefix(string $key) : string
    {
        return $this->isPrefixed($key) ? $this->substring($key, strlen($this->prefix)) : $key;
    }
    /**
     * Checks if the key is prefixed.
     *
     * @since [*next-version*]
     *
     * @param string $key The key to check.
     *
     * @return bool True if the key is prefixed, false if not.
     */
    protected function isPrefixed(string $key) : bool
    {
        return strlen($this->prefix) > 0 && strpos($key, $this->prefix) === 0;
    }
    /**
     * Extracts a substring from the specified string.
     *
     * @see substr()
     *
     * @param string $string The string to extract from.
     * @param int $offset The char position, at which to start extraction.
     * @param int|null $length The char position, at which to end extraction; unlimited if `null`.
     *
     * @return string The extracted substring.
     *
     * @throws RuntimeException If unable to extract.
     */
    protected function substring(string $string, int $offset = 0, ?int $length = null) : string
    {
        $length = $length ?? strlen($string) - $offset;
        $substring = substr($string, $offset, $length);
        if ($substring === \false) {
            throw new RuntimeException(sprintf('Could not extract substring starting at %1$d of length %2$s from string "%3$s"', $offset, $length ?: 'null', $string));
        }
        return $substring;
    }
}
