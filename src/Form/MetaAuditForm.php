<?php

declare(strict_types=1);

namespace Drupal\meta_audit\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\meta_audit\MetaAuditService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Meta Audit form.
 */
final class MetaAuditForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The MetaAuditService.
   *
   * @var \Drupal\meta_audit\MetaAuditService
   */
  protected $metaAuditService;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a MetaAuditForm object.
   *
   * @param \Drupal\meta_audit\MetaAuditService $metaAuditService
   *   The MetaAuditService.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(
    MetaAuditService $metaAuditService,
    RendererInterface $renderer,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->metaAuditService = $metaAuditService;
    $this->renderer = $renderer;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('meta_audit.meta_audit_service'),
      $container->get('renderer'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'meta_audit_meta_audit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Get the list of content types using dependency injection.
    $content_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $options = [];
    foreach ($content_types as $type) {
      $options[$type->id()] = $type->label();
    }

    // Build the form.
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    // Check if there is any rendered output to display after submission.
    if ($form_state->has('rendered_output')) {
      $form['rendered_output'] = [
        '#markup' => $this->renderer->render($form_state->get('rendered_output')),
      // Optional: add a wrapper for styling.
        '#prefix' => '<div class="meta-audit-output">',
        '#suffix' => '</div>',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if (mb_strlen($form_state->getValue('message')) < 10) {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('Message should be at least 10 characters.'),
    //     );
    //   }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    // Get the selected content type.
    $selected_content_type = $form_state->getValue('content_type');

    // Call getNodeTags with the selected content type.
    $build = $this->metaAuditService->getNodeTags($selected_content_type);

    // Store the rendered output in the form state.
    // Ensure this line is present.
    $form_state->set('rendered_output', $build);

    // Rebuild the form to display the rendered output.
    $form_state->setRebuild();

  }

}
