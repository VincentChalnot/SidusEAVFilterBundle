<?php
/*
 * This file is part of the Sidus/EAVFilterBundle package.
 *
 * Copyright (c) 2015-2020 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\EAVFilterBundle\Filter\Type;

use Sidus\EAVFilterBundle\Query\Handler\EAVQueryHandlerInterface;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;

/**
 * Replaces the standard EntityFilterType
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class EntityFilterType extends ChoiceFilterType
{
    /**
     * {@inheritdoc}
     */
    public function getFormOptions(QueryHandlerInterface $queryHandler, FilterInterface $filter): array
    {
        if (!$queryHandler instanceof EAVQueryHandlerInterface) {
            throw new BadQueryHandlerException($queryHandler, EAVQueryHandlerInterface::class);
        }
        if (!$queryHandler->isEAVFilter($filter)) {
            return $this->getFallbackFormOptions($queryHandler, $filter);
        }

        $allowedFamilies = [];
        $attributes = $queryHandler->getEAVAttributes($filter);
        foreach ($attributes as $attribute) {
            if (!$attribute->getType()->isRelation() || !$attribute->getType()->isEmbedded()) {
                throw new \LogicException(
                    "{$attribute->getCode()} is not a relation, please use the 'choice' filter type"
                );
            }
            foreach ($attribute->getOption('allowed_families', []) as $allowedFamily) {
                $allowedFamilies[] = $allowedFamily;
            }
        }

        return array_merge(
            $this->getDefaultFormOptions($queryHandler, $filter),
            ['allowed_families' => array_unique($allowedFamilies)],
            $filter->getFormOptions()
        );
    }
}
