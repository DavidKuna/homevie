<?php

namespace App;

use Nette,
	Ratchet\Server\IoServer,
	Ratchet\Http\HttpServer,
	Ratchet\WebSocket\WsServer;

/**
 * Description of ChatPresenter
 *
 * @author David Kuna
 */
class SyncPresenter extends BasePresenter{
	
	public function actionServer()
    {
		$port = 8080;
		$address = '0.0.0.0';
		$server = IoServer::factory(
			new HttpServer(
				new WsServer($this->context->SocketController)
			),
			$port,
			$address
		);

		$server->run();
    }
}
