<?php
namespace Paymill\Paymill\Helper;

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

/**
 * The Payment Helper contains methods dealing with payment relevant
 * information.
 * Examples for this might be f.Ex customer data, formating of basket amounts or
 * similar.
 */
class PaymentHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Error code mapping
     * 
     * @var array
     */
    protected $_responseCodes = array(
            '10001' => 'General undefined response.',
            '10002' => 'Still waiting on something.',
            '20000' => 'General success response.',
            '40000' => 'General problem with data.',
            '40001' => 'General problem with payment data.',
            '40100' => 'Problem with credit card data.',
            '40101' => 'Problem with cvv.',
            '40102' => 'Card expired or not yet valid.',
            '40103' => 'Limit exceeded.',
            '40104' => 'Card invalid.',
            '40105' => 'Expiry date not valid.',
            '40106' => 'Credit card brand required.',
            '40200' => 'Problem with bank account data.',
            '40201' => 'Bank account data combination mismatch.',
            '40202' => 'User authentication failed.',
            '40300' => 'Problem with 3d secure data.',
            '40301' => 'Currency / amount mismatch',
            '40400' => 'Problem with input data.',
            '40401' => 'Amount too low or zero.',
            '40402' => 'Usage field too long.',
            '40403' => 'Currency not allowed.',
            '50000' => 'General problem with backend.',
            '50001' => 'Country blacklisted.',
            '50100' => 'Technical error with credit card.',
            '50101' => 'Error limit exceeded.',
            '50102' => 'Card declined by authorization system.',
            '50103' => 'Manipulation or stolen card.',
            '50104' => 'Card restricted.',
            '50105' => 'Invalid card configuration data.',
            '50200' => 'Technical error with bank account.',
            '50201' => 'Card blacklisted.',
            '50300' => 'Technical error with 3D secure.',
            '50400' => 'Decline because of risk issues.',
            '50500' => 'General timeout.',
            '50501' => 'Timeout on side of the acquirer.',
            '50502' => 'Risk management transaction timeout.',
            '50600' => 'Duplicate transaction.'
    );

    /**
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     *
     * @var \Paymill\Paymill\Helper\OptionHelper
     */
    protected $paymillOptionHelperHelper;

    /**
     *
     * @var \Paymill\Paymill\Helper\CustomerHelper
     */
    protected $paymillCustomerHelperHelper;

    /**
     *
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     *
     * @var \Paymill\Paymill\Helper\Data
     */
    protected $paymillHelper;

    /**
     *
     * @var \Paymill\Paymill\Helper\LoggingHelper
     */
    protected $paymillLoggingHelperHelper;

    public function __construct (\Magento\Framework\App\Helper\Context $context, 
            \Magento\Checkout\Model\Session $checkoutSession, 
            \Paymill\Paymill\Helper\OptionHelper $paymillOptionHelperHelper, 
            \Paymill\Paymill\Helper\CustomerHelper $paymillCustomerHelperHelper, 
            \Magento\Framework\DB\TransactionFactory $transactionFactory, 
            \Paymill\Paymill\Helper\Data $paymillHelper, 
            \Paymill\Paymill\Helper\LoggingHelper $paymillLoggingHelperHelper)
    {
        $this->checkoutSession = $checkoutSession;
        $this->paymillOptionHelperHelper = $paymillOptionHelperHelper;
        $this->paymillCustomerHelperHelper = $paymillCustomerHelperHelper;
        $this->transactionFactory = $transactionFactory;
        $this->paymillHelper = $paymillHelper;
        $this->paymillLoggingHelperHelper = $paymillLoggingHelperHelper;
        parent::__construct($context);
    }

    /**
     * Return message for the given error code
     *
     * @param string $code            
     * @return string
     */
    public function getErrorMessage ($code)
    {
        $message = 'General undefined response.';
        if (array_key_exists($code, $this->_responseCodes)) {
            $message = $this->__($this->_responseCodes[$code]);
        }
        
        return $message;
    }

    /**
     * Returns the order amount in the smallest possible unit (f.Ex.
     * cent for the EUR currency)
     * <p align = "center" color = "red">At the moment, only currencies with a
     * 1:100 conversion are supported. Special cases need to be added if
     * necessary</p>
     * 
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $object            
     * @return int Amount in the smallest possible unit
     */
    public function getAmount ($object = null)
    {
        if (is_null($object)) {
            $object = $this->checkoutSession->getQuote();
        }
        
        $amount = $object->getBaseGrandTotal();
        
        if (! $this->paymillOptionHelperHelper->isBaseCurrency()) {
            $amount = $object->getGrandTotal();
        }
        
        return round($amount * 100);
    }

    /**
     * Returns the currency compliant to ISO 4217 (3 char code)
     * 
     * @return string 3 Character long currency code
     *        
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order|\Magento\Sales\Model\Order\Invoice|\Magento\Sales\Model\Order\Creditmemo $object            
     * @return string
     */
    public function getCurrency ($object)
    {
        $currency = $object->getBaseCurrencyCode();
        if (! $this->paymillOptionHelperHelper->isBaseCurrency()) {
            if ($object instanceof \Magento\Quote\Model\Quote) {
                $currency = $object->getQuoteCurrencyCode();
            } else {
                $currency = $object->getOrderCurrencyCode();
            }
        }
        
        return $currency;
    }

    /**
     * Returns the description you want to display in the Paymill Backend.
     * The current format is [OrderId] [Email adress of the customer]
     * 
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $object            
     * @return string
     */
    public function getDescription ($object)
    {
        return $this->getOrderId($object) . ", " .
                 $this->paymillCustomerHelperHelper->getCustomerEmail($object);
    }

    /**
     * Returns the short tag of the Payment
     * 
     * @param String $code            
     * @return string
     */
    public function getPaymentType ($code)
    {
        $type = null;
        
        // Creditcard
        if ($code === "paymill_creditcard") {
            $type = "cc";
        }
        // Directdebit
        if ($code === "paymill_directdebit") {
            $type = "elv";
        }
        
        return $type;
    }

    /**
     * Returns the reserved order id
     * 
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $object            
     * @return String OrderId
     */
    public function getOrderId ($object)
    {
        $orderId = null;
        
        if ($object instanceof \Magento\Sales\Model\Order) {
            $orderId = $object->getIncrementId();
        }
        
        if ($object instanceof \Magento\Quote\Model\Quote) {
            $orderId = $object->getReservedOrderId();
        }
        
        return $orderId;
    }

    public function invoice (\Magento\Sales\Model\Order $order, $transactionId, 
            $mail)
    {
        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();
            
            $invoice->setRequestedCaptureCase(
                    \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            
            $this->transactionFactory->create()
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            
            $invoice->setTransactionId($transactionId);
            
            $invoice->pay();
            
            $invoice->save();
        } else {
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoice->pay()->save();
            }
        }
    }

    /**
     * Returns an instance of the paymentProcessor class.
     * 
     * @param String $paymentCode
     *            name of the payment
     * @param String $token
     *            Token generated by the Javascript
     * @return Services_Paymill_PaymentProcessor
     */
    public function createPaymentProcessor ($paymentCode, $token)
    {
        $quote = $this->checkoutSession->getQuote();
        
        $params = array();
        $params['token'] = $token;
        $params['amount'] = (int) $this->getAmount();
        $params['currency'] = $this->getCurrency($quote);
        $params['payment'] = $this->getPaymentType($paymentCode); // The chosen
                                                                  // payment (cc
                                                                  // | elv)
        $params['name'] = $this->paymillCustomerHelperHelper->getCustomerName(
                $quote);
        $params['email'] = $this->paymillCustomerHelperHelper->getCustomerEmail(
                $quote);
        $params['description'] = substr($this->getDescription($quote), 0, 128);
        
        $paymentProcessor = new \Paymill\Paymill\Services\PaymentProcessor(
                $this->paymillOptionHelperHelper->getPrivateKey(), 
                $this->paymillHelper->getApiUrl(), null, $params, 
                $this->paymillLoggingHelperHelper);
        
        $paymentProcessor->setSource($this->paymillHelper->getSourceString());
        
        return $paymentProcessor;
    }
    
}
