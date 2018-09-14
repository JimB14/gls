<?php

namespace App\Models;

use PDO;
use \App\Config;


/**
 * Endicia model
 */
class Endicia extends \Core\Model
{
    // properties
    private $url = "https://elstestserver.endicia.com/LabelService/EwsLabelService.asmx";
    private $passPhrase = 'Hopehope3#';
    private $accountId = '2549867';
    private $requesterId = 'lxxx';

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






    // methods
    public function getPassPhrase()
    {
        return $this->passPhrase;
    }


    public function getAccountId()
    {
        return $this->accountId;
    }


    public function getPostUrl()
    {
        return $this->url;
    }


    public function getRequesterId()
    {
        return $this->requesterId;
    }





    /**
     * returns account status from Endicia API
     *
     * @return Array        Account status data
     */
    public function getAccountStatus()
    {
        // soap action
        $soapAction = 'www.envmgr.com/LabelService/GetAccountStatus';

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope '.$this->soapEnvelope.'>
                                <soap:Body>
                                    <GetAccountStatus xmlns="www.envmgr.com/LabelService">
                                        <AccountStatusRequest ResponseVersion="1">
                                            <RequesterID>'.$this->requesterId.'</RequesterID>
                                            <RequestID>12</RequestID>
                                            <CertifiedIntermediary>
                                               <AccountID>'.$this->accountId.'</AccountID>
                                               <PassPhrase>'.$this->passPhrase.'</PassPhrase>
                                            </CertifiedIntermediary>
                                        </AccountStatusRequest>
                                    </GetAccountStatus>
                                </soap:Body>
                            </soap:Envelope>';
        $headers = [
            $this->post_header,
            $this->host_header,
            $this->content_type_header,
            "Content-length: ".strlen($xml_post_string),
            "SOAPAction: $soapAction"
        ];

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        // disable warning
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);
        // resource: https://stackoverflow.com/questions/20393140/how-to-read-soap-response-xml-in-php?utm_medium=organic&utm_source=google_rich_qa&utm_campaign=google_rich_qa
        $xml_response = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->GetAccountStatusResponse;

        // test
        // echo '<h4>Endicia API response:</h4>';
        // echo '<pre>';
        // print_r($xml_response);
        // echo '</pre>';
        // exit();

        // create array of key fields from API response (turned into PHP object w/ simplexml_load_string())
        $account = [
            'AccountID' => (string) $xml_response->AccountStatusResponse->CertifiedIntermediary->AccountID,
            'PostageBalance' => (string) $xml_response->AccountStatusResponse->CertifiedIntermediary->PostageBalance,
            'AccountStatus' => (string) $xml_response->AccountStatusResponse->CertifiedIntermediary->AccountStatus,
            'AccountType' => (string) $xml_response->AccountStatusResponse->AccountType,
            'FirstName' => (string) $xml_response->AccountStatusResponse->Address->FirstName,
            'LastName' => (string) $xml_response->AccountStatusResponse->Address->LastName,
            'Address1' => (string) $xml_response->AccountStatusResponse->Address->Address1,
            'City' => (string) $xml_response->AccountStatusResponse->Address->City,
            'State' => (string) $xml_response->AccountStatusResponse->Address->State,
            'ZipCode' => (string) $xml_response->AccountStatusResponse->Address->ZipCode,
            'ZipCodeAddOn' => (string) $xml_response->AccountStatusResponse->Address->ZipCodeAddOn,
            'PhoneNumber' => (string) $xml_response->AccountStatusResponse->Address->PhoneNumber
        ];

        // test
        // echo '<h4>Account array:</h4>';
        // echo '<pre>';
        // print_r($account);
        // echo '</pre>';
        // exit();

