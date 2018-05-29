# magento2-order-api

## Description
Integrate Magento 2 with a logistics system

1. Orders
When order placed, create event on sales_order_place_after
    <event name="sales_order_place_after">
        <observer name="phucct_order_place_after" instance="Phucct\OrderApi\Observer\OrderApi" />
    </event>
    - On this observer get order information
    - Create function to generate data to xml
    - Send request to Logistic System
    - Get response return magento from logistics system

2. Order Status
When order shipped, create event on sales_order_shipment_save_after
    <event name="sales_order_shipment_save_after">
        <observer name="phucct_shipment" instance="Phucct\OrderApi\Observer\ShipmentApi" />
    </event>
    - On this observer get order and shipment information
    - Create function to generate data to xml
    - Send request to Logistic System
    - Get response return magento from logistics system

## Installation

```shell
# You must be in Magento root directory
composer require phucct/magento2-order-api:dev-master
php bin/magento cache:clean
php bin/magento setup:upgrade
# Execute setup:di:compile only if the store is in production mode
php bin/magento setup:di:compile