<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Sidus\EAVFilterBundle\Query\Handler\EAVQueryHandlerInterface;
use Sidus\EAVModelBundle\Doctrine\EAVQueryBuilder;
use Sidus\FilterBundle\Exception\BadQueryHandlerException;
use Sidus\FilterBundle\Filter\FilterInterface;
use Sidus\FilterBundle\Filter\Type\FilterTypeInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Replaces the standard ChoiceFilterType
 */
class ChoiceFilterType extends AbstractEAVFilterType
{
    /** @var Registry */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function setDoctrine(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

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
        if (!$queryHandler->isEAVFilter($filter)) {
            $this->fallbackFilterType->handleForm($queryHandler, $filter, $form);

            return;
        }

        $data = $form->getData();
        if (null === $data || (\is_array($data) && 0 === \count($data))) {
            return;
        }

        $eavQb = new EAVQueryBuilder($queryHandler->getQueryBuilder(), $queryHandler->getAlias());
        $eavQb->setContext($queryHandler->getContext());
        $dqlHandlers = [];
        foreach ($filter->getAttributes() as $attributePath) {
            $attributeQb = $queryHandler->getEAVAttributeQueryBuilder($eavQb, $attributePath);
            if (\is_array($data)) {
                $dqlHandlers[] = $attributeQb->in($data);
            } else {
                $dqlHandlers[] = $attributeQb->equals($data);
            }
        }

        if (0 < \count($dqlHandlers)) {
            $eavQb->apply($eavQb->getOr($dqlHandlers));
        }
    }

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
            $formOptions['choice_translation_domain'] = $attributeFormOptions['choice_translation_domain'] ?? null;
        }

        return array_merge(
            $this->getDefaultFormOptions($queryHandler, $filter),
            $formOptions,
            $filter->getFormOptions()
        );
    }
}
