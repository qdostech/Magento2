<?php
namespace Qdos\QdosSync\Block\Adminhtml\Sync;

/**
 * CMS block edit form container
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct()
    {
		$this->_objectId = 'id';
        $this->_blockGroup = 'Qdos_QdosSync';
        $this->_controller = 'adminhtml_sync';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Block'));
        $this->buttonList->update('delete', 'label', __('Delete Block'));

        $this->buttonList->add(
            'saveandcontinue',
            array(
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
                )
            ),
            -100
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'hello_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'hello_content');
                }
            }
        ";
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('checkmodule_checkmodel')->getId()) {
            return __("Edit Item '%1'", $this->escapeHtml($this->_coreRegistry->registry('checkmodule_checkmodel')->getTitle()));
        } else {
            return __('New Item');
        }
    }


/*START : Create custom button to call webservices*/
    protected function _prepareLayout()
        {
            /*echo "Controller Name: ".$controllerName = $this->getRequest()->getControllerName();
            echo "action name :".$actionName = $this->getRequest()->getActionName();
            echo "Route Name:".$routeName = $this->getRequest()->getRouteName();
            echo "Module Name: ".$moduleName = $this->getRequest()->getModuleName();*/
            //echo $this->getUrl('qdossync/syncgrid/edit/testButtonClick') ;
            //exit;

            $this->getToolbar()->addChild(
                        'custom_buttonfor_myaction',
                        'Magento\Backend\Block\Widget\Button',
                        [
                            'label' => __('Custom Button'),
                            'title' => __('Custom Button'),
                            'onclick' => 'setLocation(\'' . $this->getUrl(
                                'qdossync/syncgrid/newbutton'
                               ) . '\')',
                            'class' => 'action-default primary'
                        ]
                    );
            return parent::_prepareLayout();
        }
/*END : Create custom button to call webservices*/

       

}
