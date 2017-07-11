<?php

namespace Sidus\EAVFilterBundle\Filter;

use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Filter\Filter as BaseFilter;

/**
 * Overrides base filter class to handles the specific needs of the EAV model
 */
class EAVFilter extends BaseFilter
{
    /**
     * @param string $alias
     *
     * @return array
     */
    public function getFullAttributeReferences($alias)
    {
        $family = $this->getOptions()['family'] ?? null;
        $references = [];
        foreach ($this->getAttributes() as $attribute) {
            if ($family instanceof FamilyInterface && $family->hasAttribute($attribute)) {
                continue;
            }
            if (false === strpos($attribute, '.')) {
                $references[] = $alias.'.'.$attribute;
            } else {
                $references[] = $attribute;
            }
        }

        return $references;
    }

    /**
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     *
     * @return AttributeInterface[]
     */
    public function getEAVAttributes()
    {
        $family = $this->getOptions()['family'] ?? null;
        if (!$family instanceof FamilyInterface) {
            return [];
        }

        $attributes = [];
        foreach ($this->getAttributes() as $attributeCode) {
            if ($family->hasAttribute($attributeCode)) {
                $attributes[] = $family->getAttribute($attributeCode);
            }
        }

        return $attributes;
    }
}
