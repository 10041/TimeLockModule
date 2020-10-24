<?php

class OAV_TimeLock_Model_System_Config_Source_Category
{
    /**
     * @param bool $addEmpty
     *
     * @return array
     *
     * @throws Mage_Core_Exception
     */
    public function toOptionArray($addEmpty = true)
    {
        /**
         * @var $collection Mage_Catalog_Model_Resource_Category_Collection
         */

        $categoryCollection = Mage::getResourceModel('catalog/category_collection');

        $categoryCollection->addAttributeToSelect('name')->load();

        $options = [];

        if ($addEmpty) {
            $options[] = [
                'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
                'value' => ''
            ];
        }
        /** @var Mage_Catalog_Model_Category $category */
        foreach ($categoryCollection as $category) {
            $options[] = [
                'label' => $category->getName(),
                'value' => $category->getId()
            ];
        }

        return $options;
    }
}