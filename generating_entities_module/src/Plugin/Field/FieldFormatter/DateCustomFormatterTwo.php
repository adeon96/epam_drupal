<?php

namespace Drupal\generating_entities_module\Plugin\Field\FieldFormatter;
     
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
     
/**
 * Plugin implementation of the 'date custom' formatter.
 *
 * @FieldFormatter(
 *   id = "date_custom_formatter_2",
 *   label = @Translation("Date Custom Formatter Two"),
 *   description = @Translation("Date Custom Formatter"),
 *   field_types = {
 *     "field_date_custom_2",
 *   }
 * )
 */
     
class DateCustomFormatterTwo extends FormatterBase {
  
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'date_format' => 'Y-m-d',
    ] + parent::defaultSettings();
  }
  
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['date_format'] = [
      '#title' => $this->t('Output date format'),
      '#type' => 'select',
      '#options' => [
        'Y-m-d' => $this->t('year-month-day'),
        'm/d/Y' => $this->t('month/day/year'),
      ],
      '#default_value' => $this->getSetting('date_format'),
    ];

    return $element;
  }
  
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    
    $dateFormat = $this->getSetting('date_format');
     
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        'start_date' => [
          '#markup' => "Start date: " . date($dateFormat, $items[$delta]->start_date) . "<br />",
        ],
        'end_date' => [
          '#markup' => "End date: " . date($dateFormat, $items[$delta]->end_date),
        ],
      ];
    }
     
    return $elements;
  }
  
}