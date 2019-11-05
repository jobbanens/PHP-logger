<?php 

	/**
	 *
	 * Copyright 2019 shizusa
	 *
	 * A simple PHP script that logs the 
	 * IP, user agent, query, estimation of location and time of visiting
	 * in a .log file.
	 *
	 * This script should be placed inside every page you want to log (be sure the page has the .php extension. Ex: index.php)
	 *
	 * PHP version 7.2
	 *
	 * Make sure you deny/disallow access to files with the .log extension via the Apache or Nginx config. Otherwise people can access it.
	 * This is not an entirely safe way of storing data. This script should NOT be used for storing sensitive data!
	 *
	 */


	// Set the directory for the log files.
	// You will have to create the directory first (and give it the correct read and write permission)!
	$logDirectoryName = "logs";

  
    // Set timezone for correct timestamping in the log file (https://www.php.net/manual/en/timezones.php)
    date_default_timezone_set('Europe/Amsterdam');

    // Get the current time in the timezone set above (https://www.php.net/manual/en/function.date.php)
    $time = date("m/j G:i:s");

    // Get headers from the visitor
    // If value is null/empty, replace with a dash (this is handy for the log file layout)
    $ip = getenv('REMOTE_ADDR') ?? "-" ;
    $userAgent = getenv('HTTP_USER_AGENT') ?? "-" ;
    $referrer = getenv('HTTP_REFERER') ?? "-" ;
    $query = getenv('QUERY_STRING') ?? "-" ;

    // Call the ipinfo.io API to receive the location of the ip 
    $details = json_decode(file_get_contents("http://extreme-ip-lookup.com/json/$ip"));
    $country = $details->country;
    $region = $details->region;
    $city = $details->city; 

    // Another way of checking if the value is empty and if so setting it to a dash
    //if (empty($referrer)) { $referrer = "-"; }
    //if (empty($query)) { $query = "-"; }


    // The string to log (and add newline)
    // You can also identify on which page the visitor currently is, you can change this for every page. Ex: Index
    $msg = "[" . $time . "] " . $ip . " [" . $country . " " . $region . " " . $city . "] [Index] " . $referrer . " | " . $query  . "\n";

    // Here you can set IPs to blacklist from being logged. Ex: This is so your own IP doesn't end up in the log file.
    // The SHA512 hash of the salt+IP is hardcoded here. 

    // Replace with the SHA512 hash of the salt+IP you want to blacklist (http://www.convertstring.com/Hash/SHA512).
    // No space between the salt (variable $salt down below) and the ip you want to blacklist.
  	// Ex: salt123.456.789.012 ----> 6059BC16E74EE821B654BF438DC07845CBD57BD6BB2ACEEDAB402770782838AF55FB3306819103039F3E6267CD63D075EAAA69A9AC23E143325F06C89F45B826
    $blockip1 = "6059BC16E74EE821B654BF438DC07845CBD57BD6BB2ACEEDAB402770782838AF55FB3306819103039F3E6267CD63D075EAAA69A9AC23E143325F06C89F45B826"; 
    $blockip2 = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
    $salt = "salt";
    // Generate hash of the visitor's IP
    $hash = strtoupper(hash("sha512", $salt . $_SERVER["REMOTE_ADDR"] . $salt));

    // If the hash of the visitor's IP = the hash of the blacklisted IP or if the visitor is a form of webcrawler then the msg to log is emptied.
    if ($hash === $blockip1 || $hash === $blockip2 || 
    	(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT']))
		)
    {
        $msg = ""; 
    }

    // Call the log function 
    writeToLogFile($msg, $logDirectoryName);

    function writeToLogFile($msg, $logDirectoryName) {
        // Set name of log file based on the date
        $logFile = $logDirectoryName .'/'. date('Y') . '-' . date('m') . '-' . date('d') . '.log';

        // Check if the log file is a directory 
        //if(is_dir($logFile)){
        //    echo('Error: Log File is a DIRECTORY!');
        //}

        // Open the data stream for the log file
        $f = fopen($logFile, 'a') or die("Unable to open file!"); 
        // Write the string to the log file
        fwrite($f, $msg);
        // Close the data stream for the log file
        fclose($f);
    } 
?>      

