<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;

/**
 * Replaces the standard TextFilterType
 */
class AdvancedNumberFilterType extends AbstractSimpleFilterType
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
            case 'greaterthan':
                return $attributeQb->gt($input);
            case 'lowerthan':
                return $attributeQb->lt($input);
            case 'greaterthanequals':
                return $attributeQb->gte($input);
            case 'lowerthanequals':
                return $attributeQb->lte($input);
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
