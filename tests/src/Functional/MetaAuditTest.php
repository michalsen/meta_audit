<?php

declare(strict_types=1);

namespace Drupal\Tests\meta_audit\Functional;

use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Meta Audit module.
 *
 * @group meta_audit
 */
final class MetaAuditTest extends BrowserTestBase
{

    /**
     * {@inheritdoc}
     */
    protected $defaultTheme = 'stark';

    /**
     * {@inheritdoc}
     */
    protected static $modules = ['node', 'metatag', 'meta_audit'];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Create the 'recipe' content type.
        $this->createRecipeContentType();
    }

    /**
     * Tests the audit page access.
     */
    public function testAuditPage(): void
    {
        // Create a user with the necessary permission to access the audit page.
        $admin_user = $this->drupalCreateUser(['access administration pages', 'administer site configuration']);
        $this->drupalLogin($admin_user);

        // Access the audit page.
        $this->drupalGet('/admin/meta_audit');

        // Assert that the status code is 200.
        $this->assertSession()->statusCodeEquals(200);
    }

    /**
     * Tests that the 'recipe' content type was created successfully.
     */
    public function testRecipeContentTypeCreation(): void
    {
        // Assert that the 'recipe' content type exists.
        $this->assertNotNull(NodeType::load('recipe'), 'The recipe content type was not created successfully.');
    }

    /**
     * Creates a content type called 'recipe'.
     */
    protected function createRecipeContentType(): void
    {
        // Define the content type.
        $contentType = [
        'type' => 'recipe',
        'name' => 'Recipe',
        'description' => 'A content type for recipes.',
        'base' => 'node_content',
        'bundle' => 'recipe',
        'provider' => 'node',
        'is_translatable' => false,
        ];

        // Create the content type.
        $nodeType = NodeType::create($contentType);
        $nodeType->save();
    }

}
