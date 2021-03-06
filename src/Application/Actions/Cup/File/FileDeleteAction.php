<?php declare(strict_types=1);

namespace App\Application\Actions\Cup\File;

class FileDeleteAction extends FileAction
{
    protected function action(): \Slim\Http\Response
    {
        if ($this->resolveArg('uuid') && \Ramsey\Uuid\Uuid::isValid($this->resolveArg('uuid'))) {
            $this->fileService->delete($this->resolveArg('uuid'));
        }

        return $this->response->withRedirect('/cup/file');
    }
}
