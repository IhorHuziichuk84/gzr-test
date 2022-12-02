<?php

namespace Drupal\task\Plugin\EntityViewBuilder;

use Drupal\Core\Entity\EntityTypeInterface;
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $builder = parent::createInstance($container, $entity_type);
    $builder->entityTypeManager = $container->get('entity_type.manager');

    return $builder;
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
      $storage = $this->entityTypeManager->getStorage('og_membership');
      $props = [
        'uid' => $user->id(),
        'entity_type' => $entity->getEntityTypeId(),
        'entity_bundle' => $entity->bundle(),
        'entity_id' => $entity->id(),
      ];
      $memberships = $storage->loadByProperties($props);
      /** @var \Drupal\og\OgMembershipInterface $membership */
      $membership = reset($memberships);
      if (!$membership) {

        $parameters = [
          'entity_type_id' => $entity->getEntityTypeId(),
          'group' => $entity->id(),
          'og_membership_type' => OgMembershipInterface::TYPE_DEFAULT,
        ];
        $url = Url::fromRoute('og.subscribe', $parameters);
        $link = [
          '#type' => 'link',
          '#title' => "Hi {$user->getDisplayName()}, click here if you would like to subscribe to this group called {$entity->label()}",
          '#url' => $url,
          '#attributes' => [
            'class' => [
              'group-user-subscribe',
              'mt-5',
              'text-blue-900',
              'rounded-lg',
              'px-6',
              'py-1',
              'text-xl',
              'border-2',
              'border-blue-500',
            ],
          ],
        ];
        return $link;
      }
    }
    return [];
  }

}
