<?php
declare(strict_types=1);

namespace IfCastle\Swoole;

use IfCastle\Async\CoroutineContextInterface;

class CoroutineContext implements CoroutineContextInterface
{
    #[\Override]
    public function isCoroutine(): bool
    {
        // TODO: Implement isCoroutine() method.
    }
    
    #[\Override]
    public function getCoroutineId(): string|int
    {
        // TODO: Implement getCoroutineId() method.
    }
    
    #[\Override]
    public function getCoroutineParentId(): string|int
    {
        // TODO: Implement getCoroutineParentId() method.
    }
    
    #[\Override]
    public function has(string $key): bool
    {
        // TODO: Implement has() method.
    }
    
    #[\Override]
    public function get(string $key): mixed
    {
        // TODO: Implement get() method.
    }
    
    #[\Override]
    public function getLocal(string $key): mixed
    {
        // TODO: Implement getLocal() method.
    }
    
    #[\Override]
    public function hasLocal(string $key): bool
    {
        // TODO: Implement hasLocal() method.
    }
    
    #[\Override]
    public function set(string $key, mixed $value): static
    {
        // TODO: Implement set() method.
    }
    
    #[\Override]
    public function defer(callable $callback): static
    {
        // TODO: Implement defer() method.
    }
}