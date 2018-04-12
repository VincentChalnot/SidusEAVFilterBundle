<?php

namespace Sidus\EAVFilterBundle\Factory;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Sidus\EAVFilterBundle\Filter\EAVFilterHelper;
use Sidus\EAVFilterBundle\Query\Handler\EAVQueryHandler;
use Sidus\EAVModelBundle\Doctrine\DataLoaderInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\FilterBundle\Factory\QueryHandlerFactoryInterface;
use Sidus\FilterBundle\Query\Handler\Configuration\QueryHandlerConfigurationInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;
use Sidus\FilterBundle\Registry\FilterTypeRegistry;

/**
 * Builds query handler for eav filtering
 */
class EAVQueryHandlerFactory implements QueryHandlerFactoryInterface
{
    /** @var FilterTypeRegistry */
    protected $filterTypeRegistry;

    /** @var Registry */
    protected $doctrine;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var EAVFilterHelper */
    protected $filterHelper;

    /** @var DataLoaderInterface */
    protected $dataLoader;

    /**
     * @param FilterTypeRegistry           $filterTypeRegistry
     * @param Registry                     $doctrine
     * @param FamilyRegistry               $familyRegistry
     * @param EAVFilterHelper              $filterHelper
     * @param DataLoaderInterface $dataLoader
     */
    public function __construct(
        FilterTypeRegistry $filterTypeRegistry,
        Registry $doctrine,
        FamilyRegistry $familyRegistry,
        EAVFilterHelper $filterHelper,
        DataLoaderInterface $dataLoader
    ) {
        $this->filterTypeRegistry = $filterTypeRegistry;
        $this->doctrine = $doctrine;
        $this->familyRegistry = $familyRegistry;
        $this->filterHelper = $filterHelper;
        $this->dataLoader = $dataLoader;
    }

    /**
     * @param QueryHandlerConfigurationInterface $queryHandlerConfiguration
     *
     * @throws \UnexpectedValueException
     *
     * @return QueryHandlerInterface
     */
    public function createQueryHandler(
        QueryHandlerConfigurationInterface $queryHandlerConfiguration
    ): QueryHandlerInterface {
        return new EAVQueryHandler(
            $this->filterTypeRegistry,
            $queryHandlerConfiguration,
            $this->doctrine,
            $this->familyRegistry,
            $this->filterHelper,
            $this->dataLoader
        );
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return 'sidus.eav';
    }
}
