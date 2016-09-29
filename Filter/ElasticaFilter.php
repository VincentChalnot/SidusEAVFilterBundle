<?php

namespace Sidus\EAVFilterBundle\Filter;

use Elastica\Query;
use Sidus\EAVFilterBundle\Filter\Type\ElasticaFilterTypeInterface;
use Symfony\Component\Form\FormInterface;

class ElasticaFilter extends Filter implements ElasticaFilterInterface
{
    /**
     * @param FormInterface   $form
     * @param Query\BoolQuery $query
     *
     * @throws \Exception
     */
    public function handleESForm(FormInterface $form, Query\BoolQuery $query)
    {
        $filterType = $this->getFilterType();
        if (!$filterType instanceof ElasticaFilterTypeInterface) {
            throw new \Exception('Unsupported filter type for elastic search'); // @todo refactor with better exception
        }
        $filterType->handleESForm($this, $form, $query);
    }
}
