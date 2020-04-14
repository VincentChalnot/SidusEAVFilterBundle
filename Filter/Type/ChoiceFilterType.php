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
use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
use Sidus\EAVModelBundle\Doctrine\DQLHandlerInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilderInterface;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;

/**
 * Replaces the standard ChoiceFilterType
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class ChoiceFilterType extends AbstractSimpleFilterType
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

        if (isset($filter->getFormOptions()['choices'])) {
            return parent::getFormOptions($queryHandler, $filter);
        }

        $choices = [];
        $attributes = $queryHandler->getEAVAttributes($filter);
        foreach ($attributes as $attribute) {
            if (array_key_exists('choices', $attribute->getFormOptions())) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $choices = array_merge($choices, $attribute->getFormOptions()['choices']);
            }
        }
        $formOptions = [];
        if (\count($choices) > 0) {
            $formOptions['choices'] = $choices;
        }
        if (1 === \count($attributes)) {
            /** @noinspection PhpUndefinedVariableInspection */
            $attributeFormOptions = $attribute->getFormOptions();
            if (array_key_exists('choice_translation_domain', $attributeFormOptions)) {
                $formOptions['choice_translation_domain'] = $attributeFormOptions['choice_translation_domain'];
            }
        }

        return array_merge(
            $this->getDefaultFormOptions($queryHandler, $filter),
            $formOptions,
            $filter->getFormOptions()
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function applyAttributeQueryBuilder(
        EAVQueryBuilderInterface $eavQb,
        AttributeQueryBuilderInterface $attributeQb,
        $data
    ): DQLHandlerInterface {
        if (\is_array($data)) {
            return $attributeQb->in($data);
        }

        return $attributeQb->equals($data);
    }

    /**
     * @param QueryHandlerInterface $queryHandler
     * @param FilterInterface       $filter
     *
     * @return array
     */
    protected function getFallbackFormOptions(QueryHandlerInterface $queryHandler, FilterInterface $filter): array
    {
        if (!$this->fallbackFilterType) {
            $m = "Filter '{$filter->getCode()}' with type '{$this->getName()}' has no EAV attribute and no ";
            $m .= 'fallback filter type.';
            throw new \LogicException($m);
        }

        return $this->fallbackFilterType->getFormOptions($queryHandler, $filter);
    }
}
