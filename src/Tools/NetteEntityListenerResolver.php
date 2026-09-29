<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Tools;

use Doctrine\ORM\Mapping\EntityListenerResolver;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;

use function get_class;
use function is_null;

class NetteEntityListenerResolver implements EntityListenerResolver
{
	public function __construct(
		private readonly Container $container,
	) {
	}


	/**
	 * @inheritdoc
	 */
	public function clear(?string $className = null): void
	{
		if (is_null($className)) {
			return;
		}

		$this->container->removeService($className);
	}


	/**
	 * @inheritdoc
	 * @param  class-string $className
	 * @throws MissingServiceException
	 */
	public function resolve(string $className): object
	{
		return $this->container->getByType($className);
	}


	/**
	 * @inheritdoc
	 */
	public function register(object $resolver): void
	{
		$this->container->addService(get_class($resolver), $resolver);
	}
}
