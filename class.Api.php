<?php

/**
 * Test class for prospective staff to complete
 *
 * Class ApiTest
 */
class ApiTest{

    /**
     * The URL to use for the requests - the URL documentation for searching patients can be found here:
     * https://api.nookal.com/dev/reference/patient
     *
     * required params:
     * last_name - required [can be first part of a name (i.e. Sm for Smith)]
     * first_name - optional [can be first part of a name (i.e. Fr for Fred)]
     *
     * optional params:
     * page - must be > 0 (Default: 1)
     * page_length - must be > 0, must be <= 200 (Default: 100)
     *
     * @var string
     */
    private $url = 'https://api.nookal.com/production/v2/searchPatients';
    private $apiKey = 'abcdefghi-1234-jklm-5678-90nopqrst123';

    /**
     * Here is where the codebase goes
     *
     * The task is as follows:
     *
     * Retrieve all the customers from the database who have last names starting with 'Sm' and are born on or after 01/01/1981
     *
     * The list that is produced from this request should be outputted as a CSV in the folder that this file exists
     *
     */
    public function run(){

        //TODO: Code goes here
        
        // SET CURL REQUEST PARAMS
        $RequestData = [];
        $RequestData['last_name'] = 'Sm';
        $RequestData['page'] = 1;
        $RequestData['page_length'] = 200;
        
        // CREATE EMPTY ARRAY FOR ALL PATIENTS
        $AllPatients = [];
        
        // CREATE CSV FILE
        $CSVFile = fopen('test.csv', 'w');
        
        // LOOP THROUGH EACH PAGE TO GATHER ALL CUSTOMERS
        for($i = 0; $i < $RequestData['page']; $i++) {
		
        	// RUN CURL REQUEST
        	$APIResult = $this -> request($RequestData);
        	
        	// LOOP THROUGH EACH PATIENT ARRAY TO BUILD ARRAY CONTAINING ALL PATIENTS
        	foreach($APIResult["data"]["results"]["patients"] as $Key => $EachPatient) {
        		// CONVERT DATE OF BIRTH TO SECONDS
        		$DOBSeconds = strtotime($EachPatient["DOB"]);
        		// CHECK IF DATE OF BIRTH IS GREATER THAN OR EQUAL TO 01/01/1981
        		if($DOBSeconds >= 347155200) {
        			
        			// GET ARRAY KEYS IF FIRST LOOP TO SET AS CSV HEADINGS
        			if($Key === 0) {
					fputcsv($CSVFile, array_keys($EachPatient));
				}
        		
        			// PUSH EACH PATIENT ARRAY INTO END RESULT
        			array_push($AllPatients, $EachPatient);
        			// PUSH EACH PATIENT INTO CSV
        			fputcsv($CSVFile, $EachPatient);
        		}
        	}
        	
        	// CHECK IF LOOPED THROUGH EACH PAGE
        	if(count($APIResult["data"]["results"]["patients"]) == $RequestData['page_length']) {
			// INCREMENT PAGE NUMBER
			$RequestData['page']++;
		}
		
        }
        
        // CLOSE CSV FILE
        fclose($CSVFile);
        
        // RETURN ARRAY CONTAINING ALL PATIENTS
        //return $AllPatients;

    }

    /**
     * Builds the request and sends it to the Nookal API Servers
     *
     * @param array $data - the array of data to be sent in the API request
     * @return mixed
     * @throws Exception
     */
    private function request(array $data){

        //If the data isn't an array then throw an error
        if(!is_array($data)){
            throw new Exception('Required data is missing');
        }

        // Add the api key to the request
        $data['api_key'] = $this->apiKey;

        // Stringify the array in a HTML-friendly string
        $data   = http_build_query($data);

        // Initialise the cURL element
        $ch	    = curl_init();

        // cURL settings array
        curl_setopt_array($ch, array(
            CURLOPT_URL 				=> $this->url,
            CURLOPT_CONNECTTIMEOUT      => 30,
            CURLOPT_TIMEOUT				=> 30,
            CURLOPT_HTTPHEADER 			=> array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8', 'Content-Length: ' . strlen($data)),
            CURLOPT_CUSTOMREQUEST		=> "POST",
            CURLOPT_POSTFIELDS          => $data,
            CURLOPT_SSL_VERIFYPEER      => false,
            CURLOPT_RETURNTRANSFER		=> true
        ));

        // Execute the cURL request
        $response   = curl_exec($ch);

        if (curl_errno($ch)) {

            // Throw the cURL error and the cURL response
            print_r($response);
            echo "\n";
            exit('curl error : ' . curl_error($ch));

        }else{
            $r = json_decode($response, true);
            // If this statement is true, there was an error pulling data from the server
            if(empty($r) && !empty($response)){
                echo json_last_error_msg() . "\n";
                print_r($response);die();
            }
            // If this statement is true then there was either a server error or a major error in the request that
            // throws an error
            if(empty($r) && empty($response)){
                echo curl_error($ch)."\n";die();
            }
            unset($response);
        }

        curl_close($ch);

        return $r;

    }
}
