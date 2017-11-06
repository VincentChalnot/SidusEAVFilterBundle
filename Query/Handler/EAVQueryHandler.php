<?php

namespace Sidus\EAVFilterBundle\Configuration;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
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

    /**
     * @param FilterTypeRegistry                 $filterTypeRegistry
     * @param QueryHandlerConfigurationInterface $configuration
     * @param Registry                           $doctrine
     * @param FamilyRegistry                     $familyRegistry
     *
     * @throws \UnexpectedValueException
     */
    public function __construct(
        FilterTypeRegistry $filterTypeRegistry,
        QueryHandlerConfigurationInterface $configuration,
        Registry $doctrine,
        FamilyRegistry $familyRegistry
    ) {
        AbstractQueryHandler::__construct($filterTypeRegistry, $configuration);
        $this->doctrine = $doctrine;
        $this->entityReference = $this->getFamily()->getDataClass();
        $this->repository = $doctrine->getRepository($this->entityReference);
        $this->familyRegistry = $familyRegistry;
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
        $family = $this->getFamily();
        $attributeQueryBuilder = null;
        $attribute = null;
        /**
         * @var AttributeInterface             $attribute
         * @var AttributeQueryBuilderInterface $attributeQueryBuilder
         */
        foreach (explode('.', $attributePath) as $attributeCode) {
            if (null !== $attribute) { // This means we're in a nested attribute
                $families = $attribute->getOption('allowed_families', []);
                if (1 !== count($families)) {
                    throw new \UnexpectedValueException(
                        "Bad 'allowed_families' configuration for attribute {$attribute->getCode()}"
                    );
                }
                $family = $this->familyRegistry->getFamily(reset($families));
                $eavQueryBuilder = $attributeQueryBuilder->join();
            }
            $attribute = $family->getAttribute($attributeCode);
            $attributeQueryBuilder = $eavQueryBuilder->attribute($attribute);
        }

        return $attributeQueryBuilder;
    }

    /**
     * @param FilterInterface $filter
     *
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    public function getEAVAttributes(FilterInterface $filter): array
    {
        $family = $this->getFamily();
        $attributes = [];
        foreach ($filter->getAttributes() as $attributePath) {
            $attribute = null;
            /** @var AttributeInterface $attribute */
            foreach (explode('.', $attributePath) as $attributeCode) {
                if (null !== $attribute) { // This means we're in a nested attribute
                    $families = $attribute->getOption('allowed_families', []);
                    if (1 !== count($families)) {
                        throw new \UnexpectedValueException(
                            "Bad 'allowed_families' configuration for attribute {$attribute->getCode()}"
                        );
                    }
                    $family = $this->familyRegistry->getFamily(reset($families));
                    $attribute = $family->getAttribute($attributeCode); // No check on attribute existence: crash
                } else { // else we're at root level
                    if (!$family->hasAttribute($attributeCode)) {
                        break; // Skip attribute if not EAV
                    }
                    $attribute = $family->getAttribute($attributeCode);
                }
            }

            if ($attribute) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
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
     * @param QueryBuilder $qb
     * @param SortConfig   $sortConfig
     *
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     * @throws \UnexpectedValueException
     */
    protected function applySort(QueryBuilder $qb, SortConfig $sortConfig)
    {
        $column = $sortConfig->getColumn();

        if (!$column || !$this->getFamily()->hasAttribute($column)) {
            parent::applySort($qb, $sortConfig);

            return;
        }

        $attribute = $this->getFamily()->getAttribute($column);
        $eavQb = new EAVQueryBuilder($qb, $this->alias);
        $eavQb->addOrderBy($eavQb->attribute($attribute), $sortConfig->getDirection() ? 'DESC' : 'ASC');
    }
}
