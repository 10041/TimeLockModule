<?php

class OAV_TimeLock_Helper_TimeChecker extends Mage_Core_Helper_Abstract
{
    private $blockTime;
    private $unlockTime;
    private $blockCategoryId;

    /**
     * OAV_TimeLock_Helper_TimeChecker constructor.
     */
    public function __construct()
    {
        $this->getConfigData();
        $this->dateCommaToColon();
        $this->adjustTime();
    }

    private function getConfigData()
    {
        $this->blockTime = Mage::getStoreConfig('admin/settings/timeLock');
        $this->unlockTime = Mage::getStoreConfig('admin/settings/timeUnlock');
        $this->blockCategoryId = Mage::getStoreConfig('admin/settings/categoryId');
    }

    private function dateCommaToColon()
    {
        $this->blockTime = str_replace(",", ":", $this->blockTime);
        $this->unlockTime = str_replace(",", ":", $this->unlockTime);
    }

    /**
     * @param $product
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function isProductLock($product)
    {
        if(!$this->checkTime()) {

            return $this->checkProductInCategoryIds($product);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkTime()
    {
        /** @var Mage_Core_Model_Date $model */
        $model = Mage::getModel('core/date');
        $currentTime = strtotime(date("Y-m-d G:i:s", $model->timestamp(time())));
        if ($currentTime >= $this->blockTime && $currentTime <= $this->unlockTime) {

            return false;
        }

        return true;
    }

    private function adjustTime()
    {
        /** @var \Mage_Core_Model_Date $model */
        $model = Mage::getModel('core/date');
        $currentTime = date("G:i:s", $model->timestamp(time()));
        if ($this->blockTime >= $this->unlockTime && $currentTime >= $this->unlockTime) {
            $this->unlockTime = strtotime(date("Y-m-d")." ".$this->unlockTime."+1 day");
        } else {
            $this->unlockTime = strtotime(date("Y-m-d")." ".$this->unlockTime);
        }
        $this->blockTime= strtotime(date("Y-m-d")." ".$this->blockTime);
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     * @return bool
     */
    public function checkProductInCategoryIds($product)
    {
        if (in_array($this->blockCategoryId, $product->getCategoryIds())) {

            return true;
        }

        return false;
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     * @throws Mage_Core_Exception
     */
    public function checkProductInCategoryIdsAndGenException($product)
    {
        if (in_array($this->blockCategoryId, $product->getCategoryIds())) {
            Mage::throwException('Buying alcohol after '.date("G:i", $this->blockTime).' is forbidden.');
        }
    }

    /**
     * @return string
     */
    public function getTimeLockInterval()
    {
        $str = date("G:i",$this->blockTime)." - ".date("G:i", $this->unlockTime);

        return $str;
    }



}