<?php
namespace Paymill\Paymill\Model\Source;

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category Paymill
 * @package Paymill_Paymill
 * @copyright Copyright (c) 2013 PAYMILL GmbH (https://paymill.com/en-gb/)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 *          (OSL 3.0)
 */
class Hooks
{

    /**
     * Define which Creditcard Logos are shown for payment
     *
     * @return array
     */
    public function toOptionArray ()
    {
        return array(
                array(
                        'label' => __('refund.succeeded'),
                        'value' => 'refund.succeeded'
                ),
                array(
                        'label' => __('transaction.succeeded'),
                        'value' => 'transaction.succeeded'
                ),
                array(
                        'label' => __('chargeback.executed'),
                        'value' => 'chargeback.executed'
                )
        );
    }
    
}