        // return to controller
        return $account;
    }





    /**
     * Buy postage from Endicia
     *
     * @return Array        Account status data
     */
    public function buyPostage($amount)
    {
        // soap action
        $soapAction = 'www.envmgr.com/LabelService/BuyPostage';

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope '.$this->soapEnvelope.'>
                                <soap:Body>
                                    <BuyPostage xmlns="www.envmgr.com/LabelService">
                                        <RecreditRequest>
                                            <RequesterID>'.$this->requesterId.'</RequesterID>
                                            <RequestID>1</RequestID>
                                            <CertifiedIntermediary>
                                               <AccountID>'.$this->accountId.'</AccountID>
                                               <PassPhrase>'.$this->passPhrase.'</PassPhrase>
                                            </CertifiedIntermediary>
                                            <RecreditAmount>'.$amount.'</RecreditAmount>
                                        </RecreditRequest>
                                    </BuyPostage>
                                </soap:Body>
                            </soap:Envelope>';
        $headers = [
            $this->post_header,
            $this->host_header,
            $this->content_type_header,
            "Content-length: ".strlen($xml_post_string),
            "SOAPAction: $soapAction"
        ];

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        // disable warning
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);
        // resource: https://stackoverflow.com/questions/20393140/how-to-read-soap-response-xml-in-php?utm_medium=organic&utm_source=google_rich_qa&utm_campaign=google_rich_qa
        $xml_response = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->BuyPostageResponse;

        // test
        // echo '<h4>Endicia API response:</h4>';
        // echo '<pre>';
        // print_r($xml_response);
        // echo '</pre>';
        // exit();

        // return to controller
        return $xml_response;
    }




    /**
     * returns postage and fees
     *
     * @return Array        The postage amount
     */
    public function calculatePostageRate($length, $width, $height, $shipping_method, $weight, $zip)
    {
        // test
        // echo $length .  '<br>';
        // echo $width . '<br>';
        // echo $height . '<br>';
        // echo $shipping_method . '<br>';
        // echo $weight . '<br>';
        // echo $zip . '<br>';
        // exit();

        // modify if necessary zip code to five digits for Endicia
        $zip5 = $this->zipToFive($zip);

        // convert to Endicia API element nomenclature
        switch ($shipping_method)
        {
            CASE 'Priority':
                $shipping_method = 'Priority';
                break;
            CASE 'First':
                $shipping_method = 'First';
                break;
            default:
                $shipping_method = 'error';
        }

        // test
        // echo $shipping_method; exit();
        // $myArray = ['Class' => $shipping_method, 'Weight' => $weight, 'Zip' => $zip];
        // echo json_encode($myArray);
        // echo "Connected!";
        // exit();

        // soap action
        $soapAction = 'www.envmgr.com/LabelService/CalculatePostageRate';

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope '.$this->soapEnvelope.'>
                                <soap:Body>
                                    <CalculatePostageRate>
                                        <PostageRateRequest ResponseVersion="1">
                                            <RequesterID>'.$this->requesterId.'</RequesterID>
                                            <CertifiedIntermediary>
                                                <AccountID>'.$this->accountId.'</AccountID>
                                                <PassPhrase>'.$this->passPhrase.'</PassPhrase>
                                            </CertifiedIntermediary>
                                            <MailpieceShape>Parcel</MailpieceShape>
                                            <MailClass>'.$shipping_method.'</MailClass>
                                            <WeightOz>'.$weight.'</WeightOz>
                                            <SundayHolidayDelivery>False</SundayHolidayDelivery>
                                            <Extension>9042199057</Extension>
                                            <MailpieceDimensions>
                                                <Length>'.$length.'</Length>
                                                <Width>'.$width.'</Width>
                                                <Height>'.$height.'</Height>
                                            </MailpieceDimensions>
                                            <Machinable>TRUE</Machinable>
                                            <Services DeliveryConfirmation="OFF"
                                                      MailClassOnly="OFF"
                                                      CertifiedMail="OFF"
                                                      COD="OFF"
                                                      ElectronicReturnReceipt="OFF"
                                                      InsuredMail="OFF"
                                                      RegisteredMail="OFF"
                                                      RestrictedDelivery="OFF"
                                                      ReturnReceipt="OFF"
                                                      SignatureConfirmation="OFF"
                                                      SignatureService="OFF"
                                                      HoldForPickup="OFF"
                                                      MerchandiseReturnService="OFF"
                                                      OpenAndDistribute="OFF"
                                                      AdultSignature="OFF"
                                                      AdultSignatureRestrictedDelivery="OFF"
                                                      AMDelivery="OFF" />
                                            <InsuredValue></InsuredValue>
                                            <FromPostalCode>'.$this->FromPostalCode5.'</FromPostalCode>
                                            <ToPostalCode>'.$zip5.'</ToPostalCode>
                                            <ToCountryCode />
                                            <ShipDate />
                                            <ShipTime />
                                            <ResponseOptions PostagePrice="TRUE" />
                                            <DeliveryTimeDays>TRUE</DeliveryTimeDays>
                                            <FromCountryCode />
                                            <EstimatedDeliveryDate>TRUE</EstimatedDeliveryDate>
                                        </PostageRateRequest>
                                    </CalculatePostageRate>
                                </soap:Body>
                            </soap:Envelope>';
        $headers = [
            $this->post_header,
            $this->host_header,
            $this->content_type_header,
            "Content-length: ".strlen($xml_post_string),
            "SOAPAction: $soapAction"
        ];

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        // simplexml_load_string interprets a string of XML into an object
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);

        // resource: https://stackoverflow.com/questions/20393140/how-to-read-soap-response-xml-in-php?utm_medium=organic&utm_source=google_rich_qa&utm_campaign=google_rich_qa
        $xml_response = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children();

        // test
        // echo json_encode($xml_response); exit();
        // $postage = $xml_response->CalculatePostageRateResponse->PostageRateResponse->PostagePrice[0]['TotalAmount'];

        // return to controller (must json_encode before returned to AJAX request)
        return $xml_response;
    }





    /**
     * returns postage label and fees
     *
     * @return Array        The postage amount
     */
    public function getPostageLabel($customer, $length, $width, $height, $shipping_method, $weight, $zip)
    {
        // modify zip to 5 characters per Endicia API requirement (http://developer.endicia.com/docs/v8.9.html#mailpiece-dimensions)
        // modify if necessary zip code to five digits for Endicia
        $zip5 = $this->zipToFive($zip);

        // test
        // echo '<pre>';
        // print_r($customer);
        // echo '</pre>';
        // echo 'Box length: ' . $length . '<br>';
        // echo 'Box width: ' . $width  . '<br>';
        // echo 'Box height: ' . $height . '<br>';
        // echo 'Shipping method: ' . $shipping_method . '<br>';
        // echo 'Weight (oz): ' . $weight . '<br>';
        // echo 'Zip: ' . $zip . '<br>';
        // echo 'Zip5: ' . $zip5 . '<br>';
        // echo 'From postal code: ' . $this->FromPostalCode5 . '<br>';
        // exit();

        // store path for log files
        $outputFileFolder = 'C:\xampp\htdocs\gunlaser.store\public\assets\shipping_labels\usps';  // development
        // $outputFileFolder = '/assets/shipping_labels/usps';  // production
        // $outputFileFolder = '/home/pamska5/public_html/gunlaser.store/public/assets/shipping_labels/usps';  // production

        // soap action
        $soapAction = 'www.envmgr.com/LabelService/GetPostageLabel';

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope '.$this->soapEnvelope.'>
                                <soap:Body>
                                    <GetPostageLabel xmlns="www.envmgr.com/LabelService">
                                        <LabelRequest Test="no" LabelSize="4x6" ImageFormat="PNGMONOCHROME" LabelTemplate="">
                                            <MailpieceShape>Parcel</MailpieceShape>
                                            <MailClass>'.$shipping_method.'</MailClass>
                                            <WeightOz>'.$weight.'</WeightOz>
                                            <Extension />
                                            <RequesterID>'.$this->requesterId.'</RequesterID>
                                            <AccountID>'.$this->accountId.'</AccountID>
                                            <PassPhrase>'.$this->passPhrase.'</PassPhrase>
                                            <CertifiedIntermediary>
                                                <AccountID>'.$this->accountId.'</AccountID>
                                                <PassPhrase>'.$this->passPhrase.'</PassPhrase>
                                            </CertifiedIntermediary>
                                            <AutomationRate>FALSE</AutomationRate>
                                            <Machinable>TRUE</Machinable>
                                            <MailpieceDimensions>
                                                <Length>'.$length.'</Length>
                                                <Width>'.$width.'</Width>
                                                <Height>'.$height.'</Height>
                                            </MailpieceDimensions>
                                            <IncludePostage>TRUE</IncludePostage>
                                            <ReplyPostage>FALSE</ReplyPostage>
                                            <ShowReturnAddress>TRUE</ShowReturnAddress>
                                            <Stealth>FALSE</Stealth>
                                            <ValidateAddress>TRUE</ValidateAddress>
                                            <Services DeliveryConfirmation="OFF"
                                                MailClassOnly="OFF"
                                                CertifiedMail="OFF"
                                                COD="OFF"
                                                ElectronicReturnReceipt="OFF"
                                                InsuredMail="OFF"
                                                RegisteredMail="OFF"
                                                RestrictedDelivery="OFF"
                                                ReturnReceipt="OFF"
                                                SignatureConfirmation="OFF"
                                                SignatureService="OFF"
                                                HoldForPickup="OFF"
                                                MerchandiseReturnService="OFF"
                                                OpenAndDistribute="OFF"
                                                AdultSignature="OFF"
                                                AdultSignatureRestrictedDelivery="OFF"
                                                AMDelivery="OFF" />
                                            <InsuredValue />
                                            <PartnerCustomerID>xxxxx</PartnerCustomerID>
                                            <PartnerTransactionID>xxxx</PartnerTransactionID>
                                            <ResponseOptions PostagePrice="true" />
                                            <NoDate>FALSE</NoDate>
                                            <FromName />
                                            <FromCompany>ARMALASER</FromCompany>
                                            <ReturnAddress1>5200 NW 43rd Street</ReturnAddress1>
                                            <FromCity>Gainesville</FromCity>
                                            <FromState>FL</FromState>
                                            <FromPostalCode>'.$this->FromPostalCode5.'</FromPostalCode>
                                            <FromCountry />
                                            <FromPhone>8006805020</FromPhone>
                                            <FromEMail>customercare@armalaser.com</FromEMail>
                                            <ToName>'.$customer["name"].'</ToName>
                                            <ToCompany />
                                            <ToAddress1>'.$customer["address"].'</ToAddress1>
                                            <ToAddress2 />
                                            <ToCity>'.$customer["city"].'</ToCity>
                                            <ToState>'.$customer["state"].'</ToState>
                                            <ToPostalCode>'.$zip5.'</ToPostalCode>
                                            <ToZIP4 />
                                            <ToDeliveryPoint />
                                            <ToCountry />
                                            <ToPhone>'.$customer["phone"].'</ToPhone>
                                            <ToEMail>'.$customer["email"].'</ToEMail>
                                        </LabelRequest>
                                    </GetPostageLabel>
                                </soap:Body>
                            </soap:Envelope>';
        $headers = [
            $this->post_header,
            $this->host_header,
            $this->content_type_header,
            "Content-length: ".strlen($xml_post_string),
            "SOAPAction: $soapAction"
        ];

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        // test
        // echo '<h4>API getPostageLabel response</h4>';
        // echo $response;
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // exit();

        // simplexml_load_string interprets a string of XML into an object
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);
        // resource: https://stackoverflow.com/questions/20393140/how-to-read-soap-response-xml-in-php?utm_medium=organic&utm_source=google_rich_qa&utm_campaign=google_rich_qa
        $xml_response = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children();

        // handle error
        if ($xml_response->GetPostageLabelResponse->LabelRequestResponse->Status != 0)
        {
            $error_msg = $xml_response->GetPostageLabelResponse->LabelRequestResponse->ErrorMessage;
            echo "Error: " . $error_msg;
            // send email to webmaster; log in error log
            exit();
        }

        // test
        // echo "<h4>Full Endicia API response for getPostageLabel():</h4>";
        // echo '<pre>';
        // highlight_string(print_r($xml_response, TRUE));
        // echo '</pre>';
        // exit();

        // store base64 string, tracking number and current date & time
        $base64_string = (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->Base64LabelImage;
        $trackingNumber = (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->TrackingNumber;
        $transaction_date_time = (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->TransactionDateTime;

        // save request and response to file & store in ups/log_request folder
        $fw = fopen ( $outputFileFolder.'\\'.$trackingNumber.'.xml', 'w' );  // development
        // $fw = fopen ( $outputFileFolder.'/'.$transaction_date_time.'-'.$trackingNumber.'.xml', 'w' );  // production
        fwrite ( $fw, "Request: \n" . $xml_post_string . "\n" );
        fwrite ( $fw, "Response: \n" . $response . "\n" );
        fclose ( $fw );

        // create array of key fields from API response (turned into PHP object w/ simplexml_load_string())
        // $results = [
        //     'Status' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->Status,
        //     'Base64LabelImage' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->Base64LabelImage,
        //     'PIC' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->PIC,
        //     'TrackingNumber' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->TrackingNumber,
        //     'FinalPostage' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->FinalPostage,
        //     'TransactionID' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->TransactionID,
        //     'TransactionDateTime' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->TransactionDateTime,
        //     'PostmarkDate' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->PostmarkDate,
        //     'PostageBalance' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->PostageBalance,
        //     'PostageBalance' => (string) $xml_response->GetPostageLabelResponse->LabelRequestResponse->PostagePrice->Postage[0]['TotalAmount']
        // ];

        // Assign target directory based on server
        if ($_SERVER['SERVER_NAME'] != 'localhost')
        {
            // path for live server @IMH
            $target_dir = Config::UPLOAD_PATH . '/assets/shipping_labels/usps/';
        }
        else
        {
            // path for local machine
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/shipping_labels/usps/';
        }

        //  path to store label images (development and production)
        $output_file = $target_dir . $trackingNumber.'.jpg';

        // convert base64 string to jpg image & store in designated folder
        $label = $this->base64_to_jpeg($base64_string, $output_file);

        // return to controller
        return $xml_response;
    }




    public function voidLabel($user_id, $timestamp, $transactionId, $trackingNumber)
    {
        // echo "Connected to voidLabel() in Endicia Model.";

        // soap action
        $soapAction = 'www.envmgr.com/LabelService/GetRefund';

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope '.$this->soapEnvelope.'>
                                <soap:Body>
                                    <GetRefund xmlns="www.envmgr.com/LabelService">
                                        <RefundRequest>
                                            <RequesterID>'.$this->requesterId.'</RequesterID>
                                            <RequestID>'.$user_id.'_'.$timestamp.'</RequestID>
                                            <CertifiedIntermediary>
                                                <AccountID>'.$this->accountId.'</AccountID>
                                                <PassPhrase>'.$this->passPhrase.'</PassPhrase>
                                            </CertifiedIntermediary>
                                            <PicNumbers>
                                                <PicNumber>'.$trackingNumber.'</PicNumber>
                                            </PicNumbers>
                                            <TransactionIds>
                                                <TransactionId>'.$transactionId.'</TransactionId>
                                            </TransactionIds>
                                        </RefundRequest>
                                    </GetRefund>
                                </soap:Body>
                            </soap:Envelope>';
        $headers = [
            $this->post_header,
            $this->host_header,
            $this->content_type_header,
            "Content-length: ".strlen($xml_post_string),
            "SOAPAction: $soapAction"
        ];

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        // disable warning
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);
        // resource: https://stackoverflow.com/questions/20393140/how-to-read-soap-response-xml-in-php?utm_medium=organic&utm_source=google_rich_qa&utm_campaign=google_rich_qa
        $xml_response = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children();

        // test
        // echo '<pre>';
        // highlight_string(print_r($xml_response, TRUE));
        // echo '</pre>';
        // exit();

        // store refund response in variable
        $refundStatus = $xml_response->GetRefundResponse->RefundResponse->Refund[0]->RefundStatus;

        // handle failure
        if ($refundStatus != 'Approved')
        {
            echo "Error. Refund was denied.";
            // email webmaster
            exit();
        }
        // success
        else
        {
            // return to controller
            return $xml_response;
        }
    }




    /**
     * returns postage and fees for multiple mail classes
     *
     * @return Array        The postage rates
     */
    public function calculatePostageRates()
    {
        // soap action
        $soapAction = 'www.envmgr.com/LabelService/CalculatePostageRates';

        // xml post structure
        $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                            <soap:Envelope '.$this->soapEnvelope.'>
                                <soap:Body>
                                    <CalculatePostageRates>
                                        <PostageRatesRequest ResponseVersion="1">
                                            <RequesterID>'.$this->requesterId.'</RequesterID>
                                            <CertifiedIntermediary>
                                                <AccountID>'.$this->accountId.'</AccountID>
                                                <PassPhrase>'.$this->passPhrase.'</PassPhrase>
                                            </CertifiedIntermediary>
                                            <MailClass>Domestic</MailClass>
                                            <WeightOz>24</WeightOz>
                                            <MailpieceShape>Parcel</MailpieceShape>
                                            <MailpieceDimensions>
                                                <Length>6</Length>
                                                <Width>4</Width>
                                                <Height>4</Height>
                                            </MailpieceDimensions>
                                            <Machinable>true</Machinable>
                                            <Services DeliveryConfirmation="ON"
                                                      MailClassOnly="OFF"
                                                      CertifiedMail="OFF"
                                                      COD="OFF"
                                                      ElectronicReturnReceipt="OFF"
                                                      InsuredMail="OFF"
                                                      RegisteredMail="OFF"
                                                      RestrictedDelivery="OFF"
                                                      ReturnReceipt="OFF"
                                                      SignatureConfirmation="OFF"
                                                      SignatureService="OFF"
                                                      HoldForPickup="OFF"
                                                      MerchandiseReturnService="OFF"
                                                      OpenAndDistribute="OFF"
                                                      AdultSignature="OFF"
                                                      AdultSignatureRestrictedDelivery="OFF"
                                                      AMDelivery="OFF" />
                                            <CODAmount>0</CODAmount>
                                            <InsuredValue>200</InsuredValue>
                                            <RegisteredMailValue>0</RegisteredMailValue>
                                            <FromPostalCode>32081</FromPostalCode>
                                            <ToPostalCode>44483</ToPostalCode>
                                            <ToCountry></ToCountry>
                                            <ToCountryCode>US</ToCountryCode>
                                            <DateAdvance>0</DateAdvance>
                                            <Extension>9042199057</Extension>
                                            <DeliveryTimeDays>TRUE</DeliveryTimeDays>
                                            <EstimatedDeliveryDate>TRUE</EstimatedDeliveryDate>
                                            <ContentsType>Merchandise</ContentsType>
                                         </PostageRatesRequest>
                                    </CalculatePostageRates>
                                </soap:Body>
                            </soap:Envelope>';
        $headers = [
            $this->post_header,
            $this->host_header,
            $this->content_type_header,
            "Content-length: ".strlen($xml_post_string),
            "SOAPAction: $soapAction"
        ];

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);

        // disable warning
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOWARNING);
        // resource: https://stackoverflow.com/questions/20393140/how-to-read-soap-response-xml-in-php?utm_medium=organic&utm_source=google_rich_qa&utm_campaign=google_rich_qa
        $xml_response = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children();

        // test
        // echo '<pre>';
        // highlight_string(print_r($xml_response, TRUE));
        // echo '</pre>';
        // exit();

        // create array of key fields from API response (turned into PHP object w/ simplexml_load_string())
        $rates = [
            'MailClass' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[0]->MailClass,
            'MailService' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[0]->Postage->MailService,
            'PriorityAmt' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[0]['TotalAmount'],
            'Insurance' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[0]->Fees[0]['TotalAmount'],
            'DeliveryTimeDays' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[0]->DeliveryTimeDays,
            'EstimatedDeiverylDate' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[0]->EstimatedDeliveryDate,

            'MailClass2' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[1]->MailClass,
            'MailService2' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[1]->Postage->MailService,
            'PriorityExpressAmt' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[1]['TotalAmount'],
            'Insurance2' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[1]->Fees[0]['TotalAmount'],
            'DeliveryTimeDays2' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[1]->DeliveryTimeDays,
            'EstimatedDeiverylDate2' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[1]->EstimatedDeliveryDate,

            'MailClass3' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[2]->MailClass,
            'MailService3' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[2]->Postage->MailService,
            'LibraryMailAmt' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[2]['TotalAmount'],
            'Insurance3' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[2]->Fees[0]['TotalAmount'],

            'MailClass4' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[3]->MailClass,
            'MailService4' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[3]->Postage->MailService,
            'MediaMailAmt' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[3]['TotalAmount'],
            'Insurance4' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[3]->Fees[0]['TotalAmount'],

            'MailClass5' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[4]->MailClass,
            'MailService5' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[4]->Postage->MailService,
            'ParcelSelectAmt' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[4]['TotalAmount'],
            'Insurance5' => (string) $xml_response->CalculatePostageRatesResponse->PostageRatesResponse->PostagePrice[4]->Fees[0]['TotalAmount'],
        ];

        // test
        // echo '<pre>';
        // print_r($rates);
        // echo '</pre>';
        // exit();

        // return to controller
        return $rates;
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
            echo "Base64 string does not exist.";
            exit();
        }
    }



    public function zipToFive($zip)
    {
        // modify zip to 5 characters per Endicia API requirement (http://developer.endicia.com/docs/v8.9.html#mailpiece-dimensions)
        if (strlen($zip) > 5)
        {
            $zip = trim(substr($zip, 0, 5));
        }
        else
        {
            $zip = $zip;
        }

        return $zip;
    }



}
