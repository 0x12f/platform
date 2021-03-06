<?php declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\AbstractHttpException;

class FileNotFoundException extends AbstractHttpException
{
    protected string $title = 'File not found';

    protected string $description = 'The requested file could not be found.';
}
