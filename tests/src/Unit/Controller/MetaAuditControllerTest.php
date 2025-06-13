<?php

declare(strict_types=1);

namespace Drupal\Tests\meta_audit\Unit\Controller;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\meta_audit\Controller\MetaAuditController;
use Drupal\meta_audit\MetaAuditService;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unit tests for MetaAuditController.
 *
 * @coversDefaultClass \Drupal\meta_audit\Controller\MetaAuditController
 * @group meta_audit
 */
final class MetaAuditControllerTest extends UnitTestCase {

  /**
   * The mocked entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected EntityTypeManagerInterface|MockObject $entityTypeManager;

  /**
   * The mocked meta audit service.
   *
   * @var \Drupal\meta_audit\MetaAuditService|\PHPUnit\Framework\MockObject\MockObject
   */
  protected MetaAuditService|MockObject $metaAuditService;

  /**
   * The controller under test.
   *
   * @var \Drupal\meta_audit\Controller\MetaAuditController
   */
  protected MetaAuditController $controller;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->metaAuditService = $this->createMock(MetaAuditService::class);

    $this->controller = new MetaAuditController(
          $this->entityTypeManager,
          $this->metaAuditService
      );
  }

  /**
   * Tests the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor(): void {
    $controller = new MetaAuditController(
          $this->entityTypeManager,
          $this->metaAuditService
      );

    $this->assertInstanceOf(MetaAuditController::class, $controller);
  }

  /**
   * Tests the create method.
   *
   * @covers ::create
   */
  public function testCreate(): void {
    // Mock container.
    $container = $this->createMock(ContainerInterface::class);

    // Mock entity type manager service.
    $entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);

    // Mock metatag manager service.
    $metatagManager = $this->createMock('\Drupal\metatag\MetatagManagerInterface');

    $container->expects($this->exactly(2))
      ->method('get')
      ->willReturnMap(
              [
              ['entity_type.manager', $entityTypeManager],
              ['metatag.manager', $metatagManager],
              ]
          );

    $controller = MetaAuditController::create($container);

    $this->assertInstanceOf(MetaAuditController::class, $controller);
  }

  /**
   * Tests that the controller properly sets up its dependencies.
   *
   * @covers ::__construct
   */
  public function testControllerDependencies(): void {
    $reflection = new \ReflectionClass($this->controller);

    // Test that entityTypeManager property is set.
    $entityTypeManagerProperty = $reflection->getProperty('entityTypeManager');
    $entityTypeManagerProperty->setAccessible(TRUE);
    $this->assertSame($this->entityTypeManager, $entityTypeManagerProperty->getValue($this->controller));

    // Test that metaAuditService property is set.
    $metaAuditServiceProperty = $reflection->getProperty('metaAuditService');
    $metaAuditServiceProperty->setAccessible(TRUE);
    $this->assertSame($this->metaAuditService, $metaAuditServiceProperty->getValue($this->controller));
  }

  /**
   * Tests that the controller extends ControllerBase.
   */
  public function testControllerExtendsControllerBase(): void {
    $this->assertInstanceOf('\Drupal\Core\Controller\ControllerBase', $this->controller);
  }

}
