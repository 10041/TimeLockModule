<?php

class OAV_TimeLock_Model_Observer {

    /**
     * @param $observer Varien_Event_Observer
     * @throws Mage_Core_Exception
     */
    public function checkout_cart_product_add_after($observer)
    {

        /** @var OAV_TimeLock_Helper_TimeChecker $helper */
        $helper = Mage::helper("TimeLock/TimeChecker");

        if ($helper->checkTime()) {

            return;
        }

        $event = $observer->getEvent();
        $productId = $event->getProduct()->getId();

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')->load($productId);
        $helper->checkProductInCategoryIdsAndGenException($product);
    }

    /**
     * @param $observer Varien_Event_Observer
     * @throws Mage_Core_Exception
     */
    public function sales_order_place_before($observer)
    {
        /** @var OAV_TimeLock_Helper_TimeChecker $helper */
        $helper = Mage::helper("TimeLock/TimeChecker");

        if ($helper->checkTime()) {

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
            $helper->checkProductInCategoryIdsAndGenException($product);
        }
    }

    /**
     * @param $observer Varien_Event_Observer
     */
    public function controller_action_predispatch_checkout_onepage_index($observer)
    {
        /** @var OAV_TimeLock_Helper_TimeChecker $helper */
        $helper = Mage::helper("TimeLock/TimeChecker");

        if ($helper->checkTime()) {

            return;
        }

        /** @var Mage_Checkout_Model_Session $model */
        $model = Mage::getModel('checkout/session');
        $quote = $model->getQuote();
        $cartItems = $quote->getAllItems();

        /** @var \Mage_Sales_Model_Quote_Item $item */
        foreach ($cartItems as $item)
        {
            $productId = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($productId);

            if($helper->checkProductInCategoryIds($product)) {
                Mage::helper('checkout/cart')->getCart()->removeItem($item->getId())->save();
            }
        }
    }
}