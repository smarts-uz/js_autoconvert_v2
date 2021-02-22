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

function redirect_back_with_message($message)
{
    redirect("https://stagingcplus.wpengine.com/api/?message=" . $message);
    exit();
}


function getValueArray($key){
    return (array)$_POST[$key];
}
$AmountToBorrow=0;
function check_AmountToBorrow(){
	global $AmountToBorrow;
	$AmountToBorrow=getValue("AmountToBorrow");
	if(!($AmountToBorrow>3 && $AmountToBorrow<50)){
		redirect_back_with_message("Amount should be between 3 to 50 pound");
	}
	redirect_back_with_message("success");	
}
$Mobile=null;
function check_Mobile(){
	global $Mobile;
	$Mobile=getValue("Mobile");
	if(strlen($Mobile)==11){
		redirect_back_with_message("Not valid mobile");
	}
	redirect_back_with_message("success");
}
function get_address($postcode){
	$curl = curl_init();
	curl_setopt_array($curl, array(
	CURLOPT_URL => 'http://api.postcodes.io/postcodes/'.$postcode,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'GET',
	CURLOPT_HTTPHEADER => array(
		'Cookie: __cfduid=d99ba8011a460cf01ca1a37393ae262481612791653'
	),
	));

	$data = curl_exec($curl);

	curl_close($curl);
	$data=json_decode($data);
	if(isset($data->result)){
		$arr['County']=$data->result->country;
		$arr['Postcode']=$data->result->postcode;
		$arr['Street']=$data->result->parish;
		$arr['Building']=null;
		return json_encode($arr);
	}else{
		return null;
	}
}

function merge_time($year, $month){
	return $year+($month/12);
}
$adresses=array();
$address_years=0;
function recursive_get_address(){
	global $adresses;
	global $address_years;
	$Postcode=getValue("Postcode");
	$address=get_address($Postcode);
	$TimeAtAddressYears=getValue("TimeAtAddressYears");
	$TimeAtAddressMonths=getValue("TimeAtAddressMonths");
	if($Postcode!=null && $address){
		$adresses[]=array(
			$address,				
			"TimeAtAddressMonths"=>$TimeAtAddressMonths,//
			"TimeAtAddressYears"=>$TimeAtAddressYears,//
			"ResidentialStatus"=>"Tenant - Private",//
		);
		//testing
		// $address_years=$address_years+merge_time($TimeAtAddressYears, $TimeAtAddressMonths);
		// if($address_years<3){
		// 	return redirect_back_with_message("get address again");
		// }else{
		// 	return redirect_back_with_message("success");
		// }
	}
	//return redirect_back_with_message("Not valid data");
}
$employments=array();
function check_employment_status(){
	global $employments;
	$EmploymentStatus=getValue("EmploymentStatus");
	if( $EmploymentStatus =="Full-Time Employment"){
		return false;
	}else{
		$employments = array(array(
			"EmploymentStatus"=>getValue("EmploymentStatus"),
			"TimeAtEmployerMonths"=>1,
			"TimeAtEmployerYears"=>2
		));
		return true;
	}
}
function get_employment(){	
	if(check_employment_status()){
		return true;
	}else{
		return false;
	}	
}

function get_employment_form(){
	global $employments;
	if( getValue("Employer")  && getValue("JobTitle")){
		$employments = array(array(
			"Employer"=>getValue("Employer"),
			"JobTitle"=>getValue("JobTitle"),
			"EmploymentStatus"=>getValue("EmploymentStatus"),			
			"TimeAtEmployerMonths"=>1,
			"TimeAtEmployerYears"=>2
		));
		//testing
		//redirect_back_with_message("success");
	}
	//testing
	//redirect_back_with_message("Please, fill all fields");
}

function get_data()
{	
	global $AmountToBorrow;
	global $Mobile;
	global $employments;
	global $adresses;

	$json_string= json_encode(array(
        "VehicleType" =>"Car",//
        "AmountToBorrow"=>$AmountToBorrow,
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
        "Applicants" => array(//
			"DateOfBirth" => getValue("DateOfBirth"),//
     		"DrivingLicenseType" => getValue("DrivingLicenseType"),//
     		"Email" => getValue("Email"),//
            "Registration" => getValue("Registration"),
            "Forename" => getValue("Forename"),//
            "Surname" => getValue("Surname"),//
            "Mobile" => $Mobile,//
            "Title" => getValue("Title"),//
			"Addresses"=>$adresses,
			"MaritalStatus" => "Single",//
			"ValidUkPassport" => true,//
		),
		"Employments"=>$employments,

    ));

	// die($json_string);
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

//Validations
//testing
// check_AmountToBorrow();
// check_Mobile();
//recursive_get_address();
// if(!get_employment()){
// 	get_employment_form();
// }
//API Request
// echo var_dump();
redirect_main();

//ending
?>
