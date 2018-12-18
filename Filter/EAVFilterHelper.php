<?php
/*
 * This file is part of the Sidus/EAVFilterBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\EAVFilterBundle\Filter;

use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\EAVModelBundle\Exception\MissingAttributeException;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;

/**
 * Common EAV logic to work with filters
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class EAVFilterHelper
{
    /** @var FamilyRegistry */
    protected $familyRegistry;

    /**
     * @param FamilyRegistry $familyRegistry
     */
    public function __construct(FamilyRegistry $familyRegistry)
    {
        $this->familyRegistry = $familyRegistry;
    }

    /**
     * @param EAVQueryBuilderInterface $eavQueryBuilder
     * @param FamilyInterface          $family
     * @param string                   $attributePath
     * @param bool                     $enforceFamilyCondition
     *
     * @throws \UnexpectedValueException
     *
     * @return AttributeQueryBuilderInterface
     */
    public function getEAVAttributeQueryBuilder(
        EAVQueryBuilderInterface $eavQueryBuilder,
        FamilyInterface $family,
        $attributePath,
        $enforceFamilyCondition = true
    ): AttributeQueryBuilderInterface {
        $attributeQueryBuilder = null;
        $attribute = null;
        /**
         * @var AttributeInterface             $attribute
         * @var AttributeQueryBuilderInterface $attributeQueryBuilder
         */
        foreach (explode('.', $attributePath) as $attributeCode) {
            if (null !== $attribute) { // This means we're in a nested attribute
                $families = $attribute->getOption('allowed_families', []);
                if (1 !== \count($families)) {
                    throw new \UnexpectedValueException(
                        "Bad 'allowed_families' configuration for attribute {$attribute->getCode()}"
                    );
                }
                $family = $this->familyRegistry->getFamily(reset($families));
                $eavQueryBuilder = $attributeQueryBuilder->join();
            }
            $attribute = $family->getAttribute($attributeCode);
            $attributeQueryBuilder = $eavQueryBuilder->attribute($attribute, $enforceFamilyCondition);
        }

        return $attributeQueryBuilder;
    }

    /**
     * @param FamilyInterface $family
     * @param array           $attributePaths
     *
     * @throws \UnexpectedValueException
     * @throws MissingAttributeException
     * @throws MissingFamilyException
     *
     * @return AttributeInterface[]
     */
    public function getEAVAttributes(FamilyInterface $family, array $attributePaths): array
    {
        $attributes = [];
        foreach ($attributePaths as $attributePath) {
            $attribute = null;
            /** @var AttributeInterface $attribute */
            foreach (explode('.', $attributePath) as $attributeCode) {
                if (null !== $attribute) { // This means we're in a nested attribute
                    $families = $attribute->getOption('allowed_families', []);
                    if (1 !== \count($families)) {
                        throw new \UnexpectedValueException(
                            "Bad 'allowed_families' configuration for attribute {$attribute->getCode()}"
                        );
                    }
                    $family = $this->familyRegistry->getFamily(reset($families));
                    $attribute = $family->getAttribute($attributeCode); // No check on attribute existence: crash
                } else { // else we're at root level
                    $attribute = $family->getAttribute($attributeCode);
                }
            }

            if ($attribute) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }
}
