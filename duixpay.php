<?php
/**
 * WHMCS Sample Payment Gateway Module
 *
 * Payment Gateway modules allow you to integrate payment solutions with the
 * WHMCS platform.
 *
 * This sample file demonstrates how a payment gateway module for WHMCS should
 * be structured and all supported functionality it can contain.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "gatewaymodule" and therefore all functions
 * begin "duixpay_".
 *
 * If your module or third party API does not support a given function, you
 * should not define that function within your module. Only the _config
 * function is required.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function duixpay_MetaData()
{
    return array(
        'DisplayName' => 'DuixPay',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function duixpay_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'DuixPay',
        ),
        // a text field type allows for single line text input
        'public_key' => array(
            'FriendlyName' => 'Votre clé API publique ',
            'Type' => 'text',
            'Size' => '50',
            'Default' => 'duix****',
            'Description' => 'Enter your Public Key here',
        ),
        // a password field type allows for masked text input
        'secret_key' => array(
            'FriendlyName' => 'Votre clé API secret',
            'Type' => 'password',
            'Size' => '50',
            'Default' => 'duix****',
            'Description' => 'Votre clé API secret',
        ),
        // the yesno field type displays a single checkbox option
        'testMode' => array(
            'FriendlyName' => 'Test Mode',
            'Type' => 'yesno',
            'Description' => 'Tick to enable test mode',
        ),
        'site_logo' => array(
            'FriendlyName' => 'Logo de votre site',
            'Type' => 'text',
            'Description' => 'Lien du logo de votre site web',
        )
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function duixpay_link($params)
{
    // Gateway Configuration Parameters
    $public_key = $params['public_key'];
    $secret_key = $params['secret_key'];
    $site_logo = $params['site_logo'];
    $testMode = $params['testMode'];
    

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
	
	$amount = $amount;
	 
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];
	
    $ipn = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';


	if($testMode){
		$url = 'https://duixpay.co/sandbox/payment/initiate';
	}else{
		$url = 'https://duixpay.co/payment/initiate';
	}


    $postfields = array();
	$postfields['amount'] = $amount;
   
	$postfields['currency'] = $currencyCode;
	$postfields['details'] = $description;
	$postfields['customer_email'] = $email;	  
	  
	$postfields['public_key'] = $public_key;
	$postfields['site_logo'] = $site_logo;
	$postfields['customer_name'] = $firstname . ' ' . $lastname;	  
    $postfields['identifier'] = $invoiceId;
    
   
	$postfields['ipn_url'] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';
    
	$postfields['accepturl'] = $params['returnurl'];
	$postfields['cancel_url'] = $params['returnurl'];
	$postfields['success_url'] = $params['returnurl'];


    $htmlOutput = '
    <form id="duixpayForm" method="post" action="' . $url . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
    }
    $htmlOutput .= '<input type="image" name="ap_image" src="https://congocloud.net/client/assets/img/visa.png"/>';
    $htmlOutput .= '</form>';

    // JavaScript pour la redirection
    $htmlOutput .= '
    <script type="text/javascript">
        document.getElementById("duixpayForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Empêcher la soumission du formulaire

            // Effectuer la soumission du formulaire
            fetch("' . $url . '", {
                method: "POST",
                body: new FormData(this)
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success === "ok" && data.url) {
                    // Créer un formulaire caché pour rediriger l\'utilisateur
                    var redirectForm = document.createElement("form");
                    redirectForm.method = "post";
                    redirectForm.action = data.url;

                    // Soumettre le formulaire de redirection
                    document.body.appendChild(redirectForm);
                    redirectForm.submit();
                } else {
                    alert("Erreur lors de l\'initialisation du paiement.");
                }
            })
            .catch(function(error) {
                console.error("Erreur lors de la soumission du formulaire : ", error);
                alert("Une erreur est survenue.");
            });
        });
    </script>';
    return $htmlOutput;

}

/**
 * Refund transaction.
 *
 * Called when a refund is requested for a previously successful transaction.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/refunds/
 *
 * @return array Transaction response status
 */
function duixpay_refund($params)
{
    // Gateway Configuration Parameters
    $public_key = $params['public_key'];
    $secret_key = $params['secret_key'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];

    // Transaction Parameters
    $transactionIdToRefund = $params['transid'];
    $refundAmount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to initiate refund and interpret result

    return array(
        // 'success' if successful, otherwise 'declined', 'error' for failure
        'status' => 'success',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $responseData,
        // Unique Transaction ID for the refund transaction
        'transid' => $refundTransactionId,
        // Optional fee amount for the fee value refunded
        'fees' => $feeAmount,
    );
}

/**
 * Cancel subscription.
 *
 * If the payment gateway creates subscriptions and stores the subscription
 * ID in tblhosting.subscriptionid, this function is called upon cancellation
 * or request by an admin user.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/subscription-management/
 *
 * @return array Transaction response status
 */
function duixpay_cancelSubscription($params)
{
    // Gateway Configuration Parameters
    $public_key = $params['public_key'];
    $secret_key = $params['secret_key'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];

    // Subscription Parameters
    $subscriptionIdToCancel = $params['subscriptionID'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    // perform API call to cancel subscription and interpret result

    return array(
        // 'success' if successful, any other value for failure
        'status' => 'success',
        // Data to be recorded in the gateway log - can be a string or array
        'rawdata' => $responseData,
    );
}
