<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVFilterBundle\Filter\EAVFilter;
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
        if (!$filter instanceof EAVFilter) {
            return [];
        }

        $eavAttributes = $this->eavFilterHelper->getEAVAttributes($filter);
        if (count($eavAttributes) > 1) {
            throw new \UnexpectedValueException(
                "Autocomplete filters does not support multiple attributes ({$filter->getCode()})"
            );
        }

        return [
            'attribute' => reset($eavAttributes),
        ];
    }
}
