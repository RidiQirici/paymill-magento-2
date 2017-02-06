<?php
namespace Paymill\Paymill\Model\Source\Creditcard;

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
 *         
 */
class Pci implements \Magento\Framework\Option\ArrayInterface
{

    /**
     *
     * @return array
     */
    public function toOptionArray ()
    {
        $pciMode = array(
                array(
                        'label' => __('PayFrame (min. PCI SAQ A)'),
                        'value' => 'SAQ A'
                ),
                array(
                        'label' => __('direct integration (min. PCI SAQ A-EP)'),
                        'value' => 'SAQ A-EP'
                )
        );
        
        return $pciMode;
    }
    
}
