<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Sidus\EAVFilterBundle\Query\Handler\EAVQueryHandlerInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;

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
    public function handleData(QueryHandlerInterface $queryHandler, FilterInterface $filter, $data)
    {
        if (!$queryHandler instanceof EAVQueryHandlerInterface) {
            throw new BadQueryHandlerException($queryHandler, EAVQueryHandlerInterface::class);
        }
        if (!$queryHandler->isEAVFilter($filter)) {
            $this->fallbackFilterType->handleData($queryHandler, $filter, $data);

            return;
        }
        if (null === $data) {
            return;
        }

        $eavQb = new EAVQueryBuilder($queryHandler->getQueryBuilder(), $queryHandler->getAlias());
        $eavQb->setContext($queryHandler->getContext());
        $dqlHandlers = [];
        foreach ($filter->getAttributes() as $attributePath) {
            $attributeQb = $queryHandler->getEAVAttributeQueryBuilder($eavQb, $attributePath);
            $dqlHandlers[] = $attributeQb->like('%'.trim($data, '%').'%');
        }

        if (0 < \count($dqlHandlers)) {
            $eavQb->apply($eavQb->getOr($dqlHandlers));
        }
    }
}
