<?php declare(strict_types=1);

namespace App\Domain\Service\File\Exception;

use App\Domain\AbstractException;

class FileNotFoundException extends AbstractException
{
    protected $message = 'EXCEPTION_FILE_NOT_FOUND';
}
