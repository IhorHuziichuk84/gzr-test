<?php

namespace Drupal\Tests\task\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests for the Group page.
 */
class GroupTest extends ExistingSiteBase {

  /**
   * There is subscribe link only for authenticated user.
   */
  public function testFullView() {
    // Create a "Test" group.
    $node = $this->createNode([
      'title' => 'Test group',
      'type' => 'group',
      'uid' => 1,
    ]);
    // Visit group page.
    $this->drupalGet($node->toUrl());
    // There is no Subscribe link for anonymous users.
    $this->assertSession()->pageTextNotMatches('/Hi .+?, click here if you would like to subscribe to this group called .+?/');
    // Creates a user and log in.
    $user = $this->createUser();
    $this->drupalLogin($user);
    $this->drupalGet($node->toUrl());
    $message = "Hi {$user->getDisplayName()}, click here if you would like to subscribe to this group called {$node->label()}";
    // Users can see subscribe link.
    // This is the link for the current user - name and group names.
    $this->assertSession()->pageTextMatches("/{$message}/");
  }

}
