<?php

namespace Sidus\EAVFilterBundle\Filter;

use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Filter\Filter as BaseFilter;

/**
 * Overrides base filter class to handles the specific needs of the EAV model
 */
class EAVFilter extends BaseFilter
{
    /**
     * @throws \UnexpectedValueException
     *
     * @return FamilyInterface|null
     */
    public function getFamily()
    {
        return $this->getOptions()['family'] ?? null;
    }

    /**
     * @param string $alias
     *
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    public function getFullAttributeReferences($alias)
    {
        $references = [];
        foreach ($this->getAttributes() as $attribute) {
            if ($this->isEAVAttribute($attribute)) {
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
     * @param string $attributePath
     *
     * @throws \UnexpectedValueException
     *
     * @return bool
     */
    protected function isEAVAttribute($attributePath)
    {
        $family = $this->getFamily();
        if (!$family) {
            return false;
        }

        foreach (explode('.', $attributePath) as $attributeCode) {
            if ($family->hasAttribute($attributeCode)) {
                return true;
            }
        }

        return false;
    }
}
