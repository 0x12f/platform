<?php declare(strict_types=1);

namespace App\Application\Actions\Common\User;

class UserLogoutAction extends UserAction
{
    protected function action(): \Slim\Http\Response
    {
        /** @var \App\Domain\Entities\User $user */
        $user = $this->request->getAttribute('user', false);

        if ($user) {
            $session = $user->getSession()
                ->setAgent('')
                ->setDate(null)
                ->setIp('0.0.0.0');

            // write clear session
            $this->userService->write($session);

            setcookie('uuid', '-1', time(), '/');
            setcookie('session', '-1', time(), '/');
        }

        return $this->response->withRedirect('/');
    }
}
