<?php

namespace Sidus\EAVFilterBundle\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\FilterBundle\Filter\Filter as BaseFilter;
use Symfony\Component\Form\FormInterface;

/**
 * Overrides base filter class to handles the specific needs of the EAV model
 */
class Filter extends BaseFilter
{
    /** @var AttributeInterface[] */
    protected $attributeJoins = [];

    /** @var array */
    protected $eavReferences = [];

    /** @var bool */
    protected $isBuilt = false;

    /**
     * @param FormInterface $form
     * @param QueryBuilder  $qb
     * @param string        $alias
     */
    public function handleForm(FormInterface $form, QueryBuilder $qb, $alias)
    {
        if ($form->getData()) {
            $this->buildAttributeJoins($qb, $alias);
        }
        parent::handleForm($form, $qb, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions(QueryBuilder $qb, $alias)
    {
        $this->buildAttributeJoins($qb, $alias);

        return parent::getFormOptions($qb, $alias);
    }

    /**
     * @param string $alias
     *
     * @return array
     */
    public function getFullAttributeReferences($alias)
    {
        $references = [];
        foreach ($this->getAttributes() as $attribute) {
            if (false === strpos($attribute, '.')) {
                if (empty($this->eavReferences[$attribute])) {
                    $references[] = $alias.'.'.$attribute;
                } else {
                    $references[] = $this->eavReferences[$attribute];
                }
            } else {
                $references[] = $attribute;
            }
        }

        return $references;
    }


    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     */
    protected function buildAttributeJoins(QueryBuilder $qb, $alias)
    {
        $this->buildEAVReferences();
        foreach ($this->attributeJoins as $customAlias => $attribute) {
            if ($this->joinExists($qb, $customAlias)) {
                continue;
            }
            $qb->leftJoin(
                $alias.'.values',
                $customAlias,
                Join::WITH,
                "({$customAlias}.attributeCode = '{$attribute->getCode()}')"
            );
        }
    }

    /**
     * Build necessary attribute's references for EAV filtering
     */
    protected function buildEAVReferences()
    {
        if ($this->isBuilt) {
            return;
        }
        foreach ($this->getAttributes() as $attribute) {
            if (false !== strpos($attribute, '.')) {
                continue;
            }
            if (!empty($this->options['family']) && $this->options['family'] instanceof FamilyInterface) {
                /** @var FamilyInterface $family */
                $family = $this->options['family'];
                if ($family->hasAttribute($attribute)) {
                    $customAlias = uniqid('join');
                    $attributeType = $family->getAttribute($attribute)->getType();
                    $this->eavReferences[$attribute] = $customAlias.'.'.$attributeType->getDatabaseType();
                    $this->attributeJoins[$customAlias] = $family->getAttribute($attribute);
                }
            }
        }
        $this->isBuilt = true;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $customAlias
     *
     * @return bool
     */
    protected function joinExists(QueryBuilder $qb, $customAlias)
    {
        /** @var array $joinPart */
        $joinPart = $qb->getDQLPart('join');
        /* @var $joins Join[] */
        foreach ($joinPart as $joins) {
            foreach ($joins as $join) {
                if ($join->getAlias() === $customAlias) {
                    return true;
                }
            }
        }

        return false;
    }
}
