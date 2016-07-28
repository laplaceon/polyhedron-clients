<?php

	include "ApiWrapper.php";
	
	$apiWrapper = new ApiWrapper();
	
	echo $apiWrapper->request('/') . "\n";
	echo $apiWrapper->request('/user/check', array('method' => 'POST', 'params' => array('id' => 12, 'otherkey' => 'othervalue'))) . "\n";