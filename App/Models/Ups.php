<?php

namespace App\Models;

use PDO;
use \App\Config;


/**
 * Ordoro model
 */
class Ups extends \Core\Model
{
    // properties (UPS URLs are case sensitive)
    private $testing_ship_confirm_url         = "https://wwwcie.ups.com/ups.app/xml/ShipConfirm";
    private $testing_ship_accept_url          = "https://wwwcie.ups.com/ups.app/xml/ShipAccept";
    private $testing_void_package_shipment    = 'https://wwwcie.ups.com/ups.app/xml/Void';
    private $testing_label_recovery           = 'https://wwwcie.ups.com/ups.app/xml/LabelRecovery';

    private $production_ship_confirm_url      = 'https://onlinetools.ups.com/ups.app/xml/ShipConfirm';
    private $production_ship_accept_url       = 'https://onlinetools.ups.com/ups.app/xml/ShipAccept';
    private $production_void_package_shipment = 'https://onlinetools.ups.com/ups.app/xml/Void';
    private $production_label_recovery        = 'https://onlinetools.ups.com/ups.app/xml/LavelRecovery';

    // credentials
    private $userId = 'jimb814';
    private $password = 'Hopehope1!';
    private $accessKey = 'BD46E2A31341FB28';
    private $accountNumber = '505E33';
    private $armAcct = '2156V';  //  wrong acct number

    // soap envelope content
    private $soapEnvelope = '
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:xsd="http://www.w3.org/2001/XMLSchema"
        xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns="www.envmgr.com/LabelService"';

    // header components
    private $post_header = "POST /LabelService/EwsLabelService.asmx HTTP/1.1";
    private $host_header = "Host: labelserver.endicia.com";
    private $content_type_header = "Content-type: text/xml; charset=utf-8";

    private $FromPostalCode9 = '32601-9997';
    private $FromPostalCode5 = '32601';

    // UPS service codes
    private $ups_ground             = '03';
    private $ups_3_day_select       = '12';
    private $ups_2_day_air          = '02';
    private $ups_2_day_air_am       = '59';
    private $ups_next_day_air       = '01';
    private $ups_next_day_air_early = '14';



