<?php
/*
 * This file is part of the Sidus/EAVFilterBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\EAVFilterBundle\Filter\Type;

use Sidus\EAVFilterBundle\Query\Handler\EAVQueryHandlerInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Form\Type\DateRangeType;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;

/**
 * Replaces the standard DateRangeFilterType
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DateRangeFilterType extends AbstractEAVFilterType
{
    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function handleData(QueryHandlerInterface $queryHandler, FilterInterface $filter, $data): void
    {
        if (!$queryHandler instanceof EAVQueryHandlerInterface) {
            throw new BadQueryHandlerException($queryHandler, EAVQueryHandlerInterface::class);
        }
        if (!$queryHandler->isEAVFilter($filter)) {
            $this->fallbackFilterType->handleData($queryHandler, $filter, $data);

            return;
        }

        $eavQb = new EAVQueryBuilder($queryHandler->getQueryBuilder(), $queryHandler->getAlias());
        $eavQb->setContext($queryHandler->getQueryContext());
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
