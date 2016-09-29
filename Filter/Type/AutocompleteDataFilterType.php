<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Elastica\Query;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Filter\FilterInterface;

class AutocompleteDataFilterType extends ChoiceFilterType
{
    /**
     * @inheritDoc
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
            'family' => $attribute->getFormOptions()['family'],
        ];
    }
}
