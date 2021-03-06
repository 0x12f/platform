<?php declare(strict_types=1);

namespace App\Application\Actions\Cup\User\Group;

use App\Application\Actions\Cup\User\UserAction;
use App\Domain\Service\User\Exception\TitleAlreadyExistsException;

class UpdateAction extends UserAction
{
    protected function action(): \Slim\Http\Response
    {
        if ($this->resolveArg('uuid')) {
            $userGroup = $this->userGroupService->read(['uuid' => $this->resolveArg('uuid')]);

            if ($userGroup) {
                if ($this->request->isPost()) {
                    try {
                        $this->userGroupService->update($userGroup, [
                            'title' => $this->request->getParam('title'),
                            'description' => $this->request->getParam('description'),
                            'access' => $this->request->getParam('access', []),
                        ]);

                        switch (true) {
                            case $this->request->getParam('save', 'exit') === 'exit':
                                return $this->response->withRedirect('/cup/user/group');

                            default:
                                return $this->response->withRedirect('/cup/user/group/' . $userGroup->getUuid() . '/edit');
                        }
                    } catch (TitleAlreadyExistsException $e) {
                        $this->addError('title', $e->getMessage());
                    }
                }

                return $this->respondWithTemplate('cup/user/group/form.twig', [
                    'item' => $userGroup,
                    'routes' => [
                        'all' => $this->getRoutes()->all(),
                        'default' => explode(',', $this->parameter('user_access', '')),
                    ],
                ]);
            }
        }

        return $this->response->withRedirect('/cup/user/group');
    }
}
