<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_BuyNow
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Mdbhojwani\BuyNow\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Quote\Api\CartRepositoryInterface;
use Mdbhojwani\BuyNow\Model\Session as CheckoutSession;

/**
 * Class UpdateQuote
 */
class UpdateQuote implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CustomerCart $cart
     * @param CartRepositoryInterface $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerCart $cart,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Assign previous quote to current quote
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $prevQuoteId = (int)$this->checkoutSession->getPrevQuoteId();
        if ($prevQuoteId) {
            $quote = $this->quoteRepository->get($prevQuoteId);
            $quote->setIsActive(1);
            $this->quoteRepository->save($quote->collectTotals());
            $this->checkoutSession->replaceQuote($quote);
            $this->checkoutSession->setQuoteId($quote->getId());
            $this->cart->setQuote($quote);
            $this->checkoutSession->unsPrevQuoteId();
        }
    }
}
