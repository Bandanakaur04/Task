<?php

namespace Drupal\task\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\task\Event\NodeInsertDemoEvent;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Logs the creation of a new node.
 */
class NodeInsertDemoSubscriber implements EventSubscriberInterface
{
  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;
  /**
   * The logger channel for task.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;
  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Task constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */

  public function __construct(Connection $database, LoggerChannelFactoryInterface $loggerFactory, AccountInterface $account)
  {
    $this->database = $database;
    $this->loggerFactory = $loggerFactory;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('database'),
      $container->get('logger.factory'),
      $container->get('current_user')
    );
  }

  /**
   * Log the creation of a new node.
   *
   * @param \Drupal\task\Event\NodeInsertDemoEvent $event
   */
  public function onDemoNodeInsert(NodeInsertDemoEvent $event)
  {
    $entity = $event->getEntity();
    if ($this->account->isAuthenticated() && $this->account->id() == 1) {
      $this->loggerFactory->get('task')->notice('New @type: @title. Created by: @owner. time: @time. nid: @nid',
        array(
          '@type' => $entity->getType(),
          '@nid' => $entity->id(),
          '@title' => $entity->label(),
          '@time' => $entity->getChangedTime(),
          '@owner' => $entity->getOwner()->getDisplayName()
        ));
      $query = $this->database->insert('custom_table')
        ->fields(['uid', 'title', 'nid', 'created_date'])
        ->values([1, $entity->label(), $entity->id(), '2017-12-14T08:00:00'])->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    $events[NodeInsertDemoEvent::DEMO_NODE_INSERT][] = ['onDemoNodeInsert'];
    return $events;
  }
}
