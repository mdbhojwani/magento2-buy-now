<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_BuyNow
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Mdbhojwani\BuyNow\Controller\Cart;

use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Mdbhojwani\BuyNow\Model\Session as CheckoutSession;
use Mdbhojwani\BuyNow\Helper\Data as buyNowHelper;
use Magento\Framework\Escaper;

/**
 * Class Add
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var RequestQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var BuyNowHelper
     */
    protected $buyNowHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CheckoutSession $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param RequestQuantityProcessor|null $quantityProcessor
     * @param QuoteFactory $quoteFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param Escaper $escaper
     * @param BuyNowHelper $buyNowHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        ?RequestQuantityProcessor $quantityProcessor = null,
        QuoteFactory $quoteFactory,
        CartRepositoryInterface $quoteRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        Escaper $escaper,
        BuyNowHelper $buyNowHelper
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository,
            $quantityProcessor
        );
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->escaper = $escaper;
        $this->buyNowHelper = $buyNowHelper;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->buyNowHelper->isEnabled()) {
            $this->messageManager->addErrorMessage(
                __('Please check and unpdate module configuration.')
            );
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $params = $this->getRequest()->getParams();

        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            $cartProducts = $this->buyNowHelper->getCartActions();
            if ($cartProducts == 2) {
                $prevQuoteId = (int)$this->cart->getQuote()->getId();
                $this->createNewQuote($prevQuoteId);
            } else if (!$cartProducts) {
                $this->cart->truncate(); //remove all products from cart
            }

            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            // $this->cart->getQuote()->collectTotals()->save();
            $this->cart->save();

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                $baseUrl = $this->_url->getBaseUrl();
                return $this->goBack($baseUrl . 'checkout/', $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    $this->escaper->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        $this->escaper->escapeHtml($message)
                    );
                }
            }
            $url = $this->_checkoutSession->getRedirectUrl(true);
            if (!$url) {
                $cartUrl = $this->cart->getCartUrl();
                $url = $this->_redirect->getRedirectUrl($cartUrl);
            }
            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            return $this->goBack();
        }
    }

    /**
     * Create New Quote
     *
     * @return void
     */
    private function createNewQuote($prevQuoteId)
    {
        // remove and create new cart
        $quote = $this->_checkoutSession->getQuote();
        $this->_checkoutSession->replaceQuote($quote);
        $this->_checkoutSession->setQuoteId($quote->getId());
        $this->cart->setQuote($quote);
        $this->_checkoutSession->setPrevQuoteId($prevQuoteId);
    }
}
