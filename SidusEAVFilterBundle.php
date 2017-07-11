<?php

namespace Sidus\EAVFilterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

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
