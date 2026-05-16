<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Support;

use Countable;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

/**
 * Captures every Guzzle request a HandlerStack handles, with a strongly
 * typed accessor so tests don't have to peek into the loosely typed
 * payload that {@see \GuzzleHttp\Middleware::history} writes.
 */
final class RequestSpy implements Countable
{
    /** @var list<RequestInterface> */
    private array $requests = [];

    public function attach(HandlerStack $handler): void
    {
        $requests = &$this->requests;

        $handler->push(static function (callable $next) use (&$requests): callable {
            return static function (RequestInterface $request, array $options) use ($next, &$requests) {
                $requests[] = $request;

                return $next($request, $options);
            };
        });
    }

    /**
     * @return list<RequestInterface>
     */
    public function all(): array
    {
        return $this->requests;
    }

    public function last(): RequestInterface
    {
        $count = count($this->requests);

        if ($count === 0) {
            throw new RuntimeException('RequestSpy: no requests captured.');
        }

        return $this->requests[$count - 1];
    }

    public function count(): int
    {
        return count($this->requests);
    }

    public function at(int $index): RequestInterface
    {
        if (! array_key_exists($index, $this->requests)) {
            throw new RuntimeException(sprintf('RequestSpy: no request at index %d.', $index));
        }

        return $this->requests[$index];
    }
}
