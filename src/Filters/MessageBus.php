<?php

declare(strict_types=1);

namespace Jhavens\Streamfilters\Filters;

use SplObserver;
use SplSubject;

class MessageBus implements SplSubject
{
    private mixed $data = null;
    private ?string $message = null;
    private array $observers = [];

    public function attach(SplObserver $observer): void
    {
        $this->observers[spl_object_hash($observer)] = $observer;
    }

    public function detach(SplObserver $observer): void
    {
        unset($this->observers[spl_object_hash($observer)]);
    }

    public function notify(): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function send(string $message, mixed $data = null): void
    {
        $this->message = $message;
        $this->data = $data;
        $this->notify();
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function triggerOnPattern(string $pattern, callable $callback, string $streamData): void
    {
        if (preg_match($pattern, $streamData)) {
            $callback($this);
        }
    }
}
