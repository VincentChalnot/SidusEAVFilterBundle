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

use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;

/**
 * Replaces the standard ExactFilterType
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class ExactFilterType extends AbstractSimpleFilterType
{
    /**
     * @param AttributeQueryBuilderInterface $attributeQb
     * @param mixed                          $data
     *
     * @return AttributeQueryBuilderInterface
     */
    protected function applyAttributeQueryBuilder(
        AttributeQueryBuilderInterface $attributeQb,
        $data
    ): AttributeQueryBuilderInterface {
        return $attributeQb->equals($data);
    }
}
