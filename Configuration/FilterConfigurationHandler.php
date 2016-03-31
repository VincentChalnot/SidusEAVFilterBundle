<?php

namespace Sidus\EAVFilterBundle\Configuration;

use ArrayIterator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Entity\Data;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Configuration\FilterConfigurationHandler as BaseFilterConfigurationHandler;
use Sidus\FilterBundle\DTO\SortConfig;
use Sidus\FilterBundle\Filter\FilterFactory;
use UnexpectedValueException;

class FilterConfigurationHandler extends BaseFilterConfigurationHandler
{
    /** @var FamilyInterface */
    protected $family;

    /** @var string */
    protected $valueAlias;

    /**
     * @param string $code
     * @param Registry $doctrine
     * @param FilterFactory $filterFactory
     * @param array $configuration
     * @param FamilyConfigurationHandler $familyConfigurationHandler
     * @throws UnexpectedValueException
     */
    public function __construct($code, Registry $doctrine, FilterFactory $filterFactory, array $configuration = [], FamilyConfigurationHandler $familyConfigurationHandler)
    {

        if (!$familyConfigurationHandler->hasFamily($configuration['family'])) {
            throw new UnexpectedValueException("Unknown family '{$configuration['family']}'");
        }
        $this->family = $familyConfigurationHandler->getFamily($configuration['family']);
        unset($configuration['family']);
        $configuration['entity'] = $this->family->getDataClass();
        parent::__construct($code, $doctrine, $filterFactory, $configuration);
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    public function getQueryBuilder($alias = 'e')
    {
        if (!$this->queryBuilder) {
            $this->alias = $alias;
            $this->queryBuilder = $this->repository->createQueryBuilder($alias);
            $this->queryBuilder
                // Was supposed to be more performant but is in fact 500% slower... disabling it for the moment
//                ->addSelect('value')
//                ->leftJoin($alias . '.values', 'value') // Manual join on values
                ->andWhere("{$alias}.family IN (:families)")
                ->setParameter('families', $this->family->getMatchingCodes());
        }
        return $this->queryBuilder;
    }

    /**
     * @param array $configuration
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
     * @throws \Exception
     */
    protected function applySort(QueryBuilder $qb)
    {
        $sortConfig = $this->applySortForm();
        $column = $sortConfig->getColumn();

        if ($column) {
            $fullColumnReference = $column;
            if (false === strpos($column, '.')) {
                $fullColumnReference = $this->alias . '.' . $column;
            }
            if ($this->family->hasAttribute($column)) {
                $attribute = $this->family->getAttribute($column);
                $uid = uniqid('join');
                $fullColumnReference = $uid . '.' . $attribute->getType()->getDatabaseType();
                $qb->leftJoin($this->alias . '.values', $uid, Join::WITH,
                    "({$uid}.data = {$this->alias}.id AND ({$uid}.attributeCode = '{$attribute->getCode()}' OR {$uid}.id IS NULL))");
            }
            $direction = $sortConfig->getDirection() ? 'DESC' : 'ASC'; // null or false both default to ASC
            $qb->addOrderBy($fullColumnReference, $direction);
        }
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
     * @return FilterConfigurationHandler
     */
    public function setFamily($family)
    {
        $this->family = $family;
        return $this;
    }

    /**
     * EAV optimization: fetching all values at the same time
     * @return array|\Traversable
     */
    public function getResults()
    {
        /** @var ArrayIterator $datas */
        $datas = $this->getPager()->getCurrentPageResults();
        /** @var EntityRepository $repo */
        $repo = $this->doctrine->getRepository($this->family->getDataClass());
        // No need to actually fetch the results, the already existing data will be hydrated automatically
        $repo->createQueryBuilder('d')
            ->addSelect('v')
            ->leftJoin('d.values', 'v')
            ->where('d.id IN (:datas)')
            ->setParameter('datas', $datas->getArrayCopy())
            ->getQuery()
            ->getResult();
        return $datas;
    }
}
