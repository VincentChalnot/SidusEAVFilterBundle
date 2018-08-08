<?php

namespace Sidus\EAVFilterBundle\Query\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Sidus\EAVFilterBundle\Filter\EAVFilterHelper;
use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\DataLoaderInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Exception\MissingAttributeException;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVFilterBundle\Pager\Adapter\EAVAdapter;
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

    /** @var DataLoaderInterface */
    protected $dataLoader;

    /**
     * @param FilterTypeRegistry                 $filterTypeRegistry
     * @param QueryHandlerConfigurationInterface $configuration
     * @param EntityManagerInterface             $entityManager
     * @param FamilyRegistry                     $familyRegistry
     * @param EAVFilterHelper                    $filterHelper
     * @param DataLoaderInterface                $dataLoader
     *
     * @throws \UnexpectedValueException
     */
    public function __construct(
        FilterTypeRegistry $filterTypeRegistry,
        QueryHandlerConfigurationInterface $configuration,
        EntityManagerInterface $entityManager,
        FamilyRegistry $familyRegistry,
        EAVFilterHelper $filterHelper,
        DataLoaderInterface $dataLoader
    ) {
        AbstractQueryHandler::__construct($filterTypeRegistry, $configuration);
        $this->familyRegistry = $familyRegistry;
        $this->filterHelper = $filterHelper;
        $this->dataLoader = $dataLoader;
        $this->entityReference = $this->getFamily()->getDataClass();
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository($this->entityReference);
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
            $this->queryBuilder = $this->repository->createQueryBuilder($this->getAlias());
            $familyParam = uniqid('family', false);
            $this->queryBuilder
                ->andWhere("{$this->getAlias()}.family = :{$familyParam}")
                ->setParameter($familyParam, $this->getFamily()->getCode());
        }

        return $this->queryBuilder;
    }

    /**
     * @throws \UnexpectedValueException
     *
     * @return array|null
     */
    public function getContext(): ?array
    {
        $context = $this->getConfiguration()->getOption('context');
        if ($context || $this->getConfiguration()->getOption('use_global_context')) {
            $context = array_merge($this->getFamily()->getContext(), (array) $context);
        }

        return $context;
    }

    /**
     * @param SortConfig $sortConfig
     *
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     * @throws \UnexpectedValueException
     */
    protected function applySort(SortConfig $sortConfig)
    {
        $attributePath = $sortConfig->getColumn();
        if (!$attributePath) {
            return;
        }

        $rootAttribute = explode('.', $attributePath)[0];
        if (!$rootAttribute || !$this->getFamily()->hasAttribute($rootAttribute)) {
            parent::applySort($sortConfig);

            return;
        }

        $qb = $this->getQueryBuilder();
        $eavQb = new EAVQueryBuilder($qb, $this->getAlias());
        $eavQb->setContext($this->getContext());
        $attributeQb = $this->getEAVAttributeQueryBuilder($eavQb, $attributePath);
        $qb->addOrderBy($attributeQb->applyJoin()->getColumn(), $sortConfig->getDirection() ? 'DESC' : 'ASC');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \UnexpectedValueException
     */
    protected function createPager(): Pagerfanta
    {
        return new Pagerfanta(
            EAVAdapter::create(
                $this->dataLoader,
                $this->getQueryBuilder(),
                $this->configuration->getOption('loader_depth', 2)
            )
        );
    }
}
