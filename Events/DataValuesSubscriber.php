<?php

namespace Sidus\EAVFilterBundle\Events;

use Doctrine\Common\Collections\ArrayCollection;
use Elastica\Document;
use FOS\ElasticaBundle\Event\TransformEvent;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DataValuesSubscriber implements EventSubscriberInterface
{
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
     * @throws \Elastica\Exception\InvalidException
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
                $document->set($attribute->getCode(), $values);
            } else {
                $document->set($attribute->getCode(), $data->getValueData($attribute));
            }
        }
    }
}
