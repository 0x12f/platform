<?php declare(strict_types=1);

namespace App\Domain\Service\GuestBook\Exception;

use App\Domain\AbstractException;

class MissingNameValueException extends AbstractException
{
    protected $message = 'EXCEPTION_NAME_MISSING';
}
