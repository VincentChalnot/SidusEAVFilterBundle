<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVFilterBundle\Configuration\EAVQueryHandlerInterface;
use Sidus\EAVFilterBundle\Filter\EAVFilter;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\Doctrine\DoctrineFilterInterface;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;

/**
 * Autocomplete filter for data
 */
class AutocompleteDataFilterType extends ChoiceFilterType
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /**
     * @param FamilyRegistry $familyRegistry
     */
    public function setFamilyRegistry(FamilyRegistry $familyRegistry)
    {
        $this->familyRegistry = $familyRegistry;
    }

    /**
     * {@inheritdoc}
     * @throws \UnexpectedValueException
     */
    public function getFormOptions(QueryHandlerInterface $queryHandler, FilterInterface $filter): array
    {
        if (!$queryHandler instanceof EAVQueryHandlerInterface) {
            throw new BadQueryHandlerException($queryHandler, EAVQueryHandlerInterface::class);
        }

        $eavAttributes = $queryHandler->getEAVAttributes($filter);
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
