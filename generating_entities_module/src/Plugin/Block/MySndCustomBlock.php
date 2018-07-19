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
  
  protected $articles;
  
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
	
    $this->getArticlesInfo();
  }
  
  public function getArticlesInfo() {
    $entities = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'article']);
	
    foreach($entities as $art) {
      if($art->field_color->value == "") $art->field_color->value = "gray";
    }
	
    $this->articles = $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['my_snd_custom_block']['#theme'] = 'my_template';
    $build['my_snd_custom_block']['#articles'] = $this->articles;
    $build['my_snd_custom_block']['#attached'] = array(
      'library' => array(
        'generating_entities_module/articles_colors',
      ),
    );
	
    return $build;
  }

}
