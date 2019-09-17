<?php
namespace Neo\Winery\Model;

use Magento\Framework\Exception\SyncException;

/**
 * Synctab sync model
 */
class Activity extends \Magento\Framework\Model\AbstractModel
{
    const LOG_SUCCESS = 1;
    const LOG_FAIL = 0;
    const LOG_PENDING = 2;
    const LOG_QUEUE = 3;
    const LOG_BACKGROUND = 4;
    const LOG_WARNING = 5;

    const EMAIL_ERROR_PATH = 'grepsync_url/email_error';

    public function _construct(){
        parent::_construct();
        $this->_statusOptions = $this->getStatusOptions();
    }

    public function getStatusOptions(){
        $options = array(0=>'Fail',
                         1=>'Success',
                         2=>'Processing',
                         3=>'Queue',
                         4=>'Waiting',
                         5=>'Warning');
        return $options;
    }

    public function getActivityTypeByKey($key){
        $type = $this->getStatusOptions();
        return $type[$key];
    }
}