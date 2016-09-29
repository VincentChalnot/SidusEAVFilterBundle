<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Elastica\Query;
use Sidus\EAVFilterBundle\Filter\ElasticaFilterInterface;
use Sidus\FilterBundle\Filter\Type\ChoiceFilterType as BaseChoiceFilterType;
use Symfony\Component\Form\FormInterface;

class ChoiceFilterType extends BaseChoiceFilterType implements ElasticaFilterTypeInterface
{
    /**
     * @param ElasticaFilterInterface $filter
     * @param FormInterface           $form
     * @param Query\BoolQuery         $query
     *
     * @throws \Exception
     */
    public function handleESForm(ElasticaFilterInterface $filter, FormInterface $form, Query\BoolQuery $query)
    {
        $data = $form->getData();
        if (!$data) {
            return;
        }
        if (is_object($data)) {
            if (!method_exists($data, 'getId')) {
                throw new \Exception('It is not yet possible to search in data not implementing a getId function');
            }
            $data = $data->getId(); // @todo fix me !
        }
        foreach ($filter->getAttributes() as $column) {
            $subQuery = new Query\Match($column, $data);
            $query->addMust($subQuery);
        }
    }
}
