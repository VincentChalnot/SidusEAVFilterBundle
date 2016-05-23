<?php

namespace Sidus\EAVFilterBundle\Configuration;

use Elastica\Query;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sidus\EAVFilterBundle\Filter\ElasticaFilterInterface;
use Sidus\FilterBundle\DTO\SortConfig;

/**
 * Adds Elastica supports to the EAV model filtering
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class EAVElasticaFilterConfigurationHandler extends EAVFilterConfigurationHandler
{
    /** @var Query */
    protected $esQuery;

    /** @var Query\BoolQuery */
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
     * @return Query
     */
    public function getESQuery()
    {
        if (!$this->esQuery) {
            $this->esQuery = new Query();
            $this->boolQuery = new Query\BoolQuery();
            $this->esQuery->setQuery($this->boolQuery);
            $familyQuery = new Query\Match('family', implode(' ', $this->family->getMatchingCodes()));
            $this->boolQuery->addMust($familyQuery);
        }

        return $this->esQuery;
    }

    /**
     * @param Query $query
     * @return EAVElasticaFilterConfigurationHandler
     */
    public function setESQuery($query)
    {
        $this->esQuery = $query;

        return $this;
    }

    /**
     * @return Query\BoolQuery
     */
    public function getBoolQuery()
    {
        return $this->boolQuery;
    }

    /**
     * @param Query\BoolQuery $boolQuery
     */
    public function setBoolQuery($boolQuery)
    {
        $this->boolQuery = $boolQuery;
    }

    /**
     * @param int $selectedPage
     * @throws \Exception
     */
    protected function handleForm($selectedPage = null)
    {
        if ($this->esQuery) {
            $this->applyESSort($this->getESQuery(), $this->applySortForm());
            $this->applyESFilters($this->getBoolQuery());
            $this->applyESPager($this->getESQuery(), $selectedPage);
        } else {
            parent::handleForm($selectedPage);
        }
    }

    /**
     * @param Query\BoolQuery $query
     * @throws \Exception
     */
    protected function applyESFilters(Query\BoolQuery $query)
    {
        $form = $this->getForm();
        $filterForm = $form->get(self::FILTERS_FORM_NAME);
        foreach ($this->getFilters() as $filter) {
            if (!$filter instanceof ElasticaFilterInterface) {
                throw new \LogicException('Unsupported filter type for elastic search');
            }
            $filter->handleESForm($filterForm->get($filter->getCode()), $query);
        }
    }

    /**
     * @param Query      $query
     * @param SortConfig $sortConfig
     */
    protected function applyESSort(Query $query, SortConfig $sortConfig)
    {
        $column = $sortConfig->getColumn();
        if ($column) {
            $direction = $sortConfig->getDirection() ? 'desc' : 'asc'; // null or false both default to ASC
            $query->addSort([
                $column => [
                    'order' => $direction,
                ],
            ]);
        }
    }

    /**
     * @param Query $query
     * @param int   $selectedPage
     * @throws \Pagerfanta\Exception\LessThan1MaxPerPageException
     * @throws \Pagerfanta\Exception\NotIntegerMaxPerPageException
     * @throws \Pagerfanta\Exception\LessThan1CurrentPageException
     * @throws \Pagerfanta\Exception\NotIntegerCurrentPageException
     * @throws \Pagerfanta\Exception\OutOfRangeCurrentPageException
     */
    protected function applyESPager(Query $query, $selectedPage)
    {
        if ($selectedPage) {
            $this->sortConfig->setPage($selectedPage);
        }
        $this->pager = $this->getFinder()->findPaginated($query);
        $this->pager->setMaxPerPage($this->resultsPerPage);
        $this->pager->setCurrentPage($this->sortConfig->getPage());
    }
}
