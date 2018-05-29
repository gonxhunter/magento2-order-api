<?php
/**
 * Created by PhpStorm.
 * User: chutienphuc
 * Date: 29/05/2018
 * Time: 11:48
 */

namespace Phucct\OrderApi\Observer;

use Magento\Framework\Event\ObserverInterface;

class ShipmentApi implements ObserverInterface
{
    /** @var Zend_Http_Client */
    private $client;

    /** @var \Magento\Framework\HTTP\Adapter\CurlFactory */
    private $curlFactory;

    public function __construct(
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
    ) {
        $this->curlFactory = $curlFactory;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //Event trigger when order shipped
        $shipment = $observer->getEvent()->getShipment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        // Enter url of logistics system
        $urlLogissticsSystem = '';

        //Generata data to xml

        $data =[
            'shipment_id' => $shipment->getId(),
            'increment_id' => $order->getIncrementId(),
            'grand_total'  => $order->getGrandTotal(),
            'email' => $order->getCustomerEmail(),
            'shipping' => $order->getShippingDescription(),
            'status' =>$order->getStatus()
        ];
        $xml_data = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
        $this->generateXml($data, $xml_data);
        // Get information of order to send data to Logistic system
        $reponse = $this->send('POST', $xml_data->asXML(), $urlLogissticsSystem);
        // Get response return from Logistics System
    }

    /**
     * Get client HTTP
     * @return Zend_Http_Client
     */
    public function getClient()
    {
        if ($this->client == null) {
            $config = ['curloptions' => [
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_HTTPHEADER, ['Content-Type: application/xml'],
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,],
            ];
            try {
                $adapter = $this->curlFactory->create();
                $this->client = new \Zend_Http_Client();
                $this->client->setAdapter($adapter);
                $adapter->setConfig($config);
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $this->client;
    }

    /**
     * Send a request to Logistics system
     * @param string $methodToCall
     * @param array $params
     * @param string|null $url
     *
     * @return Response
     */
    public function send($methodToCall, $params, $url = null)
    {
        $this->getClient()->setParameterPost('method', $methodToCall);
        $this->getClient()->setParameterPost('params', $params);
        if ($url) {
            $this->getClient()->setUri($url);
            $response = $this->getClient()->request(\Zend_Http_Client::POST);
            return $response;
        }
        return 0;
    }

    /**
     * Generate array to xml
     * @param array
     *
     * @return xml
     */
    public function generateXml($data, &$xml_data)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item'.$key;
            }
            if (is_array($value)) {
                $subnode = $xml_data->addChild($key);
                array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}
