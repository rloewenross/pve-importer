<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

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
        $state = "importing";

        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $testStatus = new ImportStatus($username, $vm_name);
        $testStatus->setImporting();
        $testStatus->setVmid($vmid);
        $entityManager->persist($testStatus);
        $entityManager->flush();

        $client->loginUser(new PveUser($username));
        $client->request("GET", "/import_status");
        $importStatusResponseData = \json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(1, \count($importStatusResponseData));
        $this->assertSame($vm_name, $importStatusResponseData[0]["vm_name"]);
        $this->assertSame($vmid, $importStatusResponseData[0]["vmid"]);
        $this->assertSame($state, $importStatusResponseData[0]["state"]);
    }

    public function testShowStatusComplete() {
        $username = "test@pam";
        $vm_name = "testvm";
        $vmid = 100;
        $state = "complete";

        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $testStatus = new ImportStatus($username, $vm_name);
        $testStatus->setComplete();
        $testStatus->setVmid($vmid);
        $entityManager->persist($testStatus);
        $entityManager->flush();

        $client->loginUser(new PveUser($username));
        $client->request("GET", "/import_status");
        $importStatusResponseData = \json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(1, \count($importStatusResponseData));
        $this->assertSame($vm_name, $importStatusResponseData[0]["vm_name"]);
        $this->assertSame($vmid, $importStatusResponseData[0]["vmid"]);
        $this->assertSame($state, $importStatusResponseData[0]["state"]);

        $finishedStatus = $entityManager->getRepository(ImportStatus::class)->findOneby([ "vmid" => $vmid ]);
        $this->assertNull($finishedStatus, "complete status should not exist after being fetched");
    }

    public function testShowStatusFail() {
        $username = "test@pam";
        $vm_name = "testvm";
        $vmid = 100;
        $state = "error";
        $error_message = "error occurred";

        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $testStatus = new ImportStatus($username, $vm_name);
        $testStatus->setErrorOccurred();
        $testStatus->setVmid($vmid);
        $testStatus->setErrorMessage($error_message);
        $entityManager->persist($testStatus);
        $entityManager->flush();

        $client->loginUser(new PveUser($username));
        $client->request("GET", "/import_status");
        $importStatusResponseData = \json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(1, \count($importStatusResponseData));
        $this->assertSame($vm_name, $importStatusResponseData[0]["vm_name"]);
        $this->assertSame($vmid, $importStatusResponseData[0]["vmid"]);
        $this->assertSame($state, $importStatusResponseData[0]["state"]);

        $finishedStatus = $entityManager->getRepository(ImportStatus::class)->findOneby([ "vmid" => $vmid ]);
        $this->assertNull($finishedStatus, "failed status should not exist after being fetched");
    }

    public function testShowStatusMultipleUsers() {
        $username = "test@pam";
        $vm_name = "testvm";
        $vmid = 100;
        $state = "importing";

        $client = static::createClient();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $testStatus = new ImportStatus($username, $vm_name);
        $testStatus->setImporting();
        $testStatus->setVmid($vmid);
        $entityManager->persist($testStatus);

        $otherUserStatus = new ImportStatus("other@pam", "other_vm");
        $otherUserStatus->setImporting();
        $otherUserStatus->setVmid(200);
        $entityManager->persist($otherUserStatus);

        $entityManager->flush();

        $client->loginUser(new PveUser($username));
        $client->request("GET", "/import_status");
        $importStatusResponseData = \json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(1, \count($importStatusResponseData));
        $this->assertSame($vm_name, $importStatusResponseData[0]["vm_name"]);
        $this->assertSame($vmid, $importStatusResponseData[0]["vmid"]);
        $this->assertSame($state, $importStatusResponseData[0]["state"]);
    }
}
?>