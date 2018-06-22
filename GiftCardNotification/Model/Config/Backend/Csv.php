<?php

namespace Coyuchi\GiftCardNotification\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Config model for e-gift cards sku file
 *
 * Class Csv
 *
 * @package Coyuchi\GiftCardNotification\Model\Config\Backend
 */
class Csv extends File
{
    /**
     * @return string[]
     */
    protected function _getAllowedExtensions() {
        return ['csv'];
    }

    /**
     * use var dir for upload
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->_mediaDirectory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::beforeSave();

        return $this;
    }
}
