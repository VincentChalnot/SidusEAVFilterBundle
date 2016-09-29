<?php

namespace Sidus\EAVFilterBundle\Events;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use FOS\ElasticaBundle\Event\TransformEvent;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DataValuesSubscriber implements EventSubscriberInterface
{
    /** @var Registry */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            TransformEvent::POST_TRANSFORM => 'addCustomProperty',
        ];
    }

    /**
     * @param TransformEvent $event
     *
     * @throws \InvalidArgumentException
     * @throws InvalidException
     */
    public function addCustomProperty(TransformEvent $event)
    {
        /** @var Document $document */
        $document = $event->getDocument();
        $data = $event->getObject();
        if (!$data instanceof DataInterface) {
            return;
        }
        foreach ($data->getFamily()->getAttributes() as $attribute) {
            if ($document->has($attribute->getCode())) {
                continue;
            }
            if ($attribute->isMultiple()) {
                $values = $data->getValuesData($attribute);
                if ($values instanceof ArrayCollection) {
                    $values = $values->toArray();
                }
                $document->set($attribute->getCode(), $this->parseAttributeValues($values));
            } else {
                $value = $data->getValueData($attribute);
                $document->set($attribute->getCode(), $this->parseAttributeValue($value));
            }
        }
    }

    /**
     * @param array $values
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function parseAttributeValues(array $values)
    {
        $result = [];
        foreach ($values as $key => $value) {
            $result[$key] = $this->parseAttributeValue($value);
        }

        return $result;
    }

    /**
     * @param mixed $value
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function parseAttributeValue($value)
    {
        if (is_scalar($value) || null === $value) {
            return $value;
        }
        if (!is_object($value)) {
            return $value; // @todo fixme How to parse this ?
        }

        $class = get_class($value);
        try {
            $metadata = $this->getManager()->getClassMetadata($class);
        } catch (MappingException $e) {
            return $value; // @todo fixme How to parse this ?
        }

        $identifier = $metadata->getIdentifierValues($value);

        return array_pop($identifier);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->doctrine->getManager();
    }
}
