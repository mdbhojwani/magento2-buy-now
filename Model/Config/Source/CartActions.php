<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_BuyNow
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Mdbhojwani\BuyNow\Model\Config\Source;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class CartActions
 */
class CartActions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * CartActions Options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Remove all cart item(s) when Click Buy Now.')],
            ['value' => 1, 'label' => __('Keep other cart\'s item(s) when Click Buy Now.')],
            ['value' => 2, 'label' => __('Create a new cart and add item in cart when Click Buy Now.')]
        ];
    }
}
