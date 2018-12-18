<?php
/*
 * This file is part of the Sidus/EAVFilterBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /** @var int */
    protected $depth;

    /**
     * Automatically creates an adapter with the Sidus/FilterBundle's DoctrineORMAdapter
     *
     * @param DataLoaderInterface $dataLoader
     * @param QueryBuilder        $qb
     * @param int                 $depth
     *
     * @return EAVAdapter
     */
    public static function create(DataLoaderInterface $dataLoader, QueryBuilder $qb, $depth = 2)
    {
        return new self(
            $dataLoader,
            new DoctrineORMAdapter($qb, false, $depth)
        );
    }

    /**
     * @param DataLoaderInterface $dataLoader
     * @param AdapterInterface    $baseAdapter
     * @param int                 $depth
     */
    public function __construct(
        DataLoaderInterface $dataLoader,
        AdapterInterface $baseAdapter,
        $depth = 2
    ) {
        $this->dataLoader = $dataLoader;
        $this->baseAdapter = $baseAdapter;
        $this->depth = (int) $depth;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        $iterator = $this->baseAdapter->getSlice($offset, $length);
        $this->dataLoader->load($iterator, $this->depth);

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
