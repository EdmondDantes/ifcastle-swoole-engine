<?php
declare(strict_types=1);

namespace IfCastle\Swoole;

use IfCastle\Async\CancellationInterface;
use IfCastle\Async\CoroutineInterface;
use IfCastle\Async\CoroutineSchedulerInterface;
use IfCastle\Async\DeferredCancellationInterface;
use IfCastle\Async\QueueInterface;

class CoroutineScheduler implements CoroutineSchedulerInterface
{
    #[\Override]
    public function run(\Closure $function): CoroutineInterface
    {
        // TODO: Implement run() method.
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
    public function createTimeoutCancellation(float  $timeout,
                                                           string $message = 'Operation timed out'
    ): CancellationInterface
    {
        // TODO: Implement createTimeoutCancellation() method.
    }
    
    #[\Override]
    public function compositeCancellation(CancellationInterface ...$cancellations): CancellationInterface
    {
        // TODO: Implement compositeCancellation() method.
    }
    
    #[\Override]
    public function createDeferredCancellation(): DeferredCancellationInterface
    {
        // TODO: Implement createDeferredCancellation() method.
    }
    
    #[\Override]
    public function defer(callable $callback): void
    {
        // TODO: Implement defer() method.
    }
    
    #[\Override]
    public function delay(float|int $delay, callable $callback): int|string
    {
        // TODO: Implement delay() method.
    }
    
    #[\Override]
    public function interval(float|int $interval, callable $callback): int|string
    {
        // TODO: Implement interval() method.
    }
    
    #[\Override]
    public function cancelInterval(int|string $timerId): void
    {
        // TODO: Implement cancelInterval() method.
    }
    
    #[\Override]
    public function stopAllCoroutines(?\Throwable $exception = null): bool
    {
        // TODO: Implement stopAllCoroutines() method.
    }
}