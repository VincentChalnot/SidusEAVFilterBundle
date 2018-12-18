<?php
/*
 * This file is part of the Sidus/EAVFilterBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
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
 * Autocomplete filter for data
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class AutocompleteDataFilterType extends ChoiceFilterType
{
    /**
     * {@inheritdoc}
     * @throws \UnexpectedValueException
     */
    public function getFormOptions(QueryHandlerInterface $queryHandler, FilterInterface $filter): array
    {
        if (isset($filter->getFormOptions()['attribute'])) {
            return parent::getFormOptions($queryHandler, $filter);
        }

        if (!$queryHandler instanceof EAVQueryHandlerInterface) {
            throw new BadQueryHandlerException($queryHandler, EAVQueryHandlerInterface::class);
        }

        $eavAttributes = $queryHandler->getEAVAttributes($filter);
        if (\count($eavAttributes) > 1) {
            throw new \UnexpectedValueException(
                "Autocomplete filters does not support multiple attributes ({$filter->getCode()})"
            );
        }

        return array_merge(
            $this->getDefaultFormOptions($queryHandler, $filter),
            [
                'attribute' => reset($eavAttributes),
            ],
            $filter->getFormOptions()
        );
    }
}
