<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_BuyNow
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Mdbhojwani\BuyNow\Helper;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Buynow button title path
     */
    const BUYNOW_IS_ENABLED_PATH = 'mdbhojwani_buynow/general/is_enabled';

    /**
     * Buynow button title path
     */
    const BUYNOW_BUTTON_TITLE_PATH = 'mdbhojwani_buynow/general/button_title';

    /**
     * Buynow button title
     */
    const BUYNOW_BUTTON_TITLE = 'Buy Now';

    /**
     * Addtocart button form id path
     */
    const ADDTOCART_FORM_ID_PATH = 'mdbhojwani_buynow/general/addtocart_id';

    /**
     * Addtocart button form id
     */
    const ADDTOCART_FORM_ID = 'product_addtocart_form';

    /**
     * Cart actions path
     */
    const CART_ACTIONS_PATH = 'mdbhojwani_buynow/general/cart_actions';

    /**
     * Retrieve config value
     *
     * @return string
     */
    public function getConfig($config)
    {
        return $this->scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is module enabled
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getConfig(self::BUYNOW_IS_ENABLED_PATH);
    }

    /**
     * Get button title
     * @return string
     */
    public function getButtonTitle()
    {
        $btnTitle = $this->getConfig(self::BUYNOW_BUTTON_TITLE_PATH);
        return $btnTitle ? $btnTitle : self::BUYNOW_BUTTON_TITLE;
    }

    /**
     * Get addtocart form id
     * @return string
     */
    public function getAddToCartFormId()
    {
        $addToCartFormId = $this->getConfig(self::ADDTOCART_FORM_ID_PATH);
        return $addToCartFormId ? $addToCartFormId : self::ADDTOCART_FORM_ID;
    }

    /**
     * Get cart actions
     * @return string
     */
    public function getCartActions()
    {
        return $this->getConfig(self::CART_ACTIONS_PATH);
    }
}
