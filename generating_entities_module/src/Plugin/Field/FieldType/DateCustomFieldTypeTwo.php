<?php

namespace Drupal\generating_entities_module\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * @FieldType(
 *   id = "field_date_custom_2",
 *   label = @Translation("Date custom field type two"),
 *   module = "generating_entities_module",
 *   description = @Translation("My Field Type"), 
 *   default_widget = "date_custom_widget_2",
 *   default_formatter = "date_custom_formatter_2",
 * )
 */
class DateCustomFieldTypeTwo extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['start_date'] = DataDefinition::create('integer')
      ->setLabel(t('Start date'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE);
      
    $properties['end_date'] = DataDefinition::create('integer')
      ->setLabel(t('End date'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'start_date' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        
        'end_date' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $start_date = $this->get('start_date')->getValue();
    $end_date = $this->get('end_date')->getValue();
    return ($start_date === NULL || $start_date === '' || $end_date === NULL || $end_date === '');
  }
  
  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'start_date' => '',
      'end_date' => ''
    ] + parent::defaultFieldSettings();
  }

}
