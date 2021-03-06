<?php
use Monolog\Logger;
use Orno\Di\Container;
use SocioChat\Chat;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server;
use Core\DI;
use SocioChat\DIBuilder;
use Zend\Config\Config;

function CustomErrorHandler($errno, $errstr, $errfile, $errline)
{
	echo "ErrorHandler: $errfile line $errline: $errstr\n";
	return true;
}
set_error_handler('CustomErrorHandler');

require_once 'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');
/* @var $config Config */
$logger = $container->get('logger');
/* @var $logger Logger */

ini_set("session.gc_maxlifetime", $config->session->lifetime);

$pidFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'chat-server.pid';

if (file_exists($pidFile)) {
	$pid = file_get_contents($pidFile);
	if (file_exists("/proc/$pid")) {
		$logger->error("Found already running daemon instance [pid = $pid], aborting.");
		exit(1);
	} else {
		unlink($pidFile);
	}
}

$fh = fopen($pidFile, 'w');
if ($fh) {
	fwrite($fh, getmypid());
}
fclose($fh);

$app = new Chat();

$loop = $container->get('eventloop');
$webSock = new Server($loop);
$webSock->listen($config->daemon->port, $config->daemon->host);

$server = new IoServer(
	new HttpServer(new WsServer($app)),
	$webSock
);

$logger->info("Starting chat server daemon on ".$config->daemon->host.":".$config->daemon->port, ['CHAT-SERVER']);

$loop->run();
