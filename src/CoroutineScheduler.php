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
use Swoole\Coroutine;
use Swoole\Timer;

class CoroutineScheduler implements CoroutineSchedulerInterface, DisposableInterface
{
    protected array $callbacks  = [];
    
    #[\Override]
    public function run(\Closure $function): CoroutineInterface
    {
        return new CoroutineAdapter(Coroutine::create($function));
    }
    
    #[\Override]
    public function await(iterable $futures, ?CancellationInterface $cancellation = null): array
    {
        // TODO: Implement await() method.
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