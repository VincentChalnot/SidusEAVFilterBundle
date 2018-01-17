<?php

namespace Sidus\EAVFilterBundle\Query\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVFilterBundle\Filter\EAVFilterHelper;
use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Exception\MissingAttributeException;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\DTO\SortConfig;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Query\Handler\AbstractQueryHandler;
use Sidus\FilterBundle\Query\Handler\Configuration\QueryHandlerConfigurationInterface;
use Sidus\FilterBundle\Query\Handler\Doctrine\DoctrineQueryHandler;
use Sidus\FilterBundle\Registry\FilterTypeRegistry;
use UnexpectedValueException;

/**
 * Handles filtering on EAV model
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 *
 * @property DataRepository $repository
 */
class EAVQueryHandler extends DoctrineQueryHandler implements EAVQueryHandlerInterface
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var EAVFilterHelper */
    protected $filterHelper;

    /**
     * @param FilterTypeRegistry                 $filterTypeRegistry
     * @param QueryHandlerConfigurationInterface $configuration
     * @param Registry                           $doctrine
     * @param FamilyRegistry                     $familyRegistry
     * @param EAVFilterHelper                    $filterHelper
     *
     * @throws \UnexpectedValueException
     */
    public function __construct(
        FilterTypeRegistry $filterTypeRegistry,
        QueryHandlerConfigurationInterface $configuration,
        Registry $doctrine,
        FamilyRegistry $familyRegistry,
        EAVFilterHelper $filterHelper
    ) {
        AbstractQueryHandler::__construct($filterTypeRegistry, $configuration);
        $this->doctrine = $doctrine;
        $this->familyRegistry = $familyRegistry;
        $this->filterHelper = $filterHelper;
        $this->entityReference = $this->getFamily()->getDataClass();
        $this->repository = $doctrine->getRepository($this->entityReference);
    }

    /**
     * @throws \UnexpectedValueException
     *
     * @return FamilyInterface
     */
    public function getFamily(): FamilyInterface
    {
        $familyCode = $this->getConfiguration()->getOption('family');
        if (null === $familyCode) {
            throw new UnexpectedValueException(
                "Missing 'family' configuration option for {$this->getConfiguration()->getCode()}"
            );
        }

        return $this->familyRegistry->getFamily($familyCode);
    }

    /**
     * @param EAVQueryBuilderInterface $eavQueryBuilder
     * @param string                   $attributePath
     *
     * @throws \UnexpectedValueException
     *
     * @return AttributeQueryBuilderInterface
     */
    public function getEAVAttributeQueryBuilder(
        EAVQueryBuilderInterface $eavQueryBuilder,
        $attributePath
    ): AttributeQueryBuilderInterface {
        return $this->filterHelper->getEAVAttributeQueryBuilder($eavQueryBuilder, $this->getFamily(), $attributePath);
    }

    /**
     * @param FilterInterface $filter
     *
     * @throws \UnexpectedValueException
     *
     * @return AttributeInterface[]
     */
    public function getEAVAttributes(FilterInterface $filter): array
    {
        return $this->filterHelper->getEAVAttributes($this->getFamily(), $filter->getAttributes());
    }

    /**
     * @param FilterInterface $filter
     *
     * @throws \UnexpectedValueException
     *
     * @return bool
     */
    public function isEAVFilter(FilterInterface $filter): bool
    {
        try {
            $this->getEAVAttributes($filter);

            return true;
        } catch (MissingAttributeException $e) {
            return false;
        }
    }

    /**
     * @throws \UnexpectedValueException
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        if (!$this->queryBuilder) {
            $this->queryBuilder = $this->repository->createOptimizedQueryBuilder($this->getAlias());
            $familyParam = uniqid('family', false);
            $this->queryBuilder
                ->andWhere("{$this->getAlias()}.family = :{$familyParam}")
                ->setParameter($familyParam, $this->getFamily()->getCode());
        }

        return $this->queryBuilder;
    }

    /**
     * @param SortConfig $sortConfig
     *
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     * @throws \UnexpectedValueException
     */
    protected function applySort(SortConfig $sortConfig)
    {
        $column = $sortConfig->getColumn();

        if (!$column || !$this->getFamily()->hasAttribute($column)) {
            parent::applySort($sortConfig);

            return;
        }

        $attribute = $this->getFamily()->getAttribute($column);
        $eavQb = new EAVQueryBuilder($this->getQueryBuilder(), $this->alias);
        $eavQb->addOrderBy($eavQb->attribute($attribute), $sortConfig->getDirection() ? 'DESC' : 'ASC');
    }
}
