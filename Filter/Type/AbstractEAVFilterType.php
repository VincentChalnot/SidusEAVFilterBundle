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
use Sidus\EAVModelBundle\Exception\MissingAttributeException;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Filter\Type\AbstractFilterType;
use Sidus\FilterBundle\Filter\Type\FilterTypeInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;

/**
 * Handles common logic for EAV filters
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
abstract class AbstractEAVFilterType extends AbstractFilterType
{
    /** @var FilterTypeInterface */
    protected $fallbackFilterType;

    /**
     * @param FilterTypeInterface $fallbackFilterType
     */
    public function setFallbackFilterType(FilterTypeInterface $fallbackFilterType): void
    {
        $this->fallbackFilterType = $fallbackFilterType;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return 'sidus.eav';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions(QueryHandlerInterface $queryHandler, FilterInterface $filter): array
    {
        if (!$queryHandler instanceof EAVQueryHandlerInterface) {
            throw new BadQueryHandlerException($queryHandler, EAVQueryHandlerInterface::class);
        }

        return array_merge(
            $this->getDefaultFormOptions($queryHandler, $filter),
            $filter->getFormOptions()
        );
    }

    /**
     * @param EAVQueryHandlerInterface $queryHandler
     * @param FilterInterface          $filter
     *
     * @return array
     */
    protected function getDefaultFormOptions(EAVQueryHandlerInterface $queryHandler, FilterInterface $filter): array
    {
        $formOptions = $this->formOptions;
        try {
            $attributes = $queryHandler->getEAVAttributes($filter);
        } catch (MissingAttributeException $e) {
            return $formOptions;
        }
        if (1 === \count($attributes)) {
            $attribute = reset($attributes);
            $attributeFormOptions = $attribute->getFormOptions();
            $formOptions['label'] = (string) $attribute;
            $formOptions['translate_label'] = false;
            if (array_key_exists('translation_domain', $attributeFormOptions)) {
                $formOptions['translation_domain'] = $attributeFormOptions['translation_domain'];
            }
        }

        return $formOptions;
    }
}
