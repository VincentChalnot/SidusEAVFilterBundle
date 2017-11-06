<?php

namespace Sidus\EAVDataGridBundle\Model;

use Sidus\DataGridBundle\Model\Column as BaseColumn;
use Sidus\EAVModelBundle\Translator\TranslatableTrait;

/**
 * @method DataGrid getDataGrid
 */
class ColumnTranslator extends BaseColumn
{
    use TranslatableTrait;

    /**
     * @return string
     *
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     */
    public function getLabel()
    {
        if ($this->label) {
            return $this->label;
        }
        $fallBack = $this->getCode();
        $tIds = [];
        $family = $this->getDataGrid()->getFamily();
        if ($family) {
            $tIds[] = "eav.family.{$family->getCode()}.datagrid.{$this->getCode()}.label";
            if ($this->getCode() === 'label') {
                $fallBack = (string) $family->getAttributeAsLabel();
            } elseif ($family->hasAttribute($this->getCode())) {
                $fallBack = (string) $family->getAttribute($this->getCode());
            }
        }
        $tIds[] = "eav.datagrid.{$this->getCode()}.label";
        $parameters = [
            'fallback' => $fallBack,
        ];

        return $this->tryTranslate($tIds, $parameters, $fallBack);
    }
}
