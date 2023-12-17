<?php

namespace Laminas\Interface\Helper\Functional\Test;

use Laminas\Interface\Helper\Service\ClassFinderService;
use Laminas\Mvc\Controller\AbstractController;
use PHPUnit\Framework\TestCase;

final class InterfaceFindingTest extends TestCase
{
    public function testFind(): void
    {
        $classFinder = new ClassFinderService();

        $interfaceClasses = $classFinder->findClassesByExtends(AbstractController::class);

        $this->assertNotEmpty($interfaceClasses);
    }
}
