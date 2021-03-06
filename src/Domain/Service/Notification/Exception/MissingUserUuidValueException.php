<?php declare(strict_types=1);

namespace App\Domain\Service\Notification\Exception;

use App\Domain\AbstractException;

class MissingUserUuidValueException extends AbstractException
{
    protected $message = 'EXCEPTION_USER_UUID_MISSING';
}
