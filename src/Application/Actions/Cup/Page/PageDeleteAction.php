<?php declare(strict_types=1);

namespace App\Application\Actions\Cup\Page;

class PageDeleteAction extends PageAction
{
    protected function action(): \Slim\Http\Response
    {
        if ($this->resolveArg('uuid') && \Ramsey\Uuid\Uuid::isValid($this->resolveArg('uuid'))) {
            $this->pageService->delete($this->resolveArg('uuid'));
        }

        return $this->response->withRedirect('/cup/page');
    }
}
