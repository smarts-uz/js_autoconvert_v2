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
function getAddressDetails($addresses){
    $data=[];
    foreach($addresses as $address){
        //"Homeowner|2H|England|Richmond Crescent|E49RT|2|1", "Homeowner|1J|England|Richmond Crescent|E49RT|0|2"
        if($address){
            $detail=explode("|", $address);
            $data[]=array(
                "Building"=>$detail[0],
                "BuildingNumber"=>$detail[1],
                "SubBuildingName"=>null,
                "Postcode"=>$detail[4],
                "Street"=>$detail[3],
                "Town"=>null,
                "County"=>$detail[2],
                "TimeAtAddressYears"=>$detail[5],
                "TimeAtAddressMonths"=>$detail[6],
                "ResidentialStatus"=>$detail[0],
            );
        }
    }
    return $data;
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
			"DateOfBirth" => getValue("DobYear")."-".getValue("DobMonth")."-".getValue("DobDay"),//
     		"DrivingLicenseType" => getValue("DrivingLicenseType"),//
     		"Email" => getValue("email"),//
            "Registration" => getValue("Registration"),
            "Forename" => getValue("fname"),//
            "Surname" => getValue("lname"),//
            "Mobile" =>getValue("mobile"),//
            "Title" => getValue("Title"),//
			"Addresses"=>getAddressDetails(getValue("Addresses")),

            // {  
            //     "Building":"The Granary",
            //     "BuildingNumber":"2",
            //     "SubBuildingName":"Flat 1",
            //     "Postcode":"FA11 0UT",
            //     "Street":"Street",
            //     "Town":"Megaton",
            //     "County":"Maryland",
            //     "TimeAtAddressYears":"13",
            //     "TimeAtAddressMonths":"10",
            //     "ResidentialStatus":"Living With Family"
            //  },
			"MaritalStatus" => "Single",//
			"ValidUkPassport" => true,//
		)),
		"Employments"=> array(array(
			"Employer"=>getValue("Employer"),
			"JobTitle"=>getValue("JobTitle"),
			"EmploymentStatus"=>getValue("EmploymentType"),	
            "NetMonthlyIncome"=>getValue("NetMonthlyIncome"),		
			"TimeAtEmployerMonths"=>getValue("currentTimeAtAddressMonths"),
			"TimeAtEmployerYears"=>getValue("currentTimeAtAddressYears")
		)),
        // /{  
        //     "Company":"Vault-tec",
        //     "Building":"1",
        //     "Street":"Lane",
        //     "Town":"town",
        //     "Postcode":"FA11 0UT",
        //     "JobTitle":"Job",
        //     "EmploymentStatus":"Full-Time Employment",
        //     "TimeAtEmployerYears":"4",
        //     "TimeAtEmployerMonths":"3",
        //     "NetMonthlyIncome":"2000",
        //     "ZeroHourContract":"False",
        //     "PhoneNumber":"07777777777"
        // }
    ));
    //die($json_string);
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
    //die(print_r($jsonArrayResponse));
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