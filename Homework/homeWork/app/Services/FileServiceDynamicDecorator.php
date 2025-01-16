<?php

namespace App\Services;

use App\Aspects\ExceptionAspect;
use App\Aspects\TransactionAspect;

class FileServiceDynamicDecorator
{
    protected $fileService;

    public function __construct($fileService)
    {
        $this->fileService = $fileService;
    }

    public function __call($method, $arguments)
    {
        // تحديد الطرق التي تحتاج إلى معاملة (TransactionAspect)
        $transactionMethods = ['reserveFiles'];

        // إذا كانت الطريقة ضمن القائمة، أضف المعاملة
        if (in_array($method, $transactionMethods)) {
            return ExceptionAspect::handle(function () use ($method, $arguments) {
                return TransactionAspect::wrap(function () use ($method, $arguments) {
                    return call_user_func_array([$this->fileService, $method], $arguments);
                });
            }, $method);
        }

        // في حالة عدم وجودها، استخدم فقط ExceptionAspect
        return ExceptionAspect::handle(function () use ($method, $arguments) {
            return call_user_func_array([$this->fileService, $method], $arguments);
        }, $method);
    }
}
