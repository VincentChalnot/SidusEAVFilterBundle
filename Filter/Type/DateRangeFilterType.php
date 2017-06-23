<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVFilterBundle\Filter\EAVFilter;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Filter\Type\DateRangeFilterType as BaseDateRangeFilterType;
use Sidus\FilterBundle\Form\Type\DateRangeType;
use Symfony\Component\Form\FormInterface;

/**
 * Replaces the standard DateRangeFilterType
 */
class DateRangeFilterType extends BaseDateRangeFilterType
{
    /**
     * We don't handle default values for dates because it's complicated and it doesn't make much sense
     *
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
            $attributeDqlHandlers = [];
            $attributeQb = null;
            if (!empty($data[DateRangeType::START_NAME])) {
                $attributeQb = $eavQb->attribute($attribute);
                $attributeDqlHandlers[] = $attributeQb->gte($data[DateRangeType::START_NAME]);
            }
            if (!empty($data[DateRangeType::END_NAME])) {
                if ($attributeQb) {
                    $attributeQb = clone $attributeQb;
                } else {
                    $attributeQb = $eavQb->attribute($attribute);
                }
                $attributeDqlHandlers[] = $attributeQb->lte($data[DateRangeType::END_NAME]);
            }
            if (0 < count($attributeDqlHandlers)) {
                $dqlHandlers[] = $eavQb->getAnd($attributeDqlHandlers);
            }
        }

        if (0 < count($dqlHandlers)) {
            $eavQb->apply($eavQb->getOr($dqlHandlers));
        }
    }
}
