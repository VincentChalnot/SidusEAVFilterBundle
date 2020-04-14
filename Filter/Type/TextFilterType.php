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
 * Replaces the standard TextFilterType
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class TextFilterType extends AbstractSimpleFilterType
{
    /**
     * {@inheritDoc}
     */
    protected function applyAttributeQueryBuilder(
        EAVQueryBuilderInterface $eavQb,
        AttributeQueryBuilderInterface $attributeQb,
        $data
    ): DQLHandlerInterface {
        return $attributeQb->like('%'.trim($data, '%').'%');
    }
}
