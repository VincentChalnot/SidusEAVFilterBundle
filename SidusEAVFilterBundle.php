<?php

namespace Sidus\EAVFilterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SidusEAVFilterBundle
 *
 * @package Sidus\EAVFilterBundle
 */
class SidusEAVFilterBundle extends Bundle
{
    /**
     * @return string
     */
    public function getParent()
    {
        return 'SidusFilterBundle';
    }
}