    /**
     * [shipConfirmClient description]
     * @return [type] [description]
     */
    public function shipConfirmClient()
    {
        // echo "Connected to shipConfirmClient() in Ups model!"; exit();

        // path to  folder to store response
        $outputFileFolder = "C:\Users\Jim\Desktop\Ups"; // development
        // $outputFileFolder = "/assets/shipping_labels/ups";  // production

        try {

        	// Create AccessRequest XMl
        	$accessRequestXML = new \SimpleXMLElement ( "<AccessRequest></AccessRequest>" );
        	$accessRequestXML->addChild ( "AccessLicenseNumber", $this->accessKey );
        	$accessRequestXML->addChild ( "UserId", $this->userId );
        	$accessRequestXML->addChild ( "Password", $this->password );

        	// Create ShipmentConfirmRequest XMl
        	$shipmentConfirmRequestXML = new \SimpleXMLElement ( "<ShipmentConfirmRequest ></ShipmentConfirmRequest>" );
        	$request = $shipmentConfirmRequestXML->addChild ( 'Request' );
        	$request->addChild ( "RequestAction", "ShipConfirm" );
        	$request->addChild ( "RequestOption", "nonvalidate" );

        	$labelSpecification = $shipmentConfirmRequestXML->addChild ( 'LabelSpecification' );
        	$labelSpecification->addChild ( "HTTPUserAgent", "" );
        	$labelPrintMethod = $labelSpecification->addChild ( 'LabelPrintMethod' );
        	$labelPrintMethod->addChild ( "Code", "GIF" );
        	$labelPrintMethod->addChild ( "Description", "" );
        	$labelImageFormat = $labelSpecification->addChild ( 'LabelImageFormat' );
        	$labelImageFormat->addChild ( "Code", "GIF" );
        	$labelImageFormat->addChild ( "Description", "" );

        	$shipment = $shipmentConfirmRequestXML->addChild ( 'Shipment' );
        	$shipment->addChild ( "Description", "" );
        	$rateInformation = $shipment->addChild ( 'RateInformation' );
        	$rateInformation->addChild ( "NegotiatedRatesIndicator", "" );

        	$shipper = $shipment->addChild ( 'Shipper' );
        	$shipper->addChild ( "Name", "Web Media Partners" );
        	$shipper->addChild ( "PhoneNumber", "9043427437" );
        	$shipper->addChild ( "TaxIdentificationNumber", "1234567877" );
        	$shipper->addChild ( "ShipperNumber", "$this->accountNumber" );
        	$shipperAddress = $shipper->addChild ( "Address" );
        	$shipperAddress->addChild ( "AddressLine1", "1341 Sylvie Ln" );
        	$shipperAddress->addChild ( "City", "Ponte Vedra" );
        	$shipperAddress->addChild ( "StateProvinceCode", "FL" );
        	$shipperAddress->addChild ( "PostalCode", "32081" );
        	$shipperAddress->addChild ( "CountryCode", "US" );

        	$shipTo = $shipment->addChild ( 'ShipTo' );
        	$shipTo->addChild ( "CompanyName", "SmileStylist" );
        	$shipTo->addChild ( "AttentionName", "Dana Burns" );
        	$shipTo->addChild ( "PhoneNumber", "9043427437" );
        	$shipToAddress = $shipTo->addChild ( "Address" );
        	$shipToAddress->addChild ( "AddressLine1", "818 N A1A Ste 209" );
        	$shipToAddress->addChild ( "City", "Ponte Vedra Beach" );
        	$shipToAddress->addChild ( "StateProvinceCode", "FL" );
        	$shipToAddress->addChild ( "PostalCode", "32082" );
        	$shipToAddress->addChild ( "CountryCode", "US" );

        	$shipFrom = $shipment->addChild ( 'ShipFrom' );
        	$shipFrom->addChild ( "CompanyName", "Web Media Partners" );
        	$shipFrom->addChild ( "AttentionName", "Shipping Dept" );
        	$shipFrom->addChild ( "PhoneNumber", "9043427437" );
        	$shipFrom->addChild ( "TaxIdentificationNumber", "1234567877" );
        	$shipFromAddress = $shipFrom->addChild ("Address" );
        	$shipFromAddress->addChild ( "AddressLine1", "1341 Sylvie Ln" );
        	$shipFromAddress->addChild ( "City", "Ponte Vedra" );
        	$shipFromAddress->addChild ( "StateProvinceCode", "FL" );
        	$shipFromAddress->addChild ( "PostalCode", "32081" );
        	$shipFromAddress->addChild ( "CountryCode", "US" );

        	$paymentInformation = $shipment->addChild ( 'PaymentInformation' );
        	$prepaid = $paymentInformation->addChild ( 'Prepaid' );
        	$billShipper = $prepaid->addChild ( 'BillShipper' );
        	$billShipper->addChild ( "AccountNumber", "$this->accountNumber" );

        	$service = $shipment->addChild ( 'Service' );
        	$service->addChild ( "Code", "02" );
        	$service->addChild ( "Description", "" );

        	$package = $shipment->addChild ( 'Package' );
        	$package->addChild ( "Description", "" );
        	$packagingType = $package->addChild ( 'PackagingType' );
        	$packagingType->addChild ( "Code", "02" );
        	$packagingType->addChild ( "Description", "" );
        	$packageWeight = $package->addChild ( 'PackageWeight' );
        	$packageWeight->addChild ( "Weight", ".5" );
        	$packageWeight->addChild ( 'UnitOfMeasurement' );

        	$requestXML = $accessRequestXML->asXML() . $shipmentConfirmRequestXML->asXML();

        	$ch = curl_init();
        	curl_setopt( $ch, CURLOPT_URL, $this->testing_ship_confirm_url );
        	curl_setopt( $ch, CURLOPT_POST, true );
        	curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        	curl_setopt( $ch, CURLOPT_POSTFIELDS, $requestXML );
        	$response = curl_exec($ch);
        	curl_close($ch);

        	if ($response == false) {
        		throw new \Exception ( "Bad data." );
        	} else {

                // simplexml_load_string interprets a string of XML into an object
                $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);

                $totalCharges = $xml->ShipmentCharges->TotalCharges->MonetaryValue;
                $unitOfMeasurement = $xml->BillingWeight->UnitOfMeasurement->Code;
                $billingWeight = $xml->BillingWeight->Weight;
                $trackingNumber = $xml->ShipmentIdentificationNumber;
                $shipmentDigest = $xml->ShipmentDigest;
                // echo $totalCharges . '<br>';
                // echo $unitOfMeasurement . '<br>';
                // echo $billingWeight . '<br>';
                // echo $trackingNumber . '<br>';
                // echo $shipmentDigest . '<br>';

        		// save request and response to file
        		$fw = fopen ( $outputFileFolder.'\\'.$trackingNumber.'.txt', 'w' );
        		fwrite ( $fw, "Request: \n" . $requestXML . "\n" );
        		fwrite ( $fw, "Response: \n" . $response . "\n" );
        		fclose ( $fw );

                // test
                // echo '<pre>';
                // print_r($xml);
                // echo '</pre>';
                // exit();

        		// get response status
        		// $resp = new \SimpleXMLElement ( $response );
        		// echo $resp->Response->ResponseStatusDescription . "\n";
        	}

        	Header ( 'Content-type: text/xml' );
        }
        catch ( \Exception $err )
        {
        	echo $err;
        }
    }




    /**
     * returns response from UPS API
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function shipmentConfirmRequest($data)
    {
        // echo "Connected to shipmentConfirmRequest() in Ups model!"; exit();

        // get service description
        $serviceDescription = $this->getServiceDescription($data['serviceCode']);

        // convert weight from ounces to lbs; API allows up to 2 decimals
        $weightInLbs = number_format( ($data['weight'] / 16), 1 );

        // path to  folder to store response
        $outputFileFolder = "C:\\xampp\htdocs\gunlaser.store\public\assets\shipping_labels\ups";  // development
        // $outputFileFolder = '/home/pamska5/public_html/gunlaser.store/public/assets/shipping_labels/ups';  // production

        try
        {
            // Create AccessRequest XMl
        	$accessRequestXML = new \SimpleXMLElement ( "<AccessRequest></AccessRequest>" );
        	$accessRequestXML->addChild ( "AccessLicenseNumber", $this->accessKey );
        	$accessRequestXML->addChild ( "UserId", $this->userId );
        	$accessRequestXML->addChild ( "Password", $this->password );

            // root element = ShipmentConfirmRequest
            $shipmentConfirmRequestXML = new \SimpleXMLElement('<ShipmentConfirmRequest></ShipmentConfirmRequest>'); // root element
            $request = $shipmentConfirmRequestXML->addChild('Request');
            $transactionReference = $request->addChild('TransactionReference');
            $transactionReference->addChild('CutomerContext', 'Web customer');
            $request->addChild('RequestAction','ShipConfirm');
            $request->addChild('RequestOption', 'validate');

            $shipment = $shipmentConfirmRequestXML->addChild('Shipment');
            $shipper = $shipment->addChild('Shipper');
            $shipper->addChild('Name', 'Web Media Partners');
            $shipper->addChild('AttentionName', 'Jim Burns');
            $shipper->addChild('CompanyDisplayableName', 'Web Media Partners');
            $shipper->addChild('PhoneNumber', '9043427437');
            $shipper->addChild('ShipperNumber', $this->accountNumber);
            $shipper->addChild('TaxIdentificationNumber', '1234567877');
            $shipperAddress = $shipper->addChild('Address');
            $shipperAddress->addChild('AddressLine1', '1341 Sylvie Ln');
            $shipperAddress->addChild('City', 'Ponte Vedra');
            $shipperAddress->addChild('StateProvinceCode', 'FL');
            $shipperAddress->addChild('PostalCode', '32081');
            $shipperAddress->addChild('CountryCode', 'US');

            $shipTo = $shipment->addChild('ShipTo');
            $shipTo->addChild('CompanyName', 'Company Name');
            $shipTo->addChild('AttentionName', $data['name']);
            $shipTo->addChild('PhoneNumber', $data['phone']);
            $shipToAddress = $shipTo->addChild('Address');
            $shipToAddress->addChild('AddressLine1', $data['address1']);
            $shipToAddress->addChild('AddressLine2', $data['address2']);
            $shipToAddress->addChild('City', $data['city']);
            $shipToAddress->addChild('StateProvinceCode', $data['state']);
            $shipToAddress->addChild('PostalCode', $data['zip']);
            $shipToAddress->addChild('CountryCode', 'US');

            $shipFrom = $shipment->addChild('ShipFrom');
            $shipFrom->addChild('CompanyName', 'Web Media Partners');
            $shipFrom->addChild('AttentionName', 'Jim Burns');
            $shipFrom->addChild('PhoneNumber', '9043427437');
            $shipFromAddress = $shipFrom->addChild('Address');
            $shipFromAddress->addChild('AddressLine1', '1341 Sylvie Ln');
            $shipFromAddress->addChild('City', 'Ponte Vedra');
            $shipFromAddress->addChild('StateProvinceCode', 'FL');
            $shipFromAddress->addChild('PostalCode', '32081');
            $shipFromAddress->addChild('CountryCode', 'US');

            $paymentInformation = $shipment->addChild('PaymentInformation');
            $prepaid = $paymentInformation->addChild('Prepaid');
            $billShipper = $prepaid->addChild('BillShipper');
            $billShipper->addChild('AccountNumber', $this->accountNumber);

            $service = $shipment->addChild('Service');
            $service->addChild('Code', $data['serviceCode']);
            $service->addChild('Description', $serviceDescription);

            $package = $shipment->addChild('Package');
            $packagingType = $package->addChild('PackagingType');
            $packagingType->addChild('Code', '02');
            $packagingType->addChild('Description', 'Package');
            $package->addChild('Description', 'Priority');
            $dimensions = $package->addChild('Dimensions');
            $unitOfMeasurement = $dimensions->addChild('UnitOfMeasurement');
            $unitOfMeasurement->addChild('Code', 'IN');
            $unitOfMeasurement->addChild('Description', 'Inches');
            $dimensions->addChild('Length', $data['length']);
            $dimensions->addChild('Width', $data['width']);
            $dimensions->addChild('Height', $data['height']);
            $packageWeight = $package->addChild('PackageWeight');
            $unitOfMeasurement = $packageWeight->addChild('UnitOfMeasurement');
            $unitOfMeasurement->addChild('Code', 'LBS');
            $unitOfMeasurement->addChild('Description', 'Pounds');
            $packageWeight->addChild('Weight', $weightInLbs);

            $labelSpecification = $shipmentConfirmRequestXML->addChild('LabelSpecification');
            $labelPrintMethod = $labelSpecification->addChild('LabelPrintMethod');
            $labelPrintMethod->addChild('Code', 'GIF');
            $labelPrintMethod->addChild('Description', 'GIF');
            $labelImageFormat = $labelSpecification->addChild('LabelImageFormat');
            $labelImageFormat->addChild('Code', 'GIF');
            $labelImageFormat->addChild('Description', 'GIF');

            $requestXML = $accessRequestXML->asXML() . $shipmentConfirmRequestXML->asXML();

        	$ch = curl_init();
        	curl_setopt( $ch, CURLOPT_URL, $this->testing_ship_confirm_url );
        	curl_setopt( $ch, CURLOPT_POST, true );
        	curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        	curl_setopt( $ch, CURLOPT_POSTFIELDS, $requestXML );
        	$response = curl_exec($ch);
        	curl_close($ch);

        	if ($response == false)
            {
        		throw new \Exception ( "Bad data." );
        	}
            // success
            else
            {
                // simplexml_load_string interprets a string of XML into an object (option:  $respObj = new SimpleXMLElement($response))
                $respObj = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);

                // store tracking number  for use in naming log file
                $trackingNumber= $respObj->ShipmentIdentificationNumber;

                // save request and response to file & store in ups/log_confirm folder
        		$fw = fopen ( $outputFileFolder.'\\'.$trackingNumber.'_confirm.xml', 'w' );  // development
        		// $fw = fopen ( $outputFileFolder.'/'.$trackingNumber.'_confirm.xml', 'w' );  // production
        		fwrite ( $fw, "Request: \n" . $requestXML . "\n" );
        		fwrite ( $fw, "Response: \n" . $response . "\n" );
        		fclose ( $fw );

                // return object to controller for json_encode & return to Ajax request
                return $respObj;
        	}

            Header('Content-type: text/xml');
        }
        catch (\Exception $err) {
            echo $err;
        }

    }




    public function shipmentAcceptRequest($shipmentDigest)
    {
        // path to  folder to store response
        $outputFileFolder = "C:\\xampp\htdocs\gunlaser.store\public\assets\shipping_labels\ups";  // development
        // $outputFileFolder = '/home/pamska5/public_html/gunlaser.store/public/assets/shipping_labels/ups';  // production

        // credentials
        $accessRequestXML = new \SimpleXMLElement('<AccessRequest></AccessRequest>');
        $accessRequestXML->addChild('AccessLicenseNumber', $this->accessKey);
        $accessRequestXML->addChild('UserId', $this->userId);
        $accessRequestXML->addChild('Password', $this->password);

        // root element = ShipmentAcceptRequest
        $shipmentAcceptRequestXML = new \SimpleXMLElement('<ShipmentAcceptRequest></ShipmentAcceptRequest>');
        $request = $shipmentAcceptRequestXML->addChild('Request');
        $transactionReference = $request->addChild('TransactionReference');
        $transactionReference->addChild('CustomerContext', 'your customer context');
        $request->addChild('RequestAction', 'ShipAccept');
        $request->addChild('RequestOption', '01');
        $shipmentAcceptRequestXML->addChild('ShipmentDigest', "$shipmentDigest");

        // store two request elements (credentials, request) in variable
        $requestXML = $accessRequestXML->asXML() . $shipmentAcceptRequestXML->asXML();

        // cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->testing_ship_accept_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXML);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response == false)
        {
            throw new \Exception('Bad data');
        }

        // interpret XML strig as PHP object of the class SimpleXMLElement
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);

        // failure
        if ($xml->Response->ResponseStatusCode != 1)
        {
            echo 'API error: ' . $xml->Response->ResponseStatusDescription;
            exit();
        }
        // success
        {
            // test - display response
            // echo "<h4>API response -- ShipmentAcceptRequest()</h4>";
            // echo '<pre>';
            // print_r($xml);
            // echo '</pre>';
            // exit();

            // save request and response to file
            $fw = fopen ( $outputFileFolder.'\\'.$xml->ShipmentResults->ShipmentIdentificationNumber.'_accept.xml', 'w' );  // development
            // $fw = fopen ( $outputFileFolder.'/'.$xml->ShipmentResults->ShipmentIdentificationNumber.'_accept.xml', 'w' );   // production
            fwrite ( $fw, "Request: \n" . $requestXML . "\n" );
            fwrite ( $fw, "Response: \n" . $response . "\n" );
            fclose ( $fw );

            // store graphic image in variable  for use in function
            $graphicImage = $xml->ShipmentResults->PackageResults->LabelImage->GraphicImage;

            // create object to store data
            $accept_response_data = new \stdClass();

            // add properties with values to object
            $accept_response_data->status = (string) $xml->Response->ResponseStatusDescription;
            $accept_response_data->totalCharges = (string) $xml->ShipmentResults->ShipmentCharges->TotalCharges->MonetaryValue;
            $accept_response_data->trackingNumber = (string) $xml->ShipmentResults->ShipmentIdentificationNumber;

            // test
            // echo "<h4>accept_response_data object</h4>";
            // echo '<pre>';
            // print_r($accept_response_data);
            // echo '</pre>';
            // exit();

            // Assign target directory based on server

            // production
            if ($_SERVER['SERVER_NAME'] != 'localhost')
            {
                // path for live server @IMH
                $target_dir = $outputFileFolder;

                $output_file = $target_dir . '/'.$accept_response_data->trackingNumber.'.jpg';
            }
            else
            // development
            {
                // path for local machine
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . '\assets\shipping_labels\ups';

                $output_file = $target_dir . '\\'.$accept_response_data->trackingNumber.'.jpg';
            }

            // convert base64 string to jpg image & store in designated folder
            $label = $this->base64_to_jpeg($graphicImage, $output_file);

            // return object to controller
            return $accept_response_data;
            exit();
        }
    }



    /**
     * voids UPS Label
     *
     * @param  String   $comment            Reason for voiding label
     * @param  String   $trackingNumber     Tracking number of label
     * @return Boolean
     */
    public function voidLabel($comment, $trackingNumber)
    {
        // path to  folder to store response
        $outputFileFolder = "C:\\xampp\htdocs\gunlaser.store\public\assets\shipping_labels\ups";  // development
        // $outputFileFolder = '/home/pamska5/public_html/gunlaser.store/public/assets/shipping_labels/ups';  // production

        // credentials
        $accessRequestXML = new \SimpleXMLElement('<AccessRequest></AccessRequest>');
        $accessRequestXML->addChild('AccessLicenseNumber', $this->accessKey);
        $accessRequestXML->addChild('UserId', $this->userId);
        $accessRequestXML->addChild('Password', $this->password);

        // root element = VoidShipmentRequest
        $voidShipmentRequestXML = new \SimpleXMLElement('<VoidShipmentRequest></VoidShipmentRequest>');
        $request = $voidShipmentRequestXML->addChild('Request');
        $transactionReference = $request->addChild('TransactionReference');
        $transactionReference->addChild('CustomerContext', "$comment");
        $request->addChild('RequestAction', '1');
        $voidShipmentRequestXML->addChild('ShipmentIdentificationNumber', "$trackingNumber");

        // store two request elements (credentials, request) in variable
        $requestXML = $accessRequestXML->asXML() . $voidShipmentRequestXML->asXML();

        // cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->testing_void_package_shipment);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXML);
        $response = curl_exec($ch);
        curl_close($ch);

        // echo $response;
        // exit();

        if ($response == false)
        {
            throw new \Exception('Bad data');
        }

        // interpret XML strig as PHP object of the class SimpleXMLElement
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);

        // test - display response
        // echo "<h4>API response -- voidShipmentResponse()</h4>";
        // echo '<pre>';
        // print_r($xml);
        // echo '</pre>';
        // exit();

        // failure
        if ($xml->Response->ResponseStatusCode != 1)
        {
            echo 'API error description: ' . $xml->Response->ResponseStatusDescription;
            // email webmaster
            exit();
        }
        // success
        else
        {
            // save request and response to file
            $fw = fopen ( $outputFileFolder.'\\'.$trackingNumber.'_void.xml', 'w' );  // development
            // $fw = fopen ( $outputFileFolder.'/'.$trackingNumber.'_void.xml', 'w' );   // production
            fwrite ( $fw, "Request: \n" . $requestXML . "\n" );
            fwrite ( $fw, "Response: \n" . $response . "\n" );
            fclose ( $fw );

            // return object to controller
            return $xml;
            exit();
        }
    }



    /**
     * creates return label, stores request & reponse & label image data
     *
     * @param  String   $comment            Reason for return
     * @param  String   $trackingNumber     UPS tracking number of order
     * @return Boolean
     */
    public function labelRecovery($comment, $trackingNumber)
    {
        // path to  folder to store response
        $outputFileFolder = "C:\\xampp\htdocs\gunlaser.store\public\assets\shipping_labels\ups";  // development
        // $outputFileFolder = '/home/pamska5/public_html/gunlaser.store/public/assets/shipping_labels/ups';  // production

        // credentials
        $accessRequestXML = new \SimpleXMLElement('<AccessRequest></AccessRequest>');
        $accessRequestXML->addChild('AccessLicenseNumber', $this->accessKey);
        $accessRequestXML->addChild('UserId', $this->userId);
        $accessRequestXML->addChild('Password', $this->password);

        // root element = VoidShipmentRequest
        $labelRecoveryRequestXML = new \SimpleXMLElement('<LabelRecoveryRequest></LabelRecoveryRequest>');
        $request = $labelRecoveryRequestXML->addChild('Request');
        $transactionReference = $request->addChild('TransactionReference');
        $transactionReference->addChild('CustomerContext', "$comment");
        $request->addChild('RequestAction', 'LabelRecovery');
        $labelSpecification = $labelRecoveryRequestXML->addChild('LabelSpecification');
        $labelImageFormat = $labelSpecification->addChild('LabelImageFormat');
        $labelImageFormat->addChild('Code', 'GIF');
        $labelDelivery = $labelRecoveryRequestXML->addChild('LabelDelivery');
        $labelDelivery->addChild('LabelLinkIndicator');
        $labelRecoveryRequestXML->addChild('TrackingNumber', "$trackingNumber");

        // store two request elements (credentials, request) in variable
        $requestXML = $accessRequestXML->asXML() . $labelRecoveryRequestXML->asXML();

        // cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->testing_label_recovery);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestXML);
        $response = curl_exec($ch);
        curl_close($ch);

        // test
        // echo '<h4>Response</h4>';
        // echo $response . '<br><br>';
        // exit();

        if ($response == false)
        {
            throw new \Exception('Bad data');
        }

        // interpret XML string as PHP object of the class SimpleXMLElement
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);

        // test - display response
        // echo "<h4>API response -- LabelRecoveryRequest()</h4>";
        // echo '<pre>';
        // print_r($xml);
        // echo '</pre>';
        // exit();

        // failure
        if ($xml->Response->ResponseStatusCode != 1)
        {
            echo 'API error description: ' . $xml->Response->ResponseStatusDescription;
            // email webmaster
            exit();
        }
        // success
        else
        {
            // save request and response to file
            $fw = fopen ( $outputFileFolder.'\\'.$trackingNumber.'_return_label.xml', 'w' );  // development
            // $fw = fopen ( $outputFileFolder.'/'.$trackingNumber.'_void.xml', 'w' );   // production
            fwrite ( $fw, "Request: \n" . $requestXML . "\n" );
            fwrite ( $fw, "Response: \n" . $response . "\n" );
            fclose ( $fw );

            // store graphic image in variable  for use in function
            $graphicImage = $xml->LabelResults->LabelImage->GraphicImage;

            // create object to store data
            $label_recovery_response_data = new \stdClass();

            // add properties with values to object
            $label_recovery_response_data->status = (string) $xml->Response->ResponseStatusDescription;
            $label_recovery_response_data->url = (string) $xml->LabelResults->LabelImage->URL;
            $label_recovery_response_data->reason = (string) $xml->Response->TransactionReference->CustomerContext;
            $label_recovery_response_data->trackingNumber = (string) $xml->ShipmentIdentificationNumber;

            // Assign target directory based on server
            // production
            if ($_SERVER['SERVER_NAME'] != 'localhost')
            {
                // path for live server @IMH
                $target_dir = $outputFileFolder;

                $output_file = $target_dir . '/'.$label_recovery_response_data->trackingNumber.'_return_label.pdf';
            }
            else
            // development
            {
                // path for local machine
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . '\assets\shipping_labels\ups';

                $output_file = $target_dir . '\\'.$label_recovery_response_data->trackingNumber.'_return_label.pdf';
            }

            // convert base64 string to jpg image & store in designated folder
            $label = $this->base64_to_pdf($graphicImage, $output_file);

            // return object to controller
            return $label_recovery_response_data;
            exit();
        }
    }




    // - - - - - - - class functions - - - - - - - - - - - - - - - - //
    /**
     * returns string description of shipper & ship method
     * @param  String   $serviceCode    UPS service code
     * @return String                   The string description
     */
    public function getServiceDescription($serviceCode)
    {
        // echo $serviceCode; exit();

        // set value of discount coupon
        switch ($serviceCode)
        {
            CASE '03':
                $serviceDescription = 'UPS Ground';
                break;
            CASE '12':
                $serviceDescription = 'UPS 3 Day Select';
                break;
            CASE '02':
                $serviceDescription = 'UPS 2nd Day Air';
                break;
            CASE '59':
                $serviceDescription = 'UPS 2nd Day Air A.M.';
                break;
            CASE '01':
                $serviceDescription = 'UPS Next Day Air';
                break;
            CASE '14':
                $serviceDescription = 'UPS Next Day Air Early';
                break;
            CASE '13':
                $serviceDescription = 'UPS Next Day Air Saver';
                break;
            default:
                $serviceDescription = 'error';
        }

        return $serviceDescription;
    }




    public function base64_to_jpeg($base64_string, $output_file)
    {
        // open the output file for writing
        $ifp = fopen( $output_file, 'wb' );

        if (strlen($base64_string) > 1)
        {
            // we could add validation here with ensuring $base64_string > 1
            fwrite( $ifp, base64_decode( $base64_string ) );

            // clean up the file resource
            fclose( $ifp );

            return $output_file;
        }
        else
        {
            echo "Base64 string for label does not exist.";
            exit();
        }
    }



    public function base64_to_pdf($base64_string, $output_file)
    {
        // open the output file for writing
        $ifp = fopen( $output_file, 'wb' );

        if (strlen($base64_string) > 1)
        {
            // we could add validation here with ensuring $base64_string > 1
            fwrite( $ifp, base64_decode( $base64_string ) );

            // clean up the file resource
            fclose( $ifp );

            return $output_file;
        }
        else
        {
            echo "Base64 string for pdf label does not exist.";
            exit();
        }
    }



    public function writeFileToServer($output_file)
    {
        // open the output file for writing
        $ifp = fopen( $output_file, 'wb' );

        if (strlen($output_file) > 1)
        {
            // we could add validation here with ensuring $base64_string > 1
            fwrite( $ifp);

            // clean up the file resource
            fclose( $ifp );

            return $output_file;
        }
        else
        {
            echo "File does not exist.";
            exit();
        }
    }


}
