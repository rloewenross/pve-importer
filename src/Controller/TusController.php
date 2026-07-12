<?php
namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use TusPhp\Tus\Server as TusServer;
use TusPhp\Events\TusEvent;
use App\PveClientFactory;
use App\Message\DiskImportMessage;
use App\Message\DiskUploadMessage;

class TusController extends AbstractController {
    public function __construct(
        private PveClientFactory $pveClientFactory,
        private MessageBusInterface $bus,
    ) {}

    #[Route('/tus/', name: 'tus_post')]
    #[Route('/tus/{token}', name: 'tus', requirements: [ 'token' => '.+' ])]
    public function tus(Request $request, TusServer $server): Response {
        $client = $this->pveClientFactory->fromSession($request->getSession());
        $bus = $this->bus;
        $server->event()->addListener('tus-server.upload.complete', function (TusEvent $event) use ($bus, $client) {
            $bus->dispatch(new DiskUploadMessage($event, $client->toInfo()));
        });
        return $server->serve();
    }
}