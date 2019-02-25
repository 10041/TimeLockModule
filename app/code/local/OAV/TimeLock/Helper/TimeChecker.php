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
        $this->blockTime = str_replace(',',':', $this->blockTime);
        $this->unlockTime = str_replace(',',':', $this->unlockTime);
    }


    /**
     * @param $product
     *
     * @return bool
     *
     * @throws Exception
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
     *
     * @throws Exception
     */
    public function checkTime()
    {
        $currentTime = $this->getCurrentTimeFromDateModel();
        $currentTimeTimestamp = strtotime($currentTime->format("Y-m-d G:i:s"));
        if ($currentTimeTimestamp >= $this->blockTime && $currentTimeTimestamp <= $this->unlockTime) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    private function adjustTime()
    {
        $currentTime = $this->getCurrentTimeFromDateModel();
        if ($this->blockTime >= $this->unlockTime && $currentTime->format("G:i:s") >= $this->unlockTime) {
            $this->unlockTime = strtotime(date("Y-m-d")." ".$this->unlockTime."+1 day");
        } else {
            $this->unlockTime = strtotime(date("Y-m-d")." ".$this->unlockTime);
        }
        $this->blockTime= strtotime(date("Y-m-d")." ".$this->blockTime);
    }

    /**
     * @return DateTime
     *
     * @throws Exception
     */
    private function getCurrentTimeFromDateModel()
    {
        /** @var \Mage_Core_Model_Date $coreDateModel */
        $coreDateModel = Mage::getModel('core/date');
        $currentTime = new DateTime();
        $currentTime->setTimestamp($coreDateModel->timestamp(time()));
        return $currentTime;
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     *
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
     *
     * @throws Mage_Core_Exception
     */
    public function checkProductInCategoryIdsAndGenException($product)
    {
        if (in_array($this->blockCategoryId, $product->getCategoryIds())) {
            Mage::throwException(sprintf('Buying alcohol after %s is forbidden', date("G:i", $this->blockTime)));
        }
    }

    /**
     * @return string
     */
    public function getTimeLockInterval()
    {
        return date("G:i",$this->blockTime)." - ".date("G:i", $this->unlockTime);
    }
}