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

		function __construct() {
			$context = new ZMQContext();
			// use req instead of dealer since there's only one api server
			$socket = $context->getSocket(ZMQ::SOCKET_REQ, 'apiconn');
			$socket->connect($this->port);
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
			
			// $timeStarted = time();
			
			// block until response received
			while(true) {
				$response = $this->socket->recv();
				
				if($response) {
					break;
				}
			}
			
			return $response;
		}
	}