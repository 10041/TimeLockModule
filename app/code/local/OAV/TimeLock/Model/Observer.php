<?php

class OAV_TimeLock_Model_Observer
{
    /**
     * @param $observer Varien_Event_Observer
     *
     * @throws Mage_Core_Exception
     * @throws Exception
     */
    public function checkout_cart_product_add_after($observer)
    {
        /** @var OAV_TimeLock_Helper_TimeChecker $timeCheckerHelper */
        $timeCheckerHelper = Mage::helper("TimeLock/TimeChecker");

        if ($timeCheckerHelper->checkTime()) {
            return;
        }

        $event = $observer->getEvent();
        $productId = $event->getProduct()->getId();

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')->load($productId);
        $timeCheckerHelper->checkProductInCategoryIdsAndGenException($product);
    }

    /**
     * @param $observer Varien_Event_Observer
     *
     * @throws Exception
     */
    public function sales_order_place_before($observer)
    {
        /** @var OAV_TimeLock_Helper_TimeChecker $timeCheckerHelper */
        $timeCheckerHelper = Mage::helper("TimeLock/TimeChecker");

        if ($timeCheckerHelper->checkTime()) {
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();
        $items = $order->getAllItems();

        foreach ($items as $item) {
            /** @var $item Mage_Sales_Model_Order_Item*/
            $productId = $item->getProduct()->getId();

            /** @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('catalog/product')->load($productId);
            $timeCheckerHelper->checkProductInCategoryIdsAndGenException($product);
        }
    }

    /**
     * @param $observer Varien_Event_Observer
     *
     * @throws Exception
     */
    public function controller_action_predispatch_checkout_onepage_index($observer)
    {
        /** @var OAV_TimeLock_Helper_TimeChecker $timeCheckerHelper */
        $timeCheckerHelper = Mage::helper("TimeLock/TimeChecker");

        if ($timeCheckerHelper->checkTime()) {
            return;
        }

        /** @var Mage_Checkout_Model_Session $checkoutSessionModel */
        $checkoutSessionModel = Mage::getModel('checkout/session');
        $quote = $checkoutSessionModel->getQuote();
        $cartItems = $quote->getAllItems();

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($cartItems as $item) {
            $productId = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($productId);

            if($timeCheckerHelper->checkProductInCategoryIds($product)) {
                Mage::helper('checkout/cart')->getCart()->removeItem($item->getId())->save();
            }
        }
    }
}