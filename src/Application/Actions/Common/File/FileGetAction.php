<?php declare(strict_types=1);

namespace App\Application\Actions\Common\File;

class FileGetAction extends FileAction
{
    protected function action(): \Slim\Http\Response
    {
        // @var \App\Domain\Entities\File $file
        $file = $this->fileService->read([
            'salt' => $this->resolveArg('salt'),
            'hash' => $this->resolveArg('hash'),
        ]);

        return $this->response
            ->withHeader('Content-Type', 'application/download')
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $file->getFileName() . '"')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Pragma', 'private')
            ->withHeader('Expires', '0')
            ->withBody(new \Slim\Http\Stream($file->getResource()));
    }
}
