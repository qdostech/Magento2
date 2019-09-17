<?php

// If we're calling this directly, exit


/**
 * Fetch information about Camec products.
 * Queries the remote Camec Magento shopping cart system.
 *
 */
ini_set('display_errors','On');
// if(!extension_loaded("soap"))
// {
//   dl("php_soap.dll");
// } 

ini_set("soap.wsdl_cache_enabled","0");
//use Magento\Framework\App\Config\ScopeConfigInterface;

class ISC_SUPPLIERSTOCK
{
	
	/**
	 * @var string Username to connect to the remote system
	 */
	protected $_username = '';
	/**
	 * @var string Password to connect to the remote system
	 */
	protected $_password = '';

	/**
	 * @var string
	 */
	protected $wsdl = '';

	//protected $store_url = 'http://viverbrazil.com.au';
    protected $domain = '';

	/**
	 * @var object SOAP client in use to communicate with the remote system
	 */
	public $client = null;

	/**
	 * @var string Session ID with the remote system
	 */
	public $session = null;

	/**
	 * @var string The supported suppliers of this class
	 */
	//protected $supported = array('Camec', 'Aussie Traveller');

	/**
	 * @var string The default supplier if required and none is provided
	 */
	//protected $defaultSupplier = 'Camec';

	/**
	 * @var string The supplier that is active and functions will cater for
	 * for specific behaviour and funtionality
	 */
	//protected $currentSupplier = '';

	/**
	 * The constructor.
	 */


	public function __construct()//\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
	{
		$this->connect();
		//$this->scopeConfig = $scopeConfig;
	}

	

	/**
	 * Connect to the remote system
	 */
	public function connect()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->wsdl = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/webServices/url_path');
		$this->domain = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/webServices/domain');
		$this->_username = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/soap_login_information/soap_username');
		$this->_password = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('qdosConfig/soap_login_information/soap_password');
		try { 
			$soapClient = new \Zend\Soap\Client($this->wsdl);
			$soapClient->setSoapVersion(SOAP_1_2);
			$soapClient->setHttpLogin($this->_username);
			$soapClient->setHttpPassword($this->_password);
			$auth = '<CredentialSoapHeader xmlns="'.$this->domain.'"><Login>' . $this->_username . '</Login><Password>' . $this->_password . '</Password></CredentialSoapHeader>';
			$var = new SoapVar($auth, XSD_ANYXML);
			$opts = ['http' => ['header' => "Authorization: Bearer ". $auth]];
			$context = stream_context_create($opts);
			$header = new SoapHeader($this->domain, 'authHeaderRequest', $var);
			$soapClient->addSoapInputHeader($header);
		} catch (SoapFault $e) {
			$error['error']= 'SOAP fault: '.$e->getMessage();
			return $error ;
		}
		return $soapClient;
	}

    /* Print the log 
    */
    public function setLog($message,$var,$fileName){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/'.$fileName);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
 		$logger->info($message);
        return $logger;
    }


   /* Disconnect from the remote system
	 */
	public function disconnect()
	{
		if($this->session !== null) {
			try {
				$this->client->endSession($this->session);
			}
			catch (SoapFault $e) {
				$this->throwError('Unable to disconnect from remote system: '.$e->getMessage());
				return false;
			}
			$this->session = null;
			return $this;
		}
		else $this->throwError('No session to disconnect from.');
	}


	/**
	 * Throw an exception and set a description for the error that's occurred.
	 *
	 * @param string The error message
	 * @throws Exception
	 */
	public function throwError($msg=null) {
		if($msg==null) $msg=get_class().' parse error.';

		// determine origin that called this function
		$bt = debug_backtrace();
		$caller = array_shift($bt);

		$msg = $caller['file'].' at line '.$caller['line'].': '.$msg;

		throw new Exception($msg);
	}

	
}

?>