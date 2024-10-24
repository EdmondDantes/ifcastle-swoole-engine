<?php
declare(strict_types=1);

namespace IfCastle\Swoole;

use IfCastle\Async\CancellationInterface;
use IfCastle\Async\DeferredCancellationInterface;

class DeferredCancellation implements DeferredCancellationInterface
{
    #[\Override]
    public function getCancellation(): CancellationInterface
    {
        // TODO: Implement getCancellation() method.
    }
    
    #[\Override]
    public function isCancelled(): bool
    {
        // TODO: Implement isCancelled() method.
    }
    
    #[\Override]
    public function cancel(?\Throwable $previous = null): void
    {
        // TODO: Implement cancel() method.
    }
}