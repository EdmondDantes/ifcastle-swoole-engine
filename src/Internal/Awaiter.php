<?php
declare(strict_types=1);

namespace IfCastle\Swoole\Internal;

use IfCastle\Async\CancellationInterface;
use IfCastle\Exceptions\LogicalException;
use IfCastle\Exceptions\UnexpectedValue;
use IfCastle\Swoole\Future;
use Swoole\Coroutine\Channel;

final class Awaiter
{
    /**
     * @throws LogicalException
     * @throws UnexpectedValue
     * @throws \Throwable
     */
    public static function await(int                    $futuresAwaitCount,
                                 iterable               $futures,
                                 ?CancellationInterface $cancellation = null,
                                 bool                   $shouldIgnoreError = false
    ): array
    {
        if($futuresAwaitCount < 0) {
            throw new UnexpectedValue('$futuresAwaitCount', $futuresAwaitCount, 'Count should be greater than 0');
        }
        
        $futuresCount               = \iterator_count($futures);
        
        if($futuresCount === 0) {
            return [];
        }
        
        if($futuresAwaitCount > $futuresCount) {
            throw new UnexpectedValue('$count', $futuresAwaitCount, 'Count should be less than or equal to the number of futures');
        }
        
        if($futuresAwaitCount === 0) {
            $futuresAwaitCount      = $futuresCount;
        }
        
        $channel                    = new Channel(1);
        $handler                    = null;
        $results                    = [];
        $awaiting                   = [];
        $succeed                    = 0;
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
        use ($channel, $complete, &$handler, $futures, $cancellation, &$results, &$awaiting, &$error, &$succeed, $futuresAwaitCount, $shouldIgnoreError) {
            
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
            
            //
            // When the Future failed
            //
            if($futureStateOrException->getThrowable() !== null) {
                
                if($shouldIgnoreError) {
                    $id             = (string)spl_object_id($futureStateOrException);
                    unset($awaiting[$id]);
                    
                    if(count($awaiting) === 0) {
                        try {
                            $error  = new LogicalException('All futures completed but condition not met.');
                            $channel->push(true);
                        } finally {
                            $complete();
                        }
                    }
                    
                    return;
                }
                
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
                $succeed++;
                unset($awaiting[$id]);
            }
            
            if(count($awaiting) === 0 || $futuresAwaitCount <= $succeed) {
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
}