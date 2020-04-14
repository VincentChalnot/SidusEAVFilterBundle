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
class AdvancedTextFilterType extends AbstractSimpleFilterType
{
    protected const EMPTY_OPTIONS = ['empty', 'notempty', 'null', 'notnull'];

    /**
     * {@inheritDoc}
     */
    protected function applyAttributeQueryBuilder(
        EAVQueryBuilderInterface $eavQb,
        AttributeQueryBuilderInterface $attributeQb,
        $data
    ): DQLHandlerInterface {
        $input = $data['input'];
        switch ($data['option']) {
            case 'exact':
                return $attributeQb->equals($input);
            case 'like_':
                return $attributeQb->like(trim($input, '%').'%');
            case '_like':
                return $attributeQb->like('%'.trim($input, '%'));
            case '_like_':
                return $attributeQb->like('%'.trim($input, '%').'%');
            case 'notlike_':
                return $attributeQb->notLike(trim($input, '%').'%');
            case '_notlike':
                return $attributeQb->notLike('%'.trim($input, '%'));
            case '_notlike_':
                return $attributeQb->notLike('%'.trim($input, '%').'%');
            case 'empty':
                return $attributeQb->equals('');
            case 'notempty':
                return $attributeQb->notEquals('');
            case 'null':
                return $attributeQb->isNull();
            case 'notnull':
                return $attributeQb->isNotNull();
        }
        throw new \UnexpectedValueException("Unknown option '{$data['option']}'");
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    protected function isEmpty($data): bool
    {
        // Handle specific cases where input can be blank
        if (array_key_exists('option', $data) && in_array($data['option'], static::EMPTY_OPTIONS, true)) {
            return false;
        }

        return parent::isEmpty($data) || empty($data['input']);
    }
}
