<?php

declare(strict_types=1);

namespace Drupal\meta_audit;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\metatag\MetatagManagerInterface;
use Drupal\metatag\Entity\MetatagDefaults;

/**
 * Meta Tag Audit Service.
 *
 * @category  Drupal
 * @package   Drupal\meta_audit
 * @author    Eric Michalsen <eric.michalsen@gmail.com>
 * @license   GPL-2.0-or-later
 * @link      https://www.drupal.org/project/meta_audit
 */
final class MetaAuditService
{
    use StringTranslationTrait;

    protected $entityTypeManager;
    protected $metatagManager;

    /**
     * Constructs a MetaAuditService object.
     *
     * @param \Drupal\metatag\MetatagManagerInterface $metatagManager
     *   The metatag manager service.
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
     *   The entity type manager service.
     */
    public function __construct(
        MetatagManagerInterface $metatagManager,
        EntityTypeManagerInterface $entityTypeManager,
    ) {
        $this->metatagManager = $metatagManager;
        $this->entityTypeManager = $entityTypeManager;
    }

    /**
     * Builds a render array table of nodes and their meta tags for a content type.
     *
     * @param string $content_type
     *   The machine name of the content type to audit.
     *
     * @return array
     *   A render array for a Drupal table listing nodes and their meta tags.
     */
    public function getNodeTags(string $content_type): array
    {
        $nodeStorage = $this->entityTypeManager->getStorage('node');
        $nodes = $nodeStorage->loadByProperties(['type' => $content_type]);

        $header = [
            $this->t('Node Title'),
            $this->t('Meta Tags'),
            $this->t('Tag Sources'),
        ];

        $rows = [];
        foreach ($nodes as $node) {
            $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
            $link = Link::fromTextAndUrl($node->label(), $url);

            // Get all possible meta tags for this node
            $tags = $this->getAllMetaTagsForNode($node);

            // Prepare display
            $meta_keys = array_keys(array_filter($tags));
            $sources = $this->getMetaTagSources($node, $tags);

            $rows[] = [
                'data' => [
                    $link->toString(),
                    implode(', ', $meta_keys),
                    implode(', ', $sources),
                ],
            ];
        }

        return [
            '#theme' => 'table',
            '#header' => $header,
            '#rows' => $rows,
            '#empty' => $this->t('No nodes found.'),
        ];
    }

    /**
     * Retrieves all meta tags for a given node, checking entity, field, content type, and global defaults.
     *
     * @param \Drupal\node\NodeInterface $node
     *   The node entity to retrieve meta tags for.
     *
     * @return array
     *   An array of meta tags.
     */
    protected function getAllMetaTagsForNode($node): array
    {
        // 1. Check entity-specific tags first
        $tags = $this->metatagManager->tagsFromEntity($node);

        // 2. If empty, check if tags are stored in a field (common pattern)
        if (empty($tags) && $node->hasField('field_meta_tags')) {
            $field_value = $node->get('field_meta_tags')->value;
            if (!empty($field_value)) {
                $tags = unserialize($field_value);
            }
        }

        // 3. Check content type defaults
        if (empty($tags)) {
            $content_type = $node->bundle();
            $defaults = MetatagDefaults::load($content_type);
            if ($defaults) {
                $tags = $defaults->get('tags');
            }
        }

        // 4. Check global defaults
        if (empty($tags)) {
            $global = MetatagDefaults::load('global');
            if ($global) {
                $tags = $global->get('tags');
            }
        }

        return is_array($tags) ? $tags : [];
    }

    /**
     * Determines the source of each meta tag for a given node.
     *
     * @param \Drupal\node\NodeInterface $node
     *   The node entity to check meta tag sources for.
     * @param array $tags
     *   The array of meta tags to check sources for.
     *
     * @return array
     *   An array mapping meta tag keys to their sources.
     */
    protected function getMetaTagSources($node, array $tags): array
    {
        $sources = [];

        // Check where each tag came from
        foreach ($tags as $key => $value) {
            if (!empty($value)) {
                // Check entity-specific first
                $entity_tags = $this->metatagManager->tagsFromEntity($node);
                if (isset($entity_tags[$key]) && !empty($entity_tags[$key])) {
                    $sources[$key] = 'Node-specific';
                    continue;
                }

                // Check content type defaults
                $content_type = $node->bundle();
                $defaults = MetatagDefaults::load($content_type);
                if ($defaults && isset($defaults->get('tags')[$key])) {
                    $sources[$key] = 'Content type default';
                    continue;
                }

                // Check global defaults
                $global = MetatagDefaults::load('global');
                if ($global && isset($global->get('tags')[$key])) {
                    $sources[$key] = 'Global default';
                }
            }
        }

        return array_unique($sources);
    }
}
