<?php

namespace Drupal\generating_entities_module\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\Request;


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
  
  protected $entityTypeManager;
  
  protected $entityQuery;
  
  protected $languageManager;
  
  protected $currentRequest;
  
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
    EntityTypeManagerInterface $entityTypeManager,
    QueryFactory $queryFact,
    LanguageManagerInterface $langManagInterf,
    Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityQuery = $queryFact;
    $this->languageManager = $langManagInterf;
    $this->currentRequest = $request;
    
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
      $container->get('entity_type.manager'),
      $container->get('entity.query'),
      $container->get('language_manager'),
      $container->get('request_stack')->getCurrentRequest()
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
    
    $articles = $this->getArticles();
    
    if($articles == []) {
      $response = ['message' => 'No one article found for your query criteria'];
      return new ResourceResponse($response);
    }
    
    if($articles == -2) {
      $response = ['message' => 'Invalid date format. Y-m-d is expected'];
      return new ResourceResponse($response);
    }
    
    $response = new ResourceResponse($articles);
    return $response;
    
  }
  
  private function validateRequestDate($reqDate) {
    $regExp = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
    
    if(!(preg_match($regExp, $reqDate))) {
      return false;
    }
    
    return true;
  }
  
  //filtering entities with necessary conditions
  private function getEntityIds() {
    $curr_lang = $this->languageManager->getCurrentLanguage()->getId();
    
    //if GET parameter with date is set
    if(NULL !== $this->currentRequest->query->get('date')) {
      $req_date = $this->currentRequest->query->get('date');
      
      //passed date is invalid
      if($this->validateRequestDate($req_date) === false) {
        return -2;
      }
      
      $intReqDate = strtotime($req_date);
      
      $query = $this->entityQuery->get('my_content_entity')
        ->condition('langcode', $curr_lang, '=')
        ->condition('field_date_my.start_date', $intReqDate, '<=')
        ->condition('field_date_my.end_date', $intReqDate, '>=');
        
      return $query->execute();
    }
    
    //if GET parameter with date is not set
    $query = $this->entityQuery->get('my_content_entity')
      ->condition('langcode', $curr_lang, '=');
     
    return $query->execute();
  }
  
  private function getArticles() {
    $entity_ids = $this->getEntityIds();
    
    if($entity_ids == -2) {
      return -2;
    }
    
    //If there is no one entity matching query
    if(count($entity_ids) == 0) {
      return [];
    }
    
    $storage = $this->entityTypeManager->getStorage('my_content_entity');
    $contEnt = $storage->loadMultiple($entity_ids);
    
    
    $articles = [];
    
    foreach($contEnt as $ent) {
      //referenced entities details
      $artInfo = [];
      
      //title of custom entity
      $artInfo["title"] = $ent->getName();
      
      $refEnt = $ent->prop_def->referencedEntities();
      
      if(count($refEnt) != 0) {
        $artInfo["refer_ent_id"] = $refEnt[0]->id();
        $artInfo["refer_ent_title"] = $refEnt[0]->title->value;;
        $artInfo["refer_ent_body"] = $refEnt[0]->body->value;
      }
      
      $articles[$ent->id()] = $artInfo;
    }
    
    return $articles;
  }

}