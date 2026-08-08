<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\PveUser;
use App\Entity\ImportStatus;

class ImportStatusControllerTest extends WebTestCase {
    public function testShowStatus() {
        $username = "test@pam";
        $vm_name = "testvm";
        $vmid = 100;
        $error_occurred = false;
        $complete = false;

        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $testStatus = new ImportStatus($username, $vm_name, $vmid);
        $testStatus->setErrorOccurred($error_occurred);
        $testStatus->setComplete($complete);
        $entityManager->persist($testStatus);
        $entityManager->flush();

        $client->loginUser(new PveUser($username));
        $client->request("GET", "/import_status");
        $importStatusResponseData = \json_decode($client->getResponse(), true);

        $this->assertSame(1, \count($importStatusResponseData));
        $this->assertSame($vm_name, $importStatusResponseData[0]["vm_name"]);
        $this->assertSame($vmid, $importStatusResponseData[0]["vmid"]);
        $this->assertSame($error_occurred, $importStatusResponseData[0]["error_occurred"]);
        $this->assertSame($complete, $importStatusResponseData[0]["complete"]);
    }

    public function testShowStatusComplete() {
        $username = "test@pam";
        $vm_name = "testvm";
        $vmid = 100;
        $error_occurred = false;
        $complete = true;

        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $testStatus = new ImportStatus($username, $vm_name, $vmid);
        $testStatus->setErrorOccurred($error_occurred);
        $testStatus->setComplete($complete);
        $entityManager->persist($testStatus);
        $entityManager->flush();

        $client->loginUser(new PveUser($username));
        $client->request("GET", "/import_status");
        $importStatusResponseData = \json_decode($client->getResponse(), true);

        $this->assertSame(1, \count($importStatusResponseData));
        $this->assertSame($vm_name, $importStatusResponseData[0]["vm_name"]);
        $this->assertSame($vmid, $importStatusResponseData[0]["vmid"]);
        $this->assertSame($error_occurred, $importStatusResponseData[0]["error_occurred"]);
        $this->assertSame($complete, $importStatusResponseData[0]["complete"]);

        $finishedStatus = $entityManager->getRepository(ImportStatus::class)->findOneby([ "vmid" => $vmid ]);
        $this->assertNull($finishedStatus, "complete status should not exist after being fetched");
    }

    public function testShowStatusFail() {
        $username = "test@pam";
        $vm_name = "testvm";
        $vmid = 100;
        $error_occurred = true;
        $error_message = "error occurred";
        $complete = false;

        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $testStatus = new ImportStatus($username, $vm_name, $vmid);
        $testStatus->setErrorOccurred($error_occurred);
        $testStatus->setComplete($complete);
        $testStatus->setErrorMessage($error_message);
        $entityManager->persist($testStatus);
        $entityManager->flush();

        $client->loginUser(new PveUser($username));
        $client->request("GET", "/import_status");
        $importStatusResponseData = \json_decode($client->getResponse(), true);

        $this->assertSame(1, \count($importStatusResponseData));
        $this->assertSame($vm_name, $importStatusResponseData[0]["vm_name"]);
        $this->assertSame($vmid, $importStatusResponseData[0]["vmid"]);
        $this->assertSame($error_occurred, $importStatusResponseData[0]["error_occurred"]);
        $this->assertSame($complete, $importStatusResponseData[0]["complete"]);

        $finishedStatus = $entityManager->getRepository(ImportStatus::class)->findOneby([ "vmid" => $vmid ]);
        $this->assertNull($finishedStatus, "failed status should not exist after being fetched");
    }

    public function testShowStatusMultipleUsers() {
        $username = "test@pam";
        $vm_name = "testvm";
        $vmid = 100;
        $error_occurred = false;
        $complete = false;

        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $testStatus = new ImportStatus($username, $vm_name, $vmid);
        $testStatus->setErrorOccurred($error_occurred);
        $testStatus->setComplete($complete);
        $entityManager->persist($testStatus);

        $otherUserStatus = new ImportStatus("other@pam", "other_vm", 200);
        $otherUserStatus->setErrorOccurred(false);
        $otherUserStatus->setComplete(false);
        $entityManager->persist($otherUserStatus);

        $entityManager->flush();

        $client->loginUser(new PveUser($username));
        $client->request("GET", "/import_status");
        $importStatusResponseData = \json_decode($client->getResponse(), true);

        $this->assertSame(1, \count($importStatusResponseData));
        $this->assertSame($vm_name, $importStatusResponseData[0]["vm_name"]);
        $this->assertSame($vmid, $importStatusResponseData[0]["vmid"]);
        $this->assertSame($error_occurred, $importStatusResponseData[0]["error_occurred"]);
        $this->assertSame($complete, $importStatusResponseData[0]["complete"]);
    }
}
?>