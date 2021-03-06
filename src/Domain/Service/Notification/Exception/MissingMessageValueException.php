<?php declare(strict_types=1);

namespace App\Domain\Service\Notification\Exception;

use App\Domain\AbstractException;

class MissingMessageValueException extends AbstractException
{
    protected $message = 'EXCEPTION_ADDRESS_ALREADY_EXISTS';
}
