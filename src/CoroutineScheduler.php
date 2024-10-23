<?php
declare(strict_types=1);

namespace IfCastle\Swoole;

use IfCastle\Async\CancellationInterface;
use IfCastle\Async\CoroutineInterface;
use IfCastle\Async\CoroutineSchedulerInterface;
use IfCastle\Async\DeferredCancellationInterface;
use IfCastle\Async\QueueInterface;
use IfCastle\DI\DisposableInterface;
use IfCastle\Exceptions\UnexpectedValue;
use IfCastle\Swoole\Internal\FutureState;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Timer;

class CoroutineScheduler implements CoroutineSchedulerInterface, DisposableInterface
{
    protected array $callbacks  = [];
    
    #[\Override]
    public function run(\Closure $function): CoroutineInterface
    {
        return new CoroutineAdapter(Coroutine::create($function));
    }
    
    /**
     * @throws \Throwable
     */
    #[\Override]
    public function await(iterable $futures, ?CancellationInterface $cancellation = null): array
    {
        $channel                    = new Channel(1);
        $handler                    = null;
        $results                    = [];
        $awaiting                   = [];
        $error                      = null;
        
        foreach ($futures as $future) {
            $results[(string)spl_object_id($future)] = null;
            $awaiting[(string)spl_object_id($future)] = true;
        }
        
        $complete                   = static function() use (&$handler, $futures, $cancellation) {
            
            if(!is_object($handler)) {
                return;
            }
            
            foreach ($futures as $future) {
                if($future instanceof Future) {
                    $future->state->unsubscribe($handler);
                }
            }
            
            $cancellation?->unsubscribe((string)spl_object_id($handler));
        };
        
        $handler                    = static function (mixed $futureStateOrException = null)
                                      use ($channel, $complete, &$handler, $futures, $cancellation, &$results, &$awaiting, &$error) {
            
            if($futureStateOrException instanceof \Throwable) {
                try {
                    $error          = $futureStateOrException;
                    $channel->push(true);
                } finally {
                    $complete();
                }
                
                return;
            }
            
            if(false === $futureStateOrException instanceof FutureState) {
                return;
            }
            
            if($futureStateOrException->getThrowable() !== null) {
                
                try {
                    $error          = $futureStateOrException->getThrowable();
                    $channel->push(true);
                } finally {
                    $complete();
                }
                
                return;
            }
            
            $id                     = (string)spl_object_id($futureStateOrException);
            
            if(array_key_exists($id, $results)) {
                $results[$id]       = $futureStateOrException->getResult();
                unset($awaiting[$id]);
            }
            
            if(count($awaiting) === 0) {
                try {
                    $channel->push(true);
                } finally {
                    $complete();
                }
            }
        };
        
        foreach ($futures as $future) {
            if($future instanceof Future) {
                $future->state->subscribe($handler);
            }
        }
        
        $cancellation?->subscribe($handler);
        
        try {
            $channel->pop();
        } finally {
            $complete();
            
            if($error instanceof \Throwable) {
                throw new $error;
            }
        }
        
        return array_values($results);
    }
    
    #[\Override]
    public function awaitFirst(iterable $futures, ?CancellationInterface $cancellation = null): mixed
    {
        // TODO: Implement awaitFirst() method.
    }
    
    #[\Override]
    public function awaitFirstSuccessful(iterable               $futures,
                                                      ?CancellationInterface $cancellation = null
    ): mixed
    {
        // TODO: Implement awaitFirstSuccessful() method.
    }
    
    #[\Override]
    public function awaitAll(iterable $futures, ?CancellationInterface $cancellation = null): array
    {
        // TODO: Implement awaitAll() method.
    }
    
    #[\Override]
    public function awaitAnyN(int                    $count,
                                           iterable               $futures,
                                           ?CancellationInterface $cancellation = null
    ): array
    {
        // TODO: Implement awaitAnyN() method.
    }
    
    #[\Override]
    public function createChannelPair(int $size = 0): array
    {
        // TODO: Implement createChannelPair() method.
    }
    
    #[\Override]
    public function createQueue(int $size = 0): QueueInterface
    {
        // TODO: Implement createQueue() method.
    }
    
    #[\Override]
    public function createTimeoutCancellation(float $timeout, string $message = 'Operation timed out'): CancellationInterface
    {
        return new TimeoutCancellation($timeout, $message);
    }
    
    #[\Override]
    public function compositeCancellation(CancellationInterface ...$cancellations): CancellationInterface
    {
        return new CompositeCancellation(...$cancellations);
    }
    
    #[\Override]
    public function createDeferredCancellation(): DeferredCancellationInterface
    {
        // TODO: Implement createDeferredCancellation() method.
    }
    
    #[\Override]
    public function defer(callable $callback): void
    {
        Timer::after(0, $callback);
    }
    
    #[\Override]
    public function delay(float|int $delay, callable $callback): int|string
    {
        return Timer::after((int)($delay * 1000), $callback);
    }
    
    /**
     * @throws UnexpectedValue
     */
    #[\Override]
    public function interval(float|int $interval, callable $callback): int|string
    {
        // Check if an interval is 10 ms or less.
        if($interval <= 0.1) {
            throw new UnexpectedValue('$interval', $interval, 'Interval must be greater than 10 ms.');
        }
        
        $timerId                    = Timer::tick((int)($interval * 1000), fn() => $callback());
        $this->callbacks[$timerId]  = $callback;
        
        return $timerId;
    }
    
    #[\Override]
    public function cancelInterval(int|string $timerId): void
    {
        if(Timer::exists($timerId)) {
            Timer::clear($timerId);
        }
        
        if(array_key_exists($timerId, $this->callbacks) === false) {
            return;
        }
        
        //
        // We do this because PHP not free memory correctly for Array structure.
        // So we build a new array from old.
        //
        $callbacks                  = [];
        
        foreach ($this->callbacks as $key => $callback) {
            if($key !== $timerId) {
                $callbacks[$key]    = $callback;
            }
        }
        
        $this->callbacks            = $callbacks;
    }
    
    #[\Override]
    public function stopAllCoroutines(?\Throwable $exception = null): bool
    {
        // TODO: Implement stopAllCoroutines() method.
    }
    
    #[\Override]
    public function dispose(): void
    {
        $callbacks                  = $this->callbacks;
        $this->callbacks            = [];
        
        foreach($callbacks as $timerId => $callback) {
            try {
                
                Timer::clear($timerId);
                
                if($callback instanceof DisposableInterface) {
                    $callback->dispose();
                }
            } catch(\Throwable) {
                // Ignore
            }
        }
    }
}