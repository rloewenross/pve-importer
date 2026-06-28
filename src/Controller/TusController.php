<?php
namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use TusPhp\Tus\Server as TusServer;
use TusPhp\Events\TusEvent;
use App\PveClientFactory;

class TusController extends AbstractController {
    public function __construct(
        private SluggerInterface $slugger,
        private PveClientFactory $pveClientFactory,
    ) {}

    #[Route('/tus/', name: 'tus_post')]
    #[Route('/tus/{token}', name: 'tus', requirements: [ 'token' => '.+' ])]
    public function tus(Request $request, TusServer $server): Response {
        $tusHandler = new TusHandler($this->slugger, $request, $this->pveClientFactory);
        $server->event()->addListener('tus-server.upload.complete', [$tusHandler, 'handleComplete']);
        return $server->serve();
    }
}

class TusHandler {
    public function __construct(
        private SluggerInterface $slugger,
        private Request $request,
        private PveClientFactory $pveClientFactory,
    ) {}

    public function handleComplete(TusEvent $event) {
        $oldFilePath = $event->getFile()->getFilePath();
        $safeFileName = $this->slugger->slug(basename($oldFilePath));
        $fileName = $safeFileName . '-' . uniqid();
        $filePath = dirname($oldFilePath) . '/' . $fileName;
        
        rename($oldFilePath, $filePath);
        
        $session = $this->request->getSession();
        $client = $this->pveClientFactory->fromSession($session);
        $nodeName = strtok(gethostname(), '.');
        $newVmid = $client->getFreeVmid();
        
        $createVmResponse = $client->api('POST', '/nodes/' . $nodeName . '/qemu', [ 'vmid' => $newVmid ]);
        if ($createVmResponse->getStatusCode() != 200) {
            $session->set('import-status', 'error');
            $session->set('import-error', $createVmResponse->toArray()['error']);
            return;
        }
        
        $this->bus->dispatch(new DiskImportMessage($newVmid, $filePath, $client->pveStorage));
    }
}
?>