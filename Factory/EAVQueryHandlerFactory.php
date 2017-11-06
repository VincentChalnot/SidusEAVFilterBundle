<?php

namespace Sidus\EAVFilterBundle\Factory;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Sidus\EAVFilterBundle\Configuration\EAVQueryHandler;
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

    /**
     * @param FilterTypeRegistry $filterTypeRegistry
     * @param Registry           $doctrine
     * @param FamilyRegistry     $familyRegistry
     */
    public function __construct(
        FilterTypeRegistry $filterTypeRegistry,
        Registry $doctrine,
        FamilyRegistry $familyRegistry
    ) {
        $this->doctrine = $doctrine;
        $this->familyRegistry = $familyRegistry;
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
            $this->familyRegistry
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
