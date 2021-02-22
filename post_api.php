<?php



function redirect($url)
{
    echo <<<HTML
    <script> window.top.location.href = "{$url}";</script>
    HTML;

    echo <<<HTML
  <script> window.opener.location.href="{$url}";
        self.close();</script>
HTML;
}
function redirect_to_success($id)
{
    redirect("https://carplus.co.uk/quote/success/?acref=" . $id);
    exit();
}

function redirect_to_declined($id)
{
    redirect("http://carplus.co.uk/quote/declined?" . $id);
    exit();
}

function redirect_to_nothing()
{
    redirect("http://carplus.co.uk/quote");
    exit();
}

//Starting
function getValue($key){
    return $_POST[$key];
}
function test_data()
{	
	$json_string= json_encode(array(
        "VehicleType" =>"Car",//
        "AmountToBorrow"=>getValue("AmountToBorrow"),
		"LoanTerm"=>60,	
		"Products"=>array(
            "Name"=>null,
            "VAT"=>null,
            "NetValue"=>null,
            "CategoryId"=>null,
            "PaymentType"=>null,
            ),
        "FinanceDetails" => array(
            "Deposit" => null,
            "PartExchangeValue" => null,
            "FDA" => null,
            "EstimatedAnnualMileage" => null,
            "Settlement" => null,
            "EnquiryType" => null,
            "FinanceTypeId" => null,
        ),
        "BankDetails" => array(
            "SortCode" => null,
            "AccountNumber" => null,
            "AccountName" => null,
            "TimeAtBankYears" => null,
            "TimeAtBankMonths" => null,
            "BranchName" => null,
            "BankName" => null,
            "BankAddress" => null,
        ),
        "Vehicles" => array(
            "Registration" => null
		),
        "Applicants" => array(array(//
			"DateOfBirth" => getValue("DateOfBirth"),//
     		"DrivingLicenseType" => getValue("DrivingLicenseType"),//
     		"Email" => getValue("Email"),//
            "Registration" => getValue("Registration"),
            "Forename" => getValue("Forename"),//
            "Surname" => getValue("Surname"),//
            "Mobile" =>getValue("Mobile"),//
            "Title" => getValue("Title"),//
			"Addresses"=>getValue("addresses"),
			"MaritalStatus" => "Single",//
			"ValidUkPassport" => true,//
		)),
		"Employments"=>getValue("employments"),

    ));
    die($json_string);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.autoconvert.co.uk/application/submit',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $json_string,
        CURLOPT_HTTPHEADER => array(
            'X-ApiKey: 19ff541e-b45e-4ac5-8cda-dc457868211b',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response, true);
}

function redirect_main()
{
	//testing
    // $jsonArrayResponse = get_data();
    $jsonArrayResponse = test_data();
    die(print_r($jsonArrayResponse));
    if($jsonArrayResponse['Accepted']){
    	print_r('Success');
    	redirect_to_success($jsonArrayResponse['Reference']);
    }else{
    	print_r('Failure');
    	redirect_to_declined($jsonArrayResponse['Reference']);
    }
}

redirect_main();

//ending
?>
