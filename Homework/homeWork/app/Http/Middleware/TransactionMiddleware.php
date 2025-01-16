<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class TransactionMiddleware
{
    public function handle($request, Closure $next)
    {
        DB::beginTransaction(); // بدء المعاملة

        try {
            // تنفيذ التابع
            $response = $next($request);

            DB::commit(); // إتمام المعاملة
            return $response;
        } catch (\Exception $e) {
            DB::rollback(); // التراجع عن المعاملة في حالة حدوث خطأ
            throw $e; // إعادة الخطأ
        }
    }
}
