<?php declare(strict_types=1);

namespace App\Domain;

use App\Domain\Service\Parameter\ParameterService;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Collection;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractComponent
{
    protected ?ContainerInterface $container = null;

    protected ?EntityManager $entityManager = null;

    protected ?LoggerInterface $logger = null;

    public function __construct(ContainerInterface $container = null, EntityManager $entityManager = null, LoggerInterface $logger = null)
    {
        if ($container) {
            $this->container = $container;
            $this->entityManager = $container->get(EntityManager::class);
            $this->logger = $container->get('monolog');
        } else {
            if ($entityManager) {
                $this->entityManager = $entityManager;
            }

            if ($logger) {
                $this->logger = $logger;
            }
        }
    }

    /**
     * Returns the value of the parameter by the passed key
     * If an array of keys is passed, returns an array of found keys and their values
     *
     * @param null|string|string[] $key
     * @param mixed                $default
     *
     * @return null|array|Collection|string
     */
    protected function parameter($key = null, $default = null)
    {
        static $parameters;

        if (!$parameters) {
            if (!$this->container) {
                global $app;

                if ($app) {
                    $this->container = $app->getContainer();
                }
            }
            if ($this->container) {
                \RunTracy\Helpers\Profiler\Profiler::start('parameters');
                $parameters = ParameterService::getWithContainer($this->container)->read();
                \RunTracy\Helpers\Profiler\Profiler::finish('parameters');
            }
        }

        if ($parameters) {
            if ($key === null) {
                return $parameters->mapWithKeys(function ($item) {
                    [$group, $key] = explode('_', $item->key, 2);

                    return [$group . '[' . $key . ']' => $item];
                });
            }
            if (is_string($key)) {
                return ($buf = $parameters->firstWhere('key', $key)) ? $buf->getValue() : $default;
            }

            return $parameters->whereIn('key', $key)->pluck('value', 'key')->all() ?? $default;
        }

        return $default;
    }
}
