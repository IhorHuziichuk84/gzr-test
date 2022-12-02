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
    $this->assertSession()->elementNotExists('css', '.group-user-subscribe');
    // Creates a user and log in.
    $user = $this->createUser();
    $this->drupalLogin($user);
    $this->drupalGet($node->toUrl());
    // Users can see subscribe link.
    $this->assertSession()->elementExists('css', '.group-user-subscribe');
  }

}
