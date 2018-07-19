<?php

namespace Drupal\generating_entities_module\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining My config entity entities.
 */
interface MyConfigEntityInterface extends ConfigEntityInterface {

  // Add get/set methods for your configuration properties here.
  public function getConfigurationTitle();
  public function getDescription();
  
  public function setConfigurationTitle($conf_title);
  public function setDescription($descr);
}
