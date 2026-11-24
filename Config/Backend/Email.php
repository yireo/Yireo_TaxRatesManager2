<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

class Email extends Value
{
    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), " \f\n\r\t\v\x00");
        $this->setValue($value);

        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidatorException(__('The email address "%1" is invalid', $value));
        }

        return parent::beforeSave();
    }
}
