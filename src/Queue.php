<?php
declare(strict_types=1);

namespace IfCastle\Swoole;

use IfCastle\Async\ConcurrentIteratorInterface;
use IfCastle\Async\FutureInterface;
use IfCastle\Async\QueueInterface;

final class Queue implements QueueInterface
{
    #[\Override]
    public function pushAsync(mixed $value): void
    {
        // TODO: Implement pushAsync() method.
    }
    
    #[\Override]
    public function pushWithPromise(mixed $value): FutureInterface
    {
        // TODO: Implement pushWithPromise() method.
    }
    
    #[\Override]
    public function push(mixed $value): void
    {
        // TODO: Implement push() method.
    }
    
    #[\Override]
    public function getIterator(): ConcurrentIteratorInterface
    {
        // TODO: Implement getIterator() method.
    }
    
    #[\Override]
    public function isComplete(): bool
    {
        // TODO: Implement isComplete() method.
    }
    
    #[\Override]
    public function isDisposed(): bool
    {
        // TODO: Implement isDisposed() method.
    }
    
    #[\Override]
    public function complete(): void
    {
        // TODO: Implement complete() method.
    }
    
    #[\Override]
    public function error(\Throwable $reason): void
    {
        // TODO: Implement error() method.
    }
}