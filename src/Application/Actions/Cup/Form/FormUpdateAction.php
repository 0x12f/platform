<?php

namespace Application\Actions\Cup\Form;

use Exception;

class FormUpdateAction extends FormAction
{
    protected function action(): \Slim\Http\Response
    {
        if ($this->resolveArg('uuid') && \Ramsey\Uuid\Uuid::isValid($this->resolveArg('uuid'))) {
            /** @var \Domain\Entities\Form $item */
            $item = $this->formRepository->findOneBy(['uuid' => $this->resolveArg('uuid')]);

            if (!$item->isEmpty()) {
                if ($this->request->isPost()) {
                    $data = [
                        'uuid' => $item->uuid,
                        'title' => $this->request->getParam('title'),
                        'address' => $this->request->getParam('address'),
                        'template' => $this->request->getParam('template'),
                        'mailto' => $this->request->getParam('mailto'),
                        'origin' => $this->request->getParam('origin'),
                    ];

                    $check = \Domain\Filters\Form::check($data);

                    if ($check === true) {
                        try {
                            $item->replace($data);
                            $this->entityManager->persist($item);
                            $this->entityManager->flush();

                            return $this->response->withAddedHeader('Location', '/cup/form');
                        } catch (Exception $e) {
                            // todo nothing
                        }
                    }
                }

                return $this->respondRender('cup/form/form.twig', ['item' => $item]);
            }
        }

        return $this->response->withAddedHeader('Location', '/cup/form');
    }
}