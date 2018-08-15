<?php

namespace Drupal\generating_entities_module\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Language\LanguageManagerInterface;

class EntitiesService {
  
  protected $languageManager;
  
  protected $entityTypeManager;
  
  protected $entityQuery;
  
  /**
   * Constructs a new EntitiesService object.
   */
  public function __construct(LanguageManagerInterface $langManagInterf, EntityTypeManagerInterface $entityTypeManager, QueryFactory $queryFact) {
    $this->languageManager = $langManagInterf;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityQuery = $queryFact;
  }
  
  /**
   * Loads a list of custom entities.
   *
   * @param string $date
   *   Date that should be in the range of dates of entity.
   */
  public function getEntities($date = "2018-08-14") {
    $entity_ids = $this->getEntityIds($date);
    
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
  
  
  //filtering entities with necessary conditions
  private function getEntityIds($date) {
    $curr_lang = $this->languageManager->getCurrentLanguage()->getId();
    
    //passed date is invalid
    if($this->validateDate($date) === false) {
      return -2;
    }
    
    $intDate = strtotime($date);
    
    $query = $this->entityQuery->get('my_content_entity')
      ->condition('langcode', $curr_lang, '=')
      ->condition('field_date_my.start_date', $intDate, '<=')
      ->condition('field_date_my.end_date', $intDate, '>=');
      
    return $query->execute();
  }
  
  private function validateDate($reqDate) {
    $regExp = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
    
    if(!(preg_match($regExp, $reqDate))) {
      return false;
    }
    
    return true;
  }
}