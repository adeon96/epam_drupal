<?php

namespace Drupal\generating_entities_module\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\Core\Cache\CacheableMetadata;

use Drupal\generating_entities_module\Services\EntitiesService;


/**
 * Provides a Get Resource
 *
 * @RestResource(
 *   id = "get_resource",
 *   label = @Translation("Get Resource"),
 *   uri_paths = {
 *     "canonical" = "/generating_entities_module/get_resource" 
 *   }
 * )
 */
class GetResource extends ResourceBase {
  
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  
  protected $entitiesService;
  
  
  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    EntitiesService $entServ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    
    $this->currentUser = $current_user;
    $this->entitiesService = $entServ;
    
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('example_rest'),
      $container->get('current_user'),
      $container->get('generating_entities_module.entities_service')
    );
  }
  
  /**
   * Responds to entity GET requests.
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    $cache = CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'max-age' => 300,
        'contexts' => ['url.query_args'],
      ],
    ]);
    
    $service = $this->entitiesService;
    $articles = $service->getEntities("2018-08-07");    
    
    $response = $articles;
    
    $cache->addCacheableDependency($articles);
    $response->addCacheableDependency($cache);
    
    return new ResourceResponse($response);
    
  }

}