<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Payment;

use Magento\Payment\Model\Method\Adapter;

class Resursbank extends Adapter
{
    /**
     * Default title.
     */
    public const TITLE = 'Resurs Bank';

    /**
     * Payment method code prefix.
     *
     * @var string
     */
    public const CODE_PREFIX = 'resursbank_';

    /**
     * Default payment method code.
     *
     * @var string
     */
    public const CODE = self::CODE_PREFIX . 'default';

    /**
     * @var string
     */
    protected $title = self::TITLE;

    /**
     * When we create an instance of this payment method we will set the correct
     * title immediately (see Plugin/Payment/Helper/Data.php :: getMethod()).
     * This lets us avoid using a value handler, which isn't always utilised
     * anyways (see the docblock for getConfigData() below). Utilising a value
     * handler may also mean overhead database transactions.
     *
     * @param string $title
     */
    public function setTitle(
        string $title
    ): void {
        $this->title = $title;
    }

    /**
     * Overrides the vanilla method to extract title from this payment method.
     * Instead of utilising a value handler we return the title applied through
     * setTitle above when this instance is created.
     *
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return (string) $this->title;
    }

    /**
     * Some implementations will utilise getConfigData directly, thus avoiding
     * the specified title value handlers and our overriding behaviour to
     * correct the title of this payment method instance (see setTitle and
     * getTitle above).
     *
     * This payment  method was implemented to ensure the correct title is
     * displayed on the order view:
     * vendor/magento/module-payment/view/adminhtml/templates/info/default.phtml
     *
     * @inheritdoc
     */
    public function getConfigData($field, $storeId = null)
    {
        return $field === 'title' ?
            $this->getTitle() :
            parent::getConfigData($field, $storeId);
    }
}
