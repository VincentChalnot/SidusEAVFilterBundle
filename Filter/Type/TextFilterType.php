<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVFilterBundle\Configuration\EAVQueryHandlerInterface;
use Sidus\EAVFilterBundle\Filter\EAVFilter;
use Sidus\EAVFilterBundle\Filter\EAVFilterHelper;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\Doctrine\DoctrineFilterInterface;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Filter\Type\Doctrine\TextFilterType as BaseTextFilterType;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Replaces the standard TextFilterType
 */
class TextFilterType extends AbstractEAVFilterType
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
        if (null === $data) {
            return;
        }

        $eavQb = new EAVQueryBuilder($queryHandler->getQueryBuilder(), $queryHandler->getAlias());
        $dqlHandlers = [];
        foreach ($filter->getAttributes() as $attributePath) {
            $attributeQb = $queryHandler->getEAVAttributeQueryBuilder($eavQb, $attributePath);
            $dqlHandlers[] = $attributeQb->like('%'.trim($data, '%').'%');
        }

        if (0 < count($dqlHandlers)) {
            $eavQb->apply($eavQb->getOr($dqlHandlers));
        }
    }
}
