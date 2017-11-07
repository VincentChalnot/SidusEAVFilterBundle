<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Sidus\EAVFilterBundle\Query\Handler\EAVQueryHandlerInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Replaces the standard ChoiceFilterType
 */
class ChoiceFilterType extends AbstractEAVFilterType
{
    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function handleForm(QueryHandlerInterface $queryHandler, FilterInterface $filter, FormInterface $form)
    {
        if (!$queryHandler instanceof EAVQueryHandlerInterface) {
            throw new BadQueryHandlerException($queryHandler, EAVQueryHandlerInterface::class);
        }
        if (!$form->isSubmitted()) {
            return;
        }
        $data = $form->getData();
        if (null === $data || (is_array($data) && 0 === count($data))) {
            return;
        }

        $eavQb = new EAVQueryBuilder($queryHandler->getQueryBuilder(), $queryHandler->getAlias());
        $dqlHandlers = [];
        foreach ($filter->getAttributes() as $attributePath) {
            $attributeQb = $queryHandler->getEAVAttributeQueryBuilder($eavQb, $attributePath);
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

//    /**
//     * {@inheritdoc}
//     */
//    public function getFormOptions(QueryHandlerInterface $queryHandler, FilterInterface $filter): array
//    {
////        if (!$queryHandler instanceof DoctrineQueryHandlerInterface) {
////            throw new BadQueryHandlerException($queryHandler, DoctrineQueryHandlerInterface::class);
////        }
////        if (isset($this->formOptions['choices'])) {
////            return $this->formOptions;
////        }
////        $choices = [];
////        $alias = $queryHandler->getAlias();
////        foreach ($this->getFullAttributeReferences($filter, $alias) as $column) {
////            $qb = clone $queryHandler->getQueryBuilder();
////            $qb->select("{$column} AS __value")
////                ->groupBy($column);
////            foreach ($qb->getQuery()->getArrayResult() as $result) {
////                $value = $result['__value'];
////                $choices[$value] = $value;
////            }
////        }
////
////        return array_merge($this->formOptions, ['choices' => $choices]);
//    }
}
