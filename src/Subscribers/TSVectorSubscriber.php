<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Subscribers;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use JuniWalk\ORM\Mapping\TSVector;

final class TSVectorSubscriber implements EventSubscriber
{
	public function getSubscribedEvents()
	{
		return [Events::loadClassMetadata];
	}


    public function loadClassMetadata(EventArgs $event)
    {
		$metadata = $event->getClassMetadata();

		foreach ($metadata->getFieldNames() as $fieldName) {
			$mapping = $metadata->getFieldMapping($fieldName);
			$attribute = $metadata->getReflectionClass()->getProperty($fieldName)
				->getAttributes(TSVector::class);

			if (!$attribute || $mapping['type'] <> TSVectorType::TYPE) {
				continue;
			}

			$mapping['generated'] = 2;
			$mapping['default'] = null;
			$mapping['nullable'] = false;
			$mapping['notUpdatable'] = true;
			$mapping['notInsertable'] = true;
			$mapping['columnDefinition'] = $attribute[0]->newInstance()
				->createDefinition($mapping['type'], $metadata);

			$metadata->setAttributeOverride($fieldName, $mapping);
		}
    }
}
