<?php

namespace Drupal\generating_entities_module;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Language\LanguageManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines a class to build a listing of My content entity entities.
 *
 * @ingroup generating_entities_module
 */
class MyContentEntityListBuilder extends EntityListBuilder {
	
  /**
   * The language manager class.
   *
   * @var Drupal\Core\Language\LanguageManager
   */
  protected $lang_manag;
  
  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
	return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('language_manager')
    );
  }
  
  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param Drupal\Core\Language\LanguageManagerInterface
   *   Language Manager Interface
   */
  public function __construct($entity_type, $storage, LanguageManagerInterface $lang_manag_interf) {
    $this->lang_manag = $lang_manag_interf;
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('My content entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\generating_entities_module\Entity\MyContentEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.my_content_entity.edit_form',
      ['my_content_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }
  
  
  //Filtering entities by language
  protected function getEntityIds() {
    $curr_lang = $this->lang_manag->getCurrentLanguage()->getId();
    $query = $this->getStorage()->getQuery()
      ->condition('langcode', $curr_lang, '=')
      ->sort($this->entityType->getKey('id'));

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }

}
