<?php

	class ApiWrapper {

		// inprocess location; untested
		// private $port = 'inproc://air';
		// ipc location; preferred but needs write permission
		private $port = 'ipc:///tmp/air.ipc';
		// tcp location; works ok and is fast enough
		// private $port = 'tcp://localhost:5555';

		private $context;
		private $socket;
		
		// 3 second timeout
		private $timeout = 3000;

		function __construct() {
			$context = new ZMQContext();
			// use req instead of dealer since there's only one api server
			$socket = $context->getSocket(ZMQ::SOCKET_REQ, 'apiconn');
			// use dealer when manually handling cases of the central API being down
			// $socket = $context->getSocket(ZMQ::SOCKET_DEALER, 'apiconn');
			// discard unsent messages
			$socket->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);
			$socket->setSockOpt(ZMQ::SOCKOPT_RCVTIMEO, $this->timeout);
			$socket->connect($this->port);
			// print_r($socket->getEndpoints());
			$this->context = $context;
			$this->socket = $socket;
		}
		
		function request($message, $bundle = null) {
			$method = 'GET';
			$parameters = null;
			if($bundle != null) {
				$method = $bundle['method'];
				if(isset($bundle['params'])) {
					$parameters = $bundle['params'];
				}
			}
			
			$payload = array(
				'method' => $method,
				'data' => $message,
				'parameters' => $parameters
			);

			$this->socket->send(json_encode($payload));
			
			// block until response received
			while(true) {
				$response = $this->socket->recv();
				
				// wait for timeout and send response or send error
				if($response) {
					break;
				} else {
					return json_decode(json_encode(array('status' => 'connecterr')));
				}
			}
			
			return json_decode($response);
		}
	}