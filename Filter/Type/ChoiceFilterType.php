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
use Sidus\EAVModelBundle\Doctrine\AttributeQueryBuilderInterface;
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
            return $this->fallbackFilterType->getFormOptions($queryHandler, $filter);
        }

        if (isset($filter->getFormOptions()['choices'])) {
            return parent::getFormOptions($queryHandler, $filter);
        }

        $choices = [];
        $attributes = $queryHandler->getEAVAttributes($filter);
        foreach ($attributes as $attribute) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $choices = array_merge($choices, $attribute->getFormOptions()['choices'] ?? []);
        }

        $formOptions = [
            'choices' => $choices,
        ];
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
     * @param AttributeQueryBuilderInterface $attributeQb
     * @param mixed                          $data
     *
     * @return AttributeQueryBuilderInterface
     */
    protected function applyAttributeQueryBuilder(
        AttributeQueryBuilderInterface $attributeQb,
        $data
    ): AttributeQueryBuilderInterface {
        if (\is_array($data)) {
            return $attributeQb->in($data);
        }

        return $attributeQb->equals($data);
    }
}
