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

/**
 * Simple test to check if column has values
 */
class NotNullFilterType extends AbstractSimpleFilterType
{
    /**
     * {@inheritDoc}
     */
    protected function applyAttributeQueryBuilder(
        EAVQueryBuilderInterface $eavQb,
        AttributeQueryBuilderInterface $attributeQb,
        $data
    ): DQLHandlerInterface {
        return $attributeQb->isNotNull();
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    protected function isEmpty($data): bool
    {
        return empty($data);
    }
}
