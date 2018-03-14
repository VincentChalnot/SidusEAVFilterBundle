<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Sidus\EAVFilterBundle\Query\Handler\EAVQueryHandlerInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Filter\Type\FilterTypeInterface;
use Sidus\FilterBundle\Form\Type\DateRangeType;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Replaces the standard DateRangeFilterType
 */
class DateRangeFilterType extends AbstractEAVFilterType
{
    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function handleForm(QueryHandlerInterface $queryHandler, FilterInterface $filter, FormInterface $form)
    {
        if (!$queryHandler instanceof EAVQueryHandlerInterface) {
            throw new BadQueryHandlerException($queryHandler, EAVQueryHandlerInterface::class);
        }
        if (!$queryHandler->isEAVFilter($filter)) {
            $this->fallbackFilterType->handleForm($queryHandler, $filter, $form);

            return;
        }

        $data = $form->getData();
        if (null === $data) {
            return;
        }

        $eavQb = new EAVQueryBuilder($queryHandler->getQueryBuilder(), $queryHandler->getAlias());
        $dqlHandlers = [];
        foreach ($filter->getAttributes() as $attributePath) {
            $attributeDqlHandlers = [];
            $attributeQb = null;
            if (!empty($data[DateRangeType::START_NAME])) {
                $attributeQb = $queryHandler->getEAVAttributeQueryBuilder($eavQb, $attributePath);
                $attributeDqlHandlers[] = $attributeQb->gte($data[DateRangeType::START_NAME]);
            }
            if (!empty($data[DateRangeType::END_NAME])) {
                if ($attributeQb) {
                    $attributeQb = clone $attributeQb;
                } else {
                    $attributeQb = $queryHandler->getEAVAttributeQueryBuilder($eavQb, $attributePath);
                }
                $attributeDqlHandlers[] = $attributeQb->lte($data[DateRangeType::END_NAME]);
            }
            if (0 < \count($attributeDqlHandlers)) {
                $dqlHandlers[] = $eavQb->getAnd($attributeDqlHandlers);
            }
        }

        if (0 < \count($dqlHandlers)) {
            $eavQb->apply($eavQb->getOr($dqlHandlers));
        }
    }
}
