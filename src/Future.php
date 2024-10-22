<?php
declare(strict_types=1);

namespace IfCastle\Swoole;

use IfCastle\Async\CancellationInterface;
use IfCastle\Async\FutureInterface;
use IfCastle\Swoole\Internal\FutureState;
use Swoole\Coroutine\Channel;

final readonly class Future implements FutureInterface
{
    public function __construct(private FutureState $state) {}
    
    #[\Override]
    public function isComplete(): bool
    {
        return $this->state->isComplete();
    }
    
    #[\Override]
    public function ignore(): void
    {
        $this->state->ignore();
    }
    
    /**
     * @throws \Throwable
     */
    #[\Override]
    public function await(CancellationInterface $cancellation = null): mixed
    {
        $channel                    = new Channel(1);
        
        $this->state->onComplete(static fn() => $channel->push(true));
        
        $channel->pop();
        
        if($this->state->getThrowable() !== null) {
            throw $this->state->getThrowable();
        } else {
            return $this->state->getResult();
        }
    }
    
    #[\Override]
    public function map(callable $mapper): FutureInterface
    {
        $futureState                = new FutureState();
        $state                      = $this->state;
        
        $state->onComplete(static function() use ($futureState, $state, $mapper) {
            if($state->getThrowable() !== null) {
                $futureState->complete($state->getThrowable());
            } else {
                $futureState->complete($mapper($state->getResult()));
            }
        });
        
        return new Future($futureState);
    }
    
    #[\Override]
    public function catch(callable $onRejected): static
    {
        $state = $this->state;
        
        $state->onComplete(static function() use ($onRejected, $state) {
            if($state->getThrowable() !== null) {
                $onRejected($state->getThrowable());
            }
        });
        
        return $this;
    }
    
    #[\Override]
    public function finally(callable $onFinally): static
    {
        $this->state->onComplete($onFinally);
        return $this;
    }
}