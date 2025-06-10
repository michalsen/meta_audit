<?php

declare(strict_types=1);

namespace Drupal\meta_audit;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\metatag\MetatagManagerInterface;

/**
 * Meta Tag Audit Service.
 */
final class MetaAuditService {

  // Use the StringTranslationTrait for translation.
  use StringTranslationTrait;

  /**
   * Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Metatag Audit Service.
   *
   * @var \Drupal\meta_audit\MetaAuditService
   */
  protected $metaAuditService;

  /**
   * Constructs a MetaAuditService object.
   */
  public function __construct(
    MetatagManagerInterface $metatagManager,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Retrieves node tags for nodes of a type content_type.
   *
   * @return array
   *   Render array for the table of nodes and their meta tags.
   */
  public function getNodeTags($content_type) {
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $nodes = $nodeStorage->loadByProperties(['type' => $content_type]);

    $header = [
      $this->t('Node Title'),
      $this->t('Meta Tags'),
    ];

    // Initialize the rows array.
    $rows = [];
    foreach ($nodes as $node) {
      // Create a link to the node using the Link class.
      $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
      $link = Link::fromTextAndUrl($node->label(), $url);

      // Prepare the meta keys for this node.
      $meta_keys = [];
      if ($node->get('field_metatag')->value) {
        $tags = json_decode($node->get('field_metatag')->value);
        foreach ($tags as $tkey => $tvalue) {
          $meta_keys[] = $tkey;
        }
      }

      // Add a row to the table.
      $rows[] = [
        'data' => [
          $link->toString(),
          implode(', ', array_unique($meta_keys)),
        ],
      ];
    }

    // Create the render array for the table.
    $build = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;

  }

}
