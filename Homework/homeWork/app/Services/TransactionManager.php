<?php 

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Contracts\TransactionManagerInterface;

class TransactionManager implements TransactionManagerInterface
{
   public function start(){
    DB::beginTransaction();
   }

   public function commit(){
    DB::commit();
   }

   public function rollback(){
    DB::rollback();
   }
}