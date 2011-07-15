<?php
class OvhSoapi extends AppModel {

/**
 * Description
 *
 * @var string
 */
	public $description = 'Ovh Soapi Model';

/**
 * SoapClient instance
 *
 * @var SoapClient
 */
	public $client = null;

/**
 * Session
 *
 * @var
 */
	public $session = null;

/**
 * Connection status
 *
 * @var boolean
 */
	public $connected = false;

/**
 * Connection status
 *
 * @var boolean
 */
	public $useTable = false;

/**
 * Default configuration
 *
 * @var array
 */
	public $configuration = array(
		'wsdl' => 'https://www.ovh.com/soapi/soapi-re-1.24.wsdl',
		'location' => 'fr',
		'login' => '',
		'password' => '',
	);

/**
 * Constructor
 *
 * @param array $config An array defining the configuration settings
 */
	public function __construct($configuration = array()) {

		$this->_parseConfig($configuration);
		$this->connect();
	}

/**
 * Destructor
 *
 *
 */
	public function __destruct() {

		if (!is_null($this->session)) {
			$this->client->logout($this->session);
			$this->client = null;
			$this->connected = false;
		}
	}

/**
 * Setup Configuration options
 *
 * @return array Configuration options
 */
	protected function _parseConfig($configuration = array()) {

		if (!class_exists('SoapClient')) {
			$this->error = 'Class SoapClient not found, please enable Soap extensions';
			$this->showError();
			return false;
		}

		$this->configuration = array_merge($this->configuration, $configuration);
		return $this->configuration;
	}

/**
 * Connects to the SOAP server using the WSDL in the configuration
 *
 * @param array $config An array defining the new configuration settings
 * @return boolean True on success, false on failure
 */
	public function connect() {

		try {
			$this->client = new SoapClient($this->configuration['wsdl']);
			$this->session = $this->client->login(
				$this->configuration['login'],
				$this->configuration['password'],
				$this->configuration['location'],
				false
			);
		} catch(SoapFault $fault) {
			$this->error = $fault->faultstring;
			$this->showError();
		}

		if ($this->client && $this->session) {
			$this->connected = true;
		}

		return $this->connected;
	}

/**
 * Returns the available SOAP methods
 *
 * @return array List of SOAP methods
 */
	public function listSources() {

		return $this->client->__getFunctions();
	}

/**
 * Query the SOAP server with the given method and parameters
 *
 * @return mixed Returns the result on success, false on failure
 */
	public function query($name, $queryData = null) {

		$this->error = false;
		if (!$this->connected || !$name) {
			return false;
		}

		$args = func_get_args();
		unset($args[0]);
		$params = array_merge(array($this->session), $args);

		try {
			$result = $this->client->__soapCall($name, $params);
		} catch (Exception $e) {

			$this->error = $fault->faultstring;
			$this->showError();
			return false;
		}

		return $result;
	}

/**
 * Returns the last SOAP response
 *
 * @return string The last SOAP response
 */
	public function getResponse() {

		return $this->client->__getLastResponse();
	}

/**
 * Returns the last SOAP request
 *
 * @return string The last SOAP request
 */
	public function getRequest() {

		return $this->client->__getLastRequest();
	}

/**
 * Returns SOAP session ID
 *
 * @return string The SOAP session ID
 */
	public function getSession() {

		return $this->session;
	}

/**
 * Shows an error message and outputs the SOAP result if passed
 *
 * @param string $result A SOAP result
 * @return string The last SOAP response
 */
	public function showError($result = null) {

		if (Configure::read() > 0) {
			if ($this->error) {
				trigger_error('<span style = "color:Red;text-align:left"><b>SOAP Error:</b> ' . $this->error . '</span>', E_USER_WARNING);
			}
			if (!empty($result)) {
				echo sprintf("<p><b>Result:</b> %s </p>", $result);
			}
		}
	}
}
