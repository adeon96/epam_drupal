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
  private function createEntity() {   
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);
    
    $dateStart = $input['field_date_my']['start_date'];
    $dateEnd = $input['field_date_my']['end_date'];
    
    if($this->validateDateFormat($dateStart, $dateEnd) === true) {
      $input['field_date_my']['start_date'] = strtotime($dateStart);
      $input['field_date_my']['end_date'] = strtotime($dateEnd);
    }
    else {
      return -2;
    }
  
    $entity = $this->entityTypeManager->getStorage('my_content_entity')->create($input);
    
    try {
      $entity->save();
    } catch (Exception $e) {
      throw new BadRequestHttpException("Problem with creating a new entity: " . $e->getMessage());
    }
    
    return $entity;
  }
  
  //ensures that date input corresponds to date format(Y-m-d)
  private function validateDateFormat($start, $end) {
    $regExp = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
    
    if(!(preg_match($regExp, $start))) {
      return false;
    }
    
    if(!(preg_match($regExp, $end))) {
      return false;
    }
    
    return true;
  }
  
  public function post() {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    
    $createdEntity = $this->createEntity();
    
    if($createdEntity == -2) {
      return new ResourceResponse(t('Invalid date format passed in the request'), 500);
    }
    
    return new ResourceResponse($createdEntity);
    
  }
  
}