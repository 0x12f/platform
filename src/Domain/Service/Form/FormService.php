<?php declare(strict_types=1);

namespace App\Domain\Service\Form;

use App\Domain\AbstractService;
use App\Domain\Entities\Form;
use App\Domain\Repository\FormRepository;
use App\Domain\Service\Form\Exception\AddressAlreadyExistsException;
use App\Domain\Service\Form\Exception\MissingTitleValueException;
use App\Domain\Service\Form\Exception\FormNotFoundException;
use App\Domain\Service\Form\Exception\TitleAlreadyExistsException;
use Ramsey\Uuid\Uuid;
use Tightenco\Collect\Support\Collection;

class FormService extends AbstractService
{
    /**
     * @var FormRepository
     */
    protected $service;

    protected function init(): void
    {
        $this->service = $this->entityManager->getRepository(Form::class);
    }

    /**
     * @param array $data
     *
     * @throws TitleAlreadyExistsException
     * @throws MissingTitleValueException
     * @throws AddressAlreadyExistsException
     *
     * @return Form
     */
    public function create(array $data = []): Form
    {
        $default = [
            'title' => '',
            'address' => '',
            'template' => '',
            'save_data' => true,
            'recaptcha' => true,
            'origin' => [],
            'mailto' => [],
        ];
        $data = array_merge($default, $data);

        if ($data['title'] && $this->service->findOneByTitle($data['title']) !== null) {
            throw new TitleAlreadyExistsException();
        }
        if (!$data['title']) {
            throw new MissingTitleValueException();
        }
        if ($data['address'] && $this->service->findOneByAddress($data['address']) !== null) {
            throw new AddressAlreadyExistsException();
        }

        $form = (new Form)
            ->setTitle($data['title'])
            ->setAddress($data['address'])
            ->setTemplate($data['template'])
            ->setSaveData($data['save_data'])
            ->setRecaptcha($data['recaptcha'])
            ->setOrigin($data['origin'])
            ->setMailto($data['mailto']);

        $this->entityManager->persist($form);
        $this->entityManager->flush();

        return $form;
    }

    /**
     * @param array $data
     *
     * @throws FormNotFoundException
     *
     * @return Collection|Form
     */
    public function read(array $data = [])
    {
        $default = [
            'uuid' => '',
            'title' => '',
            'address' => '',
            'template' => '',
            'mailto' => '',
        ];
        $data = array_merge($default, static::$default_read, $data);

        if ($data['uuid'] || $data['title'] || $data['address']) {
            switch (true) {
                case $data['uuid']:
                    $form = $this->service->findOneByUuid((string) $data['uuid']);

                    break;

                case $data['title']:
                    $form = $this->service->findOneByTitle($data['title']);

                    break;

                case $data['address']:
                    $form = $this->service->findOneByAddress($data['address']);

                    break;
            }

            if (empty($form)) {
                throw new FormNotFoundException();
            }

            return $form;
        }

        $criteria = [];

        if ($data['template']) {
            $criteria['template'] = $data['template'];
        }
        if ($data['mailto']) {
            $criteria['mailto'] = $data['mailto'];
        }

        return collect($this->service->findBy($criteria, $data['order'], $data['limit'], $data['offset']));
    }

    /**
     * @param Form|string|Uuid $entity
     * @param array            $data
     *
     * @throws TitleAlreadyExistsException
     * @throws AddressAlreadyExistsException
     * @throws FormNotFoundException
     *
     * @return Form
     */
    public function update($entity, array $data = []): Form
    {
        switch (true) {
            case is_string($entity) && Uuid::isValid($entity):
            case is_object($entity) && is_a($entity, Uuid::class):
                $entity = $this->service->findOneByUuid((string) $entity);

                break;
        }

        if (is_object($entity) && is_a($entity, Form::class)) {
            $default = [
                'title' => '',
                'address' => '',
                'template' => '',
                'save_data' => true,
                'recaptcha' => true,
                'origin' => [],
                'mailto' => [],
            ];
            $data = array_merge($default, $data);

            if ($data !== $default) {
                if ($data['title']) {
                    $found = $this->service->findOneByTitle($data['title']);

                    if ($found === null || $found === $entity) {
                        $entity->setTitle($data['title']);
                    } else {
                        throw new TitleAlreadyExistsException();
                    }
                }
                if ($data['address']) {
                    $found = $this->service->findOneByAddress($data['address']);

                    if ($found === null || $found === $entity) {
                        $entity->setAddress($data['address']);
                    } else {
                        throw new AddressAlreadyExistsException();
                    }
                }
                if ($data['template']) {
                    $entity->setTemplate($data['template']);
                }
                if ($data['save_data']) {
                    $entity->setSaveData($data['save_data']);
                }
                if ($data['recaptcha']) {
                    $entity->setRecaptcha($data['recaptcha']);
                }
                if ($data['template']) {
                    $entity->setTemplate($data['template']);
                }
                if ($data['origin']) {
                    $entity->setOrigin($data['origin']);
                }
                if ($data['mailto']) {
                    $entity->setMailto($data['mailto']);
                }

                $this->entityManager->flush();
            }

            return $entity;
        }

        throw new FormNotFoundException();
    }

    /**
     * @param Form|string|Uuid $entity
     *
     * @throws FormNotFoundException
     *
     * @return bool
     */
    public function delete($entity): bool
    {
        switch (true) {
            case is_string($entity) && Uuid::isValid($entity):
            case is_object($entity) && is_a($entity, Uuid::class):
                $entity = $this->service->findOneByUuid((string) $entity);

                break;
        }

        if (is_object($entity) && is_a($entity, Form::class)) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            return true;
        }

        throw new FormNotFoundException();
    }
}
