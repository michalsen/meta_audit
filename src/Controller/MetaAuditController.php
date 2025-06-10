<?php

declare(strict_types=1);

namespace Drupal\meta_audit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\meta_audit\MetaAuditService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Metatag audit controller.
 */
class MetaAuditController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */

  protected $entityTypeManager;

  /**
   * The meta audit service.
   *
   * @var \Drupal\meta_audit\MetaAuditService
   */
  protected $metaAuditService;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, MetaAuditService $metaAuditService) {
    $this->entityTypeManager = $entity_type_manager;
    $this->metaAuditService = $metaAuditService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
      new MetaAuditService(
        $container->get('metatag.manager'),
        $container->get('entity_type.manager')
      )
    );
  }

}
