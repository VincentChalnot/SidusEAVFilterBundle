<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVFilterBundle\Filter\EAVFilter;
use Sidus\EAVFilterBundle\Filter\EAVFilterHelper;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Filter\Type\ChoiceFilterType as BaseChoiceFilterType;
use Symfony\Component\Form\FormInterface;

/**
 * Replaces the standard ChoiceFilterType
 */
class ChoiceFilterType extends BaseChoiceFilterType
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
        if (is_array($data) && 0 === count($data)) {
            return;
        }

        $family = $filter->getFamily();
        $eavQb = new EAVQueryBuilder($qb, $alias);
        $dqlHandlers = [];
        foreach ($filter->getAttributes() as $attributePath) {
            $attributeQb = $this->eavFilterHelper->getEAVAttributeQueryBuilder($eavQb, $family, $attributePath);
            if (is_array($data)) {
                $dqlHandlers[] = $attributeQb->in($data);
            } else {
                $dqlHandlers[] = $attributeQb->equals($data);
            }
        }

        if (0 < count($dqlHandlers)) {
            $eavQb->apply($eavQb->getOr($dqlHandlers));
        }
    }
}
