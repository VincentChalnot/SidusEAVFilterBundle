<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVFilterBundle\Filter\EAVFilter;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Filter\Type\TextFilterType as BaseTextFilterType;
use Symfony\Component\Form\FormInterface;

/**
 * Replaces the standard TextFilterType
 */
class TextFilterType extends BaseTextFilterType
{
    /**
     * @param FilterInterface $filter
     * @param FormInterface   $form
     * @param QueryBuilder    $qb
     * @param string          $alias
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     */
    public function handleForm(FilterInterface $filter, FormInterface $form, QueryBuilder $qb, $alias)
    {
        parent::handleForm($filter, $form, $qb, $alias);

        $data = $form->getData();
        if (!$form->isSubmitted() || null === $data || !$filter instanceof EAVFilter) {
            return;
        }

        $eavQb = new EAVQueryBuilder($qb, $alias);
        $dqlHandlers = [];
        foreach ($filter->getEAVAttributes() as $attribute) {
            $attributeQb = $eavQb->attribute($attribute);
            $dqlHandlers[] = $attributeQb->like('%'.trim($data, '%').'%');

            // Specific case for default values
            if ($attribute->getDefault() === $data) {
                $attributeQb = clone $attributeQb;
                $dqlHandlers[] = $attributeQb->isNull();
            }
        }

        if (0 < count($dqlHandlers)) {
            $eavQb->apply($eavQb->getOr($dqlHandlers));
        }
    }
}
