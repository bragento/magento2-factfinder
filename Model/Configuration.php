<?php
namespace Flagbit\FACTFinder\Model;

use FACTFinder\Core\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const HTTP_AUTH     = 'http';
    const SIMPLE_AUTH   = 'simple';
    const ADVANCED_AUTH = 'advanced';
    const XML_CONFIG_PATH = 'factfinder/search';
    const DEFAULT_SEMAPHORE_TIMEOUT = 7200; // 60 seconds = 2 hours

    private $config;
    private $storeConfig;
    private $authType;
    private $secondaryChannels;
    private $storeId = null;

    // Should the search adapters retrieve only product ids? (otherwise, full records will be requested)
    private $idsOnly = true;

    /**
     * @param \Magento\Framework\DataObject                          $configWrapper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $storeConfig
     */
    public function __construct(
        \Magento\Framework\DataObject $configWrapper,
        \Magento\Framework\App\Config\ScopeConfigInterface $storeConfig
    )
    {
        $this->config = $configWrapper->addData($storeConfig->getValue('factfinder/search'));
        $this->storeConfig = $storeConfig;
    }

    public function __sleep()
    {
        foreach(get_class_methods($this) as $method){
            if(substr($method, 0, 3) != 'get'
                || $method == 'getCustomValue'){
                continue;
            }
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }

        return array('config');
    }


    /**
     * @return array of strings
     **/
    public function getSecondaryChannels()
    {
        if($this->secondaryChannels == null)
        {
            // array_filter() is used to remove empty channel names
            $this->secondaryChannels = array_filter(explode(';', $this->getCustomValue('secondary_channels')));
        }

        return $this->secondaryChannels;
    }

    /**
     * Allows to catch configuration for certain store id.
     * If given store id differs from internal store id, then configuration is cleared.
     *
     * @param int $storeId
     * @return FACTFinderCustom_Configuration
     */
    public function setStoreId($storeId)
    {
        if ($this->storeId != $storeId) {
            $this->config = new Varien_Object();
        }

        $this->storeId = $storeId;

        return $this;
    }

    /**
     * @param string $name
     * @return string value
     */
    public function getCustomValue($name)
    {
        if(!$this->config->hasData($name)){
            try{
                $this->config->setData($name,  $this->storeConfig->getValue(self::XML_CONFIG_PATH.'/'.$name));
            } catch (\Exception $e){
                $this->config->setData($name, null);
            }
        }
        return $this->config->getData($name);
    }

    /**
     * @return boolean
     */
    public function isDebugEnabled()
    {
        return $this->getCustomValue('debug') == 'true';
    }

    /**
     * @return string
     */
    public function getRequestProtocol()
    {
        $protocol = $this->getCustomValue('protocol');
        // legacy code because of wrong spelling
        if (!$protocol) {
            $protocol = $this->getCustomValue('protokoll');
        }
        return $protocol;
    }

    /**
     * @return string
     */
    public function getServerAddress()
    {
        return $this->getCustomValue('address');
    }

    /**
     * @return int
     */
    public function getServerPort()
    {
        return $this->getCustomValue('port');
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->getCustomValue('context');
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->getCustomValue('channel');
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->getCustomValue('language');
    }

    public function getAuthType()
    {
        if ($this->authType == null) {
            $this->authType = $this->getCustomValue('auth_type');
            if ( $this->authType != self::HTTP_AUTH
                && $this->authType != self::SIMPLE_AUTH
                && $this->authType != self::ADVANCED_AUTH ) {
                $this->authType = self::HTTP_AUTH;
            }
        }
        return $this->authType;
    }

    /**
     * @return boolean
     */
    public function isHttpAuthenticationType()
    {
        return $this->getAuthType() == self::HTTP_AUTH;
    }

    /**
     * @return boolean
     */
    public function isSimpleAuthenticationType()
    {
        return $this->getAuthType() == self::SIMPLE_AUTH;
    }

    /**
     * @return boolean
     */
    public function isAdvancedAuthenticationType()
    {
        return $this->getAuthType() == self::ADVANCED_AUTH;
    }

    public function getUserName()
    {
        return $this->getCustomValue('auth_user');
    }

    public function getPassword()
    {
        return $this->getCustomValue('auth_password');
    }

    public function getAuthenticationPrefix()
    {
        return $this->getCustomValue('auth_advancedPrefix');
    }

    public function getAuthenticationPostfix()
    {
        return $this->getCustomValue('auth_advancedPostfix');
    }

    public function getClientMappings()
    {
        return array(
            'query' => 'q',
            'page'  => 'p',
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getServerMappings()
    {
        return array();
    }

    public function getIgnoredClientParameters()
    {
        return array(
            'channel' => true,
            'format' => true,
            'log' => true,
            'productsPerPage' => true,
            'query' => true,
            'catalog' => true,
            'navigation' => true
        );
    }

    public function getIgnoredServerParameters()
    {
        return array(
            'q' => true,
            'p' => true,
            'limit' => true,
            'is_ajax' => true,
            'type_search' => true,
            'order' => true,
            'dir' => true,
            'mode' => true
        );
    }

    public function getRequiredClientParameters()
    {
        return array();
    }

    public function getRequiredServerParameters()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getDefaultConnectTimeout()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getDefaultTimeout()
    {
        return 4;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getSuggestConnectTimeout()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getSuggestTimeout()
    {
        return 2;
    }

    public function getTrackingConnectTimeout()
    {
        return 2;
    }


    public function getTrackingTimeout()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getImportConnectTimeout()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getImportTimeout()
    {
        return 360;
    }


    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getPageContentEncoding()
    {
        return $this->getCustomValue('encoding/pageContent');
    }


    /**
     * {@inheritdoc}
     */
    public function getClientUrlEncoding()
    {
        return $this->getCustomValue('encoding/pageURI');
    }


    /**
     * Sets the idsOnly flag, which determines whether product ids or full records should be requested by the search adapters.
     * Request only products ids if true, full records otherwise
     *
     * @param bool $value
     **/
    public function setIdsOnly($value)
    {
        $this->idsOnly = $value;
    }

    /**
     * Gets the idsOnly flag, which determines whether product ids or full records should be requested by the search adapters
     *
     * @return bool
     **/
    public function getIdsOnly()
    {
        return $this->idsOnly;
    }


    /**
     * {@inheritdoc}
     */
    public function getWhitelistClientParameters()
    {
        return array();
    }


    /**
     * {@inheritdoc}
     */
    public function getWhitelistServerParameters()
    {
        return array();
    }


}