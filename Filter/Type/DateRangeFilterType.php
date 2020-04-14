<?php
/*
 * This file is part of the Sidus/EAVFilterBundle package.
 *
 * Copyright (c) 2015-2020 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\EAVFilterBundle\Filter\Type;

use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\DQLHandlerInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\FilterBundle\Form\Type\DateRangeType;

/**
 * Replaces the standard DateRangeFilterType
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DateRangeFilterType extends AbstractSimpleFilterType
{
    /**
     * {@inheritDoc}
     */
    protected function applyAttributeQueryBuilder(
        EAVQueryBuilderInterface $eavQb,
        AttributeQueryBuilderInterface $attributeQb,
        $data
    ): DQLHandlerInterface {
        $attributeDqlHandlers = [];
        if (!empty($data[DateRangeType::START_NAME])) {
            $attributeDqlHandlers[] = $attributeQb->gte($data[DateRangeType::START_NAME]);
        }
        if (!empty($data[DateRangeType::END_NAME])) {
            if (\count($attributeDqlHandlers) > 0) {
                $attributeQb = clone $attributeQb;
            }
            $attributeDqlHandlers[] = $attributeQb->lte($data[DateRangeType::END_NAME]);
        }

        return $eavQb->getAnd($attributeDqlHandlers);
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    protected function isEmpty($data): bool
    {
        if (parent::isEmpty($data)) {
            return true;
        }

        return empty($data[DateRangeType::START_NAME]) && empty($data[DateRangeType::END_NAME]);
    }
}
