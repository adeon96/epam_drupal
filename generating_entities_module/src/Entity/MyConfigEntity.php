<?php

namespace Drupal\generating_entities_module\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the My config entity entity.
 *
 * @ConfigEntityType(
 *   id = "my_config_entity",
 *   label = @Translation("My config entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\generating_entities_module\MyConfigEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\generating_entities_module\Form\MyConfigEntityForm",
 *       "edit" = "Drupal\generating_entities_module\Form\MyConfigEntityForm",
 *       "delete" = "Drupal\generating_entities_module\Form\MyConfigEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\generating_entities_module\MyConfigEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "my_config_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/my_config_entity/{my_config_entity}",
 *     "add-form" = "/admin/structure/my_config_entity/add",
 *     "edit-form" = "/admin/structure/my_config_entity/{my_config_entity}/edit",
 *     "delete-form" = "/admin/structure/my_config_entity/{my_config_entity}/delete",
 *     "collection" = "/admin/structure/my_config_entity"
 *   },
 *   config_export = {
 *     "configuration_title",
 *     "description"
 *   }
 * )
 */
class MyConfigEntity extends ConfigEntityBase implements MyConfigEntityInterface {

  /**
   * The My config entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The My config entity label.
   *
   * @var string
   */
  protected $label;
  
  protected $configuration_title;
  
  protected $description;
  
  public function getConfigurationTitle() {
    return $this->configuration_title;
  }
  
  public function getDescription() {
    return $this->description;
  }
  
  public function setConfigurationTitle($conf_title) {
    $this->configuration_title = $conf_title;
    return $this;
  }
  
  public function setDescription($descr) {
    $this->description = $descr;
    return $this;
  }

}
