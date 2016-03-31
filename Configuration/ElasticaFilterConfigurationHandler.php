<?php

namespace Sidus\EAVFilterBundle\Configuration;

use Elastica\Query;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sidus\EAVFilterBundle\Filter\ElasticaFilterInterface;
use Symfony\Component\HttpFoundation\Request;

class ElasticaFilterConfigurationHandler extends FilterConfigurationHandler
{
    /** @var Query */
    protected $esQuery;

    /** @var Query\Bool */
    protected $boolQuery;

    /** @var TransformedFinder */
    protected $finder;

    /**
     * @return TransformedFinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * @param TransformedFinder $finder
     */
    public function setFinder(TransformedFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param null $selectedPage
     * @throws \Exception
     */
    protected function handleForm($selectedPage = null)
    {
        if ($this->esQuery) {
            $this->applyESSort($this->getESQuery());
            $this->applyESFilters($this->getBoolQuery());
            $this->applyESPager($this->getESQuery(), $selectedPage);
        } else {
            parent::handleForm($selectedPage);
        }
    }

    /**
     * @return Query
     */
    public function getESQuery()
    {
        if (!$this->esQuery) {
            $this->esQuery = new Query();
            $this->boolQuery = new Query\Bool();
            $this->esQuery->setQuery($this->boolQuery);
            $familyQuery = new Query\Match('family', implode(' ', $this->family->getMatchingCodes()));
            $this->boolQuery->addMust($familyQuery);
        }
        return $this->esQuery;
    }

    /**
     * @param $query
     * @return ElasticaFilterConfigurationHandler
     */
    public function setESQuery($query)
    {
        $this->esQuery = $query;
        return $this;
    }

    /**
     * @return Query\Bool
     */
    public function getBoolQuery()
    {
        return $this->boolQuery;
    }

    /**
     * @param Query\Bool $boolQuery
     */
    public function setBoolQuery($boolQuery)
    {
        $this->boolQuery = $boolQuery;
    }

    /**
     * @param Query\Bool $query
     * @throws \Exception
     */
    protected function applyESFilters(Query\Bool $query)
    {
        $form = $this->getForm();
        $filterForm = $form->get(self::FILTERS_FORM_NAME);
        foreach ($this->getFilters() as $filter) {
            if (!$filter instanceof ElasticaFilterInterface) {
                throw new \Exception('Unsupported filter type for elastic search'); // @todo refactor with better exception
            }
            $filter->handleESForm($filterForm->get($filter->getCode()), $query);
        }
    }

    /**
     * @param Query $query
     * @throws \Exception
     */
    protected function applyESSort(Query $query)
    {
        $sortConfig = $this->applySortForm();

        $column = $sortConfig->getColumn();
        if ($column) {
            $direction = $sortConfig->getDirection() ? 'desc' : 'asc'; // null or false both default to ASC
            $query->addSort([
                $column => [
                    'order' => $direction,
                ]
            ]);
        }
    }

    /**
     * @param Query $query
     * @param Request $request
     * @throws \Exception
     */
    protected function applyESPager(Query $query, Request $request)
    {
        $this->pager = $this->getFinder()->findPaginated($query);
        $this->pager->setMaxPerPage($this->resultsPerPage);
        $this->pager->setCurrentPage($request->get('page', 1));
    }
}