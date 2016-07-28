<?php

	include "ApiWrapper.php";
	
	$apiWrapper = new ApiWrapper();
	
	echo $apiWrapper->request('/') . "\n";
	// example using parameters and non GET method
	echo $apiWrapper->request('/user/check', array('method' => 'POST', 'params' => array('id' => 12, 'otherkey' => 'othervalue'))) . "\n";