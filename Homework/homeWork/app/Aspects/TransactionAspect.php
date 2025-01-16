<?php

namespace App\Aspects;

use Illuminate\Support\Facades\DB;

class TransactionAspect
{
    public static function wrap(callable $callback)
    {
        DB::beginTransaction();

        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
