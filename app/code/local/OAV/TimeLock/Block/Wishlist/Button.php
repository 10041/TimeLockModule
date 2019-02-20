<?php

class OAV_TimeLock_Block_Wishlist_Button extends Mage_Wishlist_Block_Customer_Wishlist_Button
{
    /**
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function checkItems()
    {
        $wishlist = $this->getWishlist()->getItemCollection();

        /** @var OAV_TimeLock_Helper_TimeChecker $helper */
        $helper = Mage::helper("TimeLock/TimeChecker");

        foreach ($wishlist as $item) {

            if($helper->isProductLock($item->getProduct())) {

                return false;
            }
        }

        return true;
    }
}