<?php
/**
 * A Magento 2 module named TheCodingTutor/Shell
 * Copyright (C) 2017  TheCodingTutor
 * 
 * This file included in TheCodingTutor/Shell is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Neo\Commands\Controller\Adminhtml\Shell;

class Console extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Geoip List action
     *
     * @return void
     */
    public function execute()
    {
        //echo 'mmmmm';die();
        $errors = array();      // array to hold validation errors
        $data = array();      // array to pass back data
        $postData = $this->getRequest()->getPostValue();

        if (empty($postData['path']))
            $errors['path'] = 'Name is required.';

        if (empty($postData['command']))
            $errors['command'] = 'command is required.';

        // return a response ===========================================================

        // if there are any errors in our errors array, return a success boolean of false
        if ( ! empty($errors)) {

            // if there are items in our errors array, return those errors
            $data['success'] = false;
            $data['errors']  = $errors;
        } else {
            $data['success'] = true;
            //print_r($postData['add_commands']);

            if($postData['shell_type'] == 1){
                if($postData['command'] == 'chmod -R 777') {
                    $a = explode(',', $postData['add_commands']);
                    $path_new = '';
                    foreach ($a as $path) {
                        $path = $postData['path'] . "/" . $path;
                        $path_new .= $path . " ";
                    }
                    $command = $postData['command'] . " " . $path_new;
                    system($command, $retval);
                    //print_r($command);die('sdgasdg');
                    if ($retval == 0) {
                        $data['message'] = '<pre>success<pre>';
                    } else {
                        $data['message'] = '<pre>fail<pre>';
                    }
                }else{
                    $command = 'composer update';
                    system($command, $retval);
                    if ($retval == 0) {
                        $data['message'] = 'success';
                    } else {
                        $data['message'] = 'fail';
                    }
                }


            } else if($postData['shell_type'] == 2){
                // $command ="php ".$postData['path']."/".$postData['command'];
                $command ="/usr/local/lsws/lsphp70/bin/php ".$postData['command'];
                $data['message'] = "<pre>".shell_exec($command)."</pre>";
            }



        }
        
        return $this->resultJsonFactory->create()->setData($data);
    }
}