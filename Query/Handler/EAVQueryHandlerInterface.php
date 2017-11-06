<?php

namespace Sidus\EAVFilterBundle\Configuration;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\DTO\SortConfig;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Query\Handler\AbstractQueryHandler;
use Sidus\FilterBundle\Query\Handler\Configuration\QueryHandlerConfigurationInterface;
use Sidus\FilterBundle\Query\Handler\Doctrine\DoctrineQueryHandler;
use Sidus\FilterBundle\Query\Handler\Doctrine\DoctrineQueryHandlerInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;
use Sidus\FilterBundle\Registry\FilterTypeRegistry;
use UnexpectedValueException;

/**
 * Handles filtering on EAV model
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 *
 * @property DataRepository $repository
 */
interface EAVQueryHandlerInterface extends DoctrineQueryHandlerInterface
{

    /**
     * @return FamilyInterface
     */
    public function getFamily(): FamilyInterface;

    /**
     * @param EAVQueryBuilderInterface $eavQueryBuilder
     * @param string                   $attributePath
     *
     * @return AttributeQueryBuilderInterface
     */
    public function getEAVAttributeQueryBuilder(
        EAVQueryBuilderInterface $eavQueryBuilder,
        $attributePath
    ): AttributeQueryBuilderInterface;


    /**
     * @param FilterInterface $filter
     *
     * @return array
     */
    public function getEAVAttributes(FilterInterface $filter): array;
}
