<?php

namespace App\Tests\Service;

use App\Service\CsvToMemberService;
use App\Entity\Member;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvToMemberServiceTest extends KernelTestCase
{
    protected CsvToMemberService $csvToMemberService;
    protected EntityManager $entityManager;

    public function setup(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->csvToMemberService = $container->get(CsvToMemberService::class);
        $this->entityManager = $container->get('doctrine.orm.entity_manager');

        $this->entityManager->beginTransaction();
    }

    public function tearDown(): void {
        $this->entityManager->rollback();
    }

    public function testRun()
    {
        $mockFile = new UploadedFile(dirname(__FILE__).'/fixtures/members.csv', 'members.csv', 'text/csv', null, true);
        $this->csvToMemberService->run($mockFile);
        $members = $this->csvToMemberService->getMembers();
        $errors = $this->csvToMemberService->getErrors();
        $this->assertEquals([], $errors);
        $this->assertEquals(count($members), 10);

        // Asserts that no member data was inadvertently updated
        // (except for null to an empty string)
        foreach($members as $member) {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changes = $unitOfWork->getEntityChangeSet($member);
            foreach ($changes as $key => $changeArray) {
                $this->assertEquals($changeArray[0], $changeArray[1]);
            }
        }
    }

    public function testRunWithLegacyHeadings()
    {
        $mockFile = new UploadedFile(dirname(__FILE__).'/fixtures/members-legacy-headings.csv', 'members.csv', 'text/csv', null, true);
        $this->csvToMemberService->run($mockFile);
        $members = $this->csvToMemberService->getMembers();
        $errors = $this->csvToMemberService->getErrors();
        $this->assertEquals([], $errors);
        $this->assertEquals(count($members), 10);

        // Asserts that no member data was inadvertently updated
        // (except for null to an empty string)
        foreach($members as $member) {
            $unitOfWork = $this->entityManager->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changes = $unitOfWork->getEntityChangeSet($member);
            foreach ($changes as $key => $changeArray) {
                $this->assertEquals($changeArray[0], $changeArray[1]);
            }
        }
    }

    public function testRunWithUpdates()
    {
        $mockFile = new UploadedFile(dirname(__FILE__).'/fixtures/members-with-updates.csv', 'members.csv', 'text/csv', null, true);
        $this->csvToMemberService->run($mockFile);
        $members = $this->csvToMemberService->getMembers();
        $errors = $this->csvToMemberService->getErrors();
        $this->assertEquals([], $errors);
        $this->assertEquals(count($members), 1);

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changes = $unitOfWork->getEntityChangeSet($members[1]);

        $this->assertEquals('cjenkens@example.com', $changes['primaryEmail'][0]);
        $this->assertEquals('cjenkens@example.org', $changes['primaryEmail'][1]);

        $this->assertEquals('(804) 353-1901', $changes['primaryTelephoneNumber'][0]);
        $this->assertEquals('', $changes['primaryTelephoneNumber'][1]);

        $this->assertEquals('Member', (string) $changes['status'][0]);
        $this->assertEquals('Alumnus', (string) $changes['status'][1]);

        $this->assertTrue(!isset($changes['country']), 'Country set to US, rewritten to United States');
    }
}
