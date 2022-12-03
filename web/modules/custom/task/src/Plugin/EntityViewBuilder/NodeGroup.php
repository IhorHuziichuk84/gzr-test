<?php

namespace Drupal\task\Plugin\EntityViewBuilder;

use Drupal\node\NodeInterface;
use Drupal\server_general\EntityViewBuilder\NodeViewBuilderAbstract;
use Drupal\server_general\TitleAndLabelsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\og\OgMembershipInterface;
use Drupal\Core\Url;

/**
 * The "Node Group" plugin.
 *
 * @EntityViewBuilder(
 *   id = "node.group",
 *   label = @Translation("Node - Group"),
 *   description = "Node view builder for Group bundle."
 * )
 */
class NodeGroup extends NodeViewBuilderAbstract {

  use TitleAndLabelsTrait;

  /**
   * The membership manager service.
   *
   * @var \Drupal\og\MembershipManager
   */
  protected $membershipManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $build = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $build->membershipManager = $container->get('og.membership_manager');
    return $build;
  }

  /**
   * Build full-view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildFull(array $build, NodeInterface $entity) {

    $build = [];
    // Show the page title.
    $element = $this->buildConditionalPageTitle($entity);
    $build[] = $this->wrapContainerWide($element);
    // Body.
    $element = $this->buildProcessedText($entity, 'body');
    $build[] = $this->wrapContainerWide($element);
    // Show subscibe link.
    $element = $this->getGroupSubscribeLink($entity);
    $build[] = $this->wrapContainerWide($element);

    return $build;
  }

  /**
   * Build subscribe link for group.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   A renderable array of the link.
   */
  private function getGroupSubscribeLink(NodeInterface $entity) {
    $user = $this->currentUser;

    if ($user->isAuthenticated()) {

      $membership = $this->membershipManager->getMembership($entity, $user->id());
      if (empty($membership)) {
        $parameters = [
          'entity_type_id' => $entity->getEntityTypeId(),
          'group' => $entity->id(),
          'og_membership_type' => OgMembershipInterface::TYPE_DEFAULT,
        ];
        $url = Url::fromRoute('og.subscribe', $parameters)->toString();
        $title = $this->t(
            "Hi @name, click here if you would like to subscribe to this group called @label",
            [
              '@name' => $user->getDisplayName(),
              '@label' => $entity->label(),
            ]
        );
        // Lets use theme button template.
        $link = [
          '#theme' => 'server_theme_button',
          '#url' => $url,
          '#title' => $this->t("Hi {$user->getDisplayName()}, click here if you would like to subscribe to this group called {$entity->label()}"),
        ];
        return $link;
      }
    }
    return [];
  }

}
