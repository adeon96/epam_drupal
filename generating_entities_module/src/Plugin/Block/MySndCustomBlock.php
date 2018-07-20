<?php

namespace Drupal\generating_entities_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a 'MySndCustomBlock' block.
 *
 * @Block(
 *  id = "my_snd_custom_block",
 *  admin_label = @Translation("My snd custom block"),
 * )
 */
class MySndCustomBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {
	  
  protected $entityTypeManager;
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }
  
  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param QueryInterface $query
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    //associative array
    //key - entity's title
    //value - color of the referenced article
    $entityData = array();
    
    $storage = $this->entityTypeManager->getStorage('my_content_entity');
    $contEnt = $storage->loadMultiple();
    
    foreach($contEnt as $ent) {
      $title = $ent->getName();
      $artId = $ent->prop_def->getString();
      
      $article = $this->entityTypeManager->getStorage('node')->load($artId);
      $artColor = $article->field_color->value;
      
      $entityData[$title] = $artColor;
    }
    
    $build = [];

    $build['my_snd_custom_block']['#theme'] = 'my_template';
    $build['my_snd_custom_block']['#articles'] = $entityData;
    $build['my_snd_custom_block']['#attached'] = array(
      'library' => array(
        'generating_entities_module/articles_colors',
      ),
    );
	
    return $build;
  }

}
