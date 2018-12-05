<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;

/**
 * Replaces the standard TextFilterType
 */
class AdvancedTextFilterType extends AbstractSimpleFilterType
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
}
