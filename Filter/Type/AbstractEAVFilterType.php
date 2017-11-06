<?php

namespace Sidus\EAVFilterBundle\Filter\Type;

use Sidus\FilterBundle\Filter\Type\AbstractFilterType;

/**
 *
 */
abstract class AbstractEAVFilterType extends AbstractFilterType
{
    /**
     * @return string
     */
    public function getProvider(): string
    {
        return 'sidus.eav';
    }
}
