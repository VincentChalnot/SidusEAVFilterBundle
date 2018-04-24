<?php

namespace Sidus\EAVFilterBundle\Pager\Adapter;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;
use Sidus\EAVModelBundle\Doctrine\DataLoaderInterface;
use Sidus\FilterBundle\Pagination\DoctrineORMAdapter;

/**
 * Optimize the loading of multiple data at once
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class EAVAdapter implements AdapterInterface
{
    /** @var AdapterInterface */
    protected $baseAdapter;

    /** @var DataLoaderInterface */
    protected $dataLoader;

    /**
     * Automatically creates an adapter with the Sidus/FilterBundle's DoctrineORMAdapter
     *
     * @param DataLoaderInterface $dataLoader
     * @param QueryBuilder        $qb
     *
     * @return EAVAdapter
     */
    public static function create(DataLoaderInterface $dataLoader, QueryBuilder $qb)
    {
        return new self(
            $dataLoader,
            new DoctrineORMAdapter($qb, false)
        );
    }

    /**
     * @param DataLoaderInterface $dataLoader
     * @param AdapterInterface    $baseAdapter
     */
    public function __construct(
        DataLoaderInterface $dataLoader,
        AdapterInterface $baseAdapter
    ) {
        $this->dataLoader = $dataLoader;
        $this->baseAdapter = $baseAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        $iterator = $this->baseAdapter->getSlice($offset, $length);
        $this->dataLoader->load($iterator);

        return $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return $this->baseAdapter->getNbResults();
    }
}
