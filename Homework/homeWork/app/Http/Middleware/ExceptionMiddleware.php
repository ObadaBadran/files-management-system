<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class ExceptionMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // تنفيذ التابع
            return $next($request);
        } catch (\Exception $e) {
            // تسجيل الخطأ
            Log::error('Error occurred in ' . $request->route()->getName(), [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e; // إعادة الخطأ
        }
    }
}
