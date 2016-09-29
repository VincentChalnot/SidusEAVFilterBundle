<?php

namespace Sidus\EAVFilterBundle\Filter;

use Elastica\Query;
use Sidus\FilterBundle\Filter\FilterInterface;
use Symfony\Component\Form\FormInterface;

interface ElasticaFilterInterface extends FilterInterface
{
    /**
     * @param FormInterface   $form
     * @param Query\BoolQuery $query
     */
    public function handleESForm(FormInterface $form, Query\BoolQuery $query);
}
