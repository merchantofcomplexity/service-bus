<?php
declare(strict_types=1);

namespace MerchantOfComplexity\ServiceBus;

use MerchantOfComplexity\ServiceBus\Exception\RuntimeException;
use MerchantOfComplexity\ServiceBus\Support\Concerns\DetectMessageName;
use MerchantOfComplexity\ServiceBus\Support\Events\DispatchedEvent;
use MerchantOfComplexity\Tracker\Contracts\ActionEvent;
use MerchantOfComplexity\Tracker\Contracts\Tracker;
use React\Promise\PromiseInterface;

class Envelope
{
    use DetectMessageName;

    /**
     * @var mixed
     */
    protected $message;
    protected Tracker $tracker;
    protected string $busType;
    protected ?ActionEvent $actionEvent = null;
    protected ?PromiseInterface $promise = null;

    public function __construct(Tracker $tracker, string $busType, $message)
    {
        $this->tracker = $tracker;
        $this->busType = $busType;
        $this->message = $message;
    }

    public function initialize($target = null, callable $callback = null): ActionEvent
    {
        if (!$this->actionEvent) {
            $this->actionEvent = $this->tracker->newActionEvent(new DispatchedEvent($target), $callback);

            $this->actionEvent->setMessageHandled(false);

            $this->actionEvent->setMessage($this->message);

            $this->actionEvent->setMessageName(
                $this->detectMessageName(
                    $this->actionEvent->message()
                )
            );

            return $this->actionEvent;
        }

        throw new RuntimeException("Envelope has already been initialized");
    }

    public function currentActionEvent(): ActionEvent
    {
        if ($this->actionEvent) {
            return $this->actionEvent;
        }

        throw new RuntimeException("Envelope has not been initialized");
    }

    public function markMessageReceived(): void
    {
        $this->actionEvent->setMessageHandled(true);
    }

    public function hasReceipt(): bool
    {
        return $this->actionEvent->isMessageHandled();
    }

    public function wrap($message): Envelope
    {
        if ($message instanceof self) {
            return clone $message;
        }

        $envelope = new self($this->tracker, $this->busType, $message);
        $envelope->promise = $this->promise;

        if ($this->actionEvent) {
            $envelope->actionEvent = $this->actionEvent;

            $envelope->actionEvent->setMessage($message);

            $envelope->actionEvent->setMessageName(
                $this->detectMessageName($message)
            );
        }

        return $envelope;
    }

    /**
     * @return mixed
     */
    public function message()
    {
        if ($this->actionEvent) {
            return $this->actionEvent->message();
        }

        return $this->message;
    }

    public function messageName(): string
    {
        if ($this->actionEvent) {
            return $this->actionEvent->messageName();
        }

        return $this->detectMessageName($this->message);
    }

    public function tracker(): Tracker
    {
        return $this->tracker;
    }

    public function actionEvent(): ?ActionEvent
    {
        return $this->actionEvent;
    }

    public function busType(): string
    {
        return $this->busType;
    }

    public function setPromise(PromiseInterface $promise): void
    {
        $this->promise = $promise;
    }

    public function promise(): ?PromiseInterface
    {
        return $this->promise;
    }
}
