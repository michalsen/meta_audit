<?php

declare(strict_types=1);

namespace Drupal\Tests\meta_audit\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\meta_audit\MetaAuditService;
use Drupal\metatag\Entity\MetatagDefaults;
use Drupal\metatag\MetatagManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit tests for MetaAuditService.
 *
 * @coversDefaultClass \Drupal\meta_audit\MetaAuditService
 * @group meta_audit
 */
final class MetaAuditServiceTest extends UnitTestCase {

  /**
   * The mocked entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected EntityTypeManagerInterface|MockObject $entityTypeManager;

  /**
   * The mocked metatag manager.
   *
   * @var \Drupal\metatag\MetatagManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected MetatagManagerInterface|MockObject $metatagManager;

  /**
   * The service under test.
   *
   * @var \Drupal\meta_audit\MetaAuditService
   */
  protected MetaAuditService $metaAuditService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Set up the container.
    $container = new ContainerBuilder();
    \Drupal::setContainer($container);

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->metatagManager = $this->createMock(MetatagManagerInterface::class);

    $this->metaAuditService = new MetaAuditService(
      $this->metatagManager,
      $this->entityTypeManager
    );
  }

  /**
   * Tests getNodeTags method with valid content type.
   *
   * @covers ::getNodeTags
   * @covers ::getAllMetaTagsForNode
   * @covers ::getMetaTagSources
   */
  public function testGetNodeTagsWithValidContentType(): void {
    $contentType = 'article';
    
    // Mock node storage.
    $nodeStorage = $this->createMock(EntityStorageInterface::class);
    $this->entityTypeManager
      ->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($nodeStorage);

    // Mock node entity.
    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())
      ->method('id')
      ->willReturn(1);
    $node->expects($this->once())
      ->method('label')
      ->willReturn('Test Article');
    $node->expects($this->once())
      ->method('bundle')
      ->willReturn($contentType);
    $node->expects($this->once())
      ->method('hasField')
      ->with('field_meta_tags')
      ->willReturn(FALSE);

    $nodeStorage
      ->expects($this->once())
      ->method('loadByProperties')
      ->with(['type' => $contentType])
      ->willReturn([$node]);

    // Mock metatag manager to return some tags.
    $this->metatagManager
      ->expects($this->exactly(2)) // Called twice in getAllMetaTagsForNode and getMetaTagSources
      ->method('tagsFromEntity')
      ->with($node)
      ->willReturn([
        'title' => 'Test Title',
        'description' => 'Test Description',
      ]);

    $result = $this->metaAuditService->getNodeTags($contentType);

    // Verify the structure of the returned render array.
    $this->assertIsArray($result);
    $this->assertEquals('table', $result['#theme']);
    $this->assertArrayHasKey('#header', $result);
    $this->assertArrayHasKey('#rows', $result);
    $this->assertArrayHasKey('#empty', $result);
    
    // Verify header structure.
    $this->assertCount(3, $result['#header']);
    
    // Verify we have one row for our test node.
    $this->assertCount(1, $result['#rows']);
  }

  /**
   * Tests getNodeTags method with no nodes found.
   *
   * @covers ::getNodeTags
   */
  public function testGetNodeTagsWithNoNodes(): void {
    $contentType = 'nonexistent';
    
    // Mock node storage.
    $nodeStorage = $this->createMock(EntityStorageInterface::class);
    $this->entityTypeManager
      ->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($nodeStorage);

    $nodeStorage
      ->expects($this->once())
      ->method('loadByProperties')
      ->with(['type' => $contentType])
      ->willReturn([]);

    $result = $this->metaAuditService->getNodeTags($contentType);

    // Verify the structure when no nodes are found.
    $this->assertIsArray($result);
    $this->assertEquals('table', $result['#theme']);
    $this->assertArrayHasKey('#header', $result);
    $this->assertArrayHasKey('#rows', $result);
    $this->assertArrayHasKey('#empty', $result);
    
    // Verify no rows are returned.
    $this->assertEmpty($result['#rows']);
  }

  /**
   * Tests getAllMetaTagsForNode method with entity-specific tags.
   *
   * @covers ::getAllMetaTagsForNode
   */
  public function testGetAllMetaTagsForNodeWithEntityTags(): void {
    $node = $this->createMock(NodeInterface::class);
    
    $expectedTags = [
      'title' => 'Node Specific Title',
      'description' => 'Node Specific Description',
    ];

    $this->metatagManager
      ->expects($this->once())
      ->method('tagsFromEntity')
      ->with($node)
      ->willReturn($expectedTags);

    // Use reflection to test the protected method.
    $reflection = new \ReflectionClass($this->metaAuditService);
    $method = $reflection->getMethod('getAllMetaTagsForNode');
    $method->setAccessible(TRUE);

    $result = $method->invokeArgs($this->metaAuditService, [$node]);

    $this->assertEquals($expectedTags, $result);
  }

  /**
   * Tests getAllMetaTagsForNode method falls back to field meta tags.
   *
   * @covers ::getAllMetaTagsForNode
   */
  public function testGetAllMetaTagsForNodeWithFieldTags(): void {
    $node = $this->createMock(NodeInterface::class);
    
    // Mock that metatag manager returns empty tags.
    $this->metatagManager
      ->expects($this->once())
      ->method('tagsFromEntity')
      ->with($node)
      ->willReturn([]);

    // Mock that node has field_meta_tags field.
    $node->expects($this->once())
      ->method('hasField')
      ->with('field_meta_tags')
      ->willReturn(TRUE);

    // Mock field value.
    $fieldValue = serialize([
      'title' => 'Field Title',
      'description' => 'Field Description',
    ]);
    
    $fieldItem = $this->createMock(FieldItemInterface::class);
    $fieldItem->value = $fieldValue;
    
    $fieldList = $this->createMock(FieldItemListInterface::class);
    $fieldList->expects($this->once())
      ->method('get')
      ->with(0)
      ->willReturn($fieldItem);
    
    $node->expects($this->once())
      ->method('get')
      ->with('field_meta_tags')
      ->willReturn($fieldList);

    // Use reflection to test the protected method.
    $reflection = new \ReflectionClass($this->metaAuditService);
    $method = $reflection->getMethod('getAllMetaTagsForNode');
    $method->setAccessible(TRUE);

    $result = $method->invokeArgs($this->metaAuditService, [$node]);

    $expected = [
      'title' => 'Field Title',
      'description' => 'Field Description',
    ];

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests getMetaTagSources method identifies entity-specific sources.
   *
   * @covers ::getMetaTagSources
   */
  public function testGetMetaTagSourcesWithEntitySpecific(): void {
    $node = $this->createMock(NodeInterface::class);
    $tags = [
      'title' => 'Test Title',
      'description' => 'Test Description',
    ];

    $this->metatagManager
      ->expects($this->once())
      ->method('tagsFromEntity')
      ->with($node)
      ->willReturn($tags);

    // Use reflection to test the protected method.
    $reflection = new \ReflectionClass($this->metaAuditService);
    $method = $reflection->getMethod('getMetaTagSources');
    $method->setAccessible(TRUE);

    $result = $method->invokeArgs($this->metaAuditService, [$node, $tags]);

    $expected = [
      'title' => 'Node-specific',
      'description' => 'Node-specific',
    ];

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests that the service returns an empty array for invalid tags.
   *
   * @covers ::getAllMetaTagsForNode
   */
  public function testGetAllMetaTagsForNodeReturnsEmptyArrayForInvalidTags(): void {
    $node = $this->createMock(NodeInterface::class);
    $node->expects($this->once())
      ->method('bundle')
      ->willReturn('article');
    $node->expects($this->once())
      ->method('hasField')
      ->with('field_meta_tags')
      ->willReturn(FALSE);

    // Mock that all sources return non-array values or empty.
    $this->metatagManager
      ->expects($this->once())
      ->method('tagsFromEntity')
      ->with($node)
      ->willReturn([]); // Return empty array instead of null

    // Use reflection to test the protected method.
    $reflection = new \ReflectionClass($this->metaAuditService);
    $method = $reflection->getMethod('getAllMetaTagsForNode');
    $method->setAccessible(TRUE);

    $result = $method->invokeArgs($this->metaAuditService, [$node]);

    $this->assertEquals([], $result);
  }

}
