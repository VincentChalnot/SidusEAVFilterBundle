<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVFilterBundle\Filter\EAVFilter;
use Sidus\EAVFilterBundle\Filter\EAVFilterHelper;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Filter\Doctrine\DoctrineFilterInterface;
use Sidus\FilterBundle\Filter\Type\Doctrine\DateRangeFilterType as BaseDateRangeFilterType;
use Sidus\FilterBundle\Form\Type\DateRangeType;
use Symfony\Component\Form\FormInterface;

/**
 * Replaces the standard DateRangeFilterType
 */
class DateRangeFilterType extends BaseDateRangeFilterType
{
    /** @var EAVFilterHelper */
    protected $eavFilterHelper;

    /**
     * @param EAVFilterHelper $eavFilterHelper
     */
    public function setEAVFilterHelper($eavFilterHelper)
    {
        $this->eavFilterHelper = $eavFilterHelper;
    }

    /**
     * We don't handle default values for dates because it's complicated and it doesn't make much sense
     *
     * @param DoctrineFilterInterface $filter
     * @param FormInterface           $form
     * @param QueryBuilder            $qb
     * @param string                  $alias
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     */
    public function handleForm(DoctrineFilterInterface $filter, FormInterface $form, QueryBuilder $qb, $alias)
    {
        parent::handleForm($filter, $form, $qb, $alias);

        $data = $form->getData();
        if (null === $data || !$filter instanceof EAVFilter || !$form->isSubmitted()) {
            return;
        }

        $family = $filter->getFamily();
        if (!$family) {
            return;
        }
        $eavQb = new EAVQueryBuilder($qb, $alias);
        $dqlHandlers = [];
        foreach ($filter->getAttributes() as $attributePath) {
            $attributeDqlHandlers = [];
            $attributeQb = null;
            if (!empty($data[DateRangeType::START_NAME])) {
                $attributeQb = $this->eavFilterHelper->getEAVAttributeQueryBuilder($eavQb, $family, $attributePath);
                $attributeDqlHandlers[] = $attributeQb->gte($data[DateRangeType::START_NAME]);
            }
            if (!empty($data[DateRangeType::END_NAME])) {
                if ($attributeQb) {
                    $attributeQb = clone $attributeQb;
                } else {
                    $attributeQb = $this->eavFilterHelper->getEAVAttributeQueryBuilder($eavQb, $family, $attributePath);
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
