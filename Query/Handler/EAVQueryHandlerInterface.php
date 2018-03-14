<?php

namespace Sidus\EAVFilterBundle\Query\Handler;

use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Query\Handler\Doctrine\DoctrineQueryHandlerInterface;

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
     * @return AttributeInterface[]
     */
    public function getEAVAttributes(FilterInterface $filter): array;

    /**
     * @param FilterInterface $filter
     *
     * @return bool
     */
    public function isEAVFilter(FilterInterface $filter): bool;
}
