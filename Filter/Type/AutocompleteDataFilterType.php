<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Filter\FilterInterface;

/**
 * Autocomplete filter for data
 */
class AutocompleteDataFilterType extends ChoiceFilterType
{
    /**
     * {@inheritdoc}
     * @throws \UnexpectedValueException
     */
    public function getFormOptions(FilterInterface $filter, QueryBuilder $qb, $alias)
    {
        if (count($filter->getAttributes()) > 1) {
            throw new \UnexpectedValueException(
                "Autocomplete filters does not support multiple attributes ({$filter->getCode()})"
            );
        }
        /** @var FamilyInterface $currentFamily */
        $currentFamily = $filter->getOptions()['family'];
        $attribute = $currentFamily->getAttribute(current($filter->getAttributes()));

        return [
            'attribute' => $attribute,
        ];
    }
}
