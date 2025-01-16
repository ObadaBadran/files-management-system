<?php

namespace App\Aspects;

use Illuminate\Support\Facades\Log;


class ExceptionAspect
{
   
    public static function handle(callable $callback, $context = 'default')
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            // Log the error with its context for debugging
            Log::error("Error in context: {$context}", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Optionally, you can throw a custom exception or rethrow the original
            throw $e;
        }
    }
}