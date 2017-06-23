<?php

namespace Sidus\EAVFilterBundle\Configuration;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Configuration\FilterConfigurationHandler;
use Sidus\FilterBundle\DTO\SortConfig;
use Sidus\FilterBundle\Filter\FilterFactory;
use UnexpectedValueException;

/**
 * Handles filtering on EAV model
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 *
 * @property \Sidus\EAVModelBundle\Entity\DataRepository $repository
 */
class EAVFilterConfigurationHandler extends FilterConfigurationHandler
{
    /** @var FamilyInterface */
    protected $family;

    /** @var string */
    protected $valueAlias;

    /**
     * @param string                     $code
     * @param Registry                   $doctrine
     * @param FilterFactory              $filterFactory
     * @param array                      $configuration
     * @param FamilyRegistry $familyRegistry
     *
     * @throws UnexpectedValueException
     */
    public function __construct(
        $code,
        Registry $doctrine,
        FilterFactory $filterFactory,
        array $configuration,
        FamilyRegistry $familyRegistry
    ) {
        if (!$familyRegistry->hasFamily($configuration['family'])) {
            throw new UnexpectedValueException("Unknown family '{$configuration['family']}'");
        }
        $this->family = $familyRegistry->getFamily($configuration['family']);
        unset($configuration['family']);
        $configuration['entity'] = $this->family->getDataClass();
        parent::__construct($code, $doctrine, $filterFactory, $configuration);
    }

    /**
     * @return FamilyInterface
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @param FamilyInterface $family
     *
     * @return EAVFilterConfigurationHandler
     */
    public function setFamily($family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * @param string $alias
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder($alias = 'e')
    {
        if (!$this->queryBuilder) {
            $this->alias = $alias;
            if ($this->family) {
                $this->queryBuilder = $this->repository->createOptimizedQueryBuilder($alias);
                $familyParam = uniqid('family', false);
                $this->queryBuilder
                    ->andWhere("{$alias}.family = :{$familyParam}")
                    ->setParameter($familyParam, $this->family->getCode());
            } else {
                $this->queryBuilder = $this->repository->createQueryBuilder($alias);
            }
        }

        return $this->queryBuilder;
    }

    /**
     * @param array $configuration
     *
     * @throws UnexpectedValueException
     */
    protected function parseConfiguration(array $configuration)
    {
        foreach ($configuration['fields'] as $code => $field) {
            $configuration['fields'][$code]['options']['family'] = $this->family;
        }
        parent::parseConfiguration($configuration);
    }

    /**
     * @param QueryBuilder $qb
     * @param SortConfig   $sortConfig
     *
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     */
    protected function applySort(QueryBuilder $qb, SortConfig $sortConfig)
    {
        $column = $sortConfig->getColumn();

        if (!$column || !$this->family->hasAttribute($column)) {
            parent::applySort($qb, $sortConfig);

            return;
        }

        $attribute = $this->family->getAttribute($column);
        $eavQb = new EAVQueryBuilder($qb, $this->alias);
        $eavQb->addOrderBy($eavQb->attribute($attribute), $sortConfig->getDirection() ? 'DESC' : 'ASC');
    }
}
