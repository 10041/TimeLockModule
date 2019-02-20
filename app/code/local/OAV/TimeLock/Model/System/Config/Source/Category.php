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

        $collection = Mage::getResourceModel('catalog/category_collection');

        $collection->addAttributeToSelect('name')
            ->load();

        $options = array();

        if ($addEmpty) {
            $options[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please Select a Category --'),
                'value' => ''
            );
        }
        foreach ($collection as $category) {
            $options[] = array(
                'label' => $category->getName(),
                'value' => $category->getId()
            );
        }

        return $options;
    }
}