<?php

namespace Drupal\generating_entities_module\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Provides a Post Resource
 *
 * @RestResource(
 *   id = "post_resource",
 *   label = @Translation("Post Resource"),
 *   uri_paths = {
 *     "canonical" = "/generating_entities_module/post_resource",
 *     "https://www.drupal.org/link-relations/create" = "/generating_entities_module/post_resource"
 *   }
 * )
 */
class PostResource extends ResourceBase {
  
  protected $currentUser;
  
  protected $entityTypeManager;
  
  
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
    EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
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
      $container->get('logger.factory')->get('ccms_rest'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }
  
  
  /**
   * Creates a new custom entity.
   *
   */
  private function createEntity($data) {    
    $dateStart = $data['field_date_my']['start_date'];
    $dateEnd = $data['field_date_my']['end_date'];
    
    if($this->validateDateFormat($dateStart, $dateEnd) !== false) {
      
      if($this->validateDatesOrder($dateStart, $dateEnd) !== false) {
        $data['field_date_my']['start_date'] = strtotime($dateStart);
        $data['field_date_my']['end_date'] = strtotime($dateEnd);
      }
      else {
        throw new HttpException(500, 'Your end date goes before start date.');
      }
    }
    else {
      throw new HttpException(500, 'Invalid date format passed. Y-m-d is expected.');
    }
  
    $entity = $this->entityTypeManager->getStorage('my_content_entity')->create($data);
    $entity->save();
    
    return $entity;
  }
  
  public function post($data) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    $createdEntity = $this->createEntity($data);
    
    $response = $createdEntity;
    
    return new ResourceResponse($response);
    
  }
  
  //ensures that date input corresponds to date format(Y-m-d)
  private function validateDateFormat($start, $end) {
    return (strtotime($start) && strtotime($end));
  }
  
  private function validateDatesOrder($start, $end) {
    $startDate = strtotime($start);
    $endDate = strtotime($end);
    
    return ($startDate <= $endDate);
  }
  
}