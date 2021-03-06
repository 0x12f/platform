<?php declare(strict_types=1);

namespace App\Application\Actions\Cup\User;

use App\Domain\Service\User\Exception\EmailAlreadyExistsException;
use App\Domain\Service\User\Exception\EmailBannedException;
use App\Domain\Service\User\Exception\PhoneAlreadyExistsException;
use App\Domain\Service\User\Exception\UsernameAlreadyExistsException;
use App\Domain\Service\User\Exception\WrongEmailValueException;
use App\Domain\Service\User\Exception\WrongPhoneValueException;

class UserCreateAction extends UserAction
{
    protected function action(): \Slim\Http\Response
    {
        $userGroups = $this->userGroupService->read();

        if ($this->request->isPost()) {
            try {
                $group_uuid = $this->request->getParam('group_uuid');
                $user = $this->userService->create([
                    'username' => $this->request->getParam('username'),
                    'password' => $this->request->getParam('password'),
                    'firstname' => $this->request->getParam('firstname'),
                    'lastname' => $this->request->getParam('lastname'),
                    'address' => $this->request->getParam('address'),
                    'additional' => $this->request->getParam('additional'),
                    'email' => $this->request->getParam('email'),
                    'allow_mail' => $this->request->getParam('allow_mail'),
                    'phone' => $this->request->getParam('phone'),
                    'group' => $group_uuid !== \Ramsey\Uuid\Uuid::NIL ? $userGroups->firstWhere('uuid', $group_uuid) : '',
                ]);
                $user = $this->processEntityFiles($user);

                switch (true) {
                    case $this->request->getParam('save', 'exit') === 'exit':
                        return $this->response->withRedirect('/cup/user');

                    default:
                        return $this->response->withRedirect('/cup/user/' . $user->getUuid() . '/edit');
                }
            } catch (UsernameAlreadyExistsException $e) {
                $this->addError('username', $e->getMessage());
            } catch (WrongEmailValueException | EmailAlreadyExistsException | EmailBannedException $e) {
                $this->addError('email', $e->getMessage());
            } catch (WrongPhoneValueException | PhoneAlreadyExistsException $e) {
                $this->addError('phone', $e->getMessage());
            }
        }

        return $this->respondWithTemplate('cup/user/form.twig', ['groups' => $userGroups]);
    }
}
