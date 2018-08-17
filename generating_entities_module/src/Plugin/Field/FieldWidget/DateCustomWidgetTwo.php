<?php

namespace Drupal\generating_entities_module\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Plugin implementation of the 'date_custom_widget_2' widget.
 *
 * @FieldWidget(
 *   id = "date_custom_widget_2",
 *   label = @Translation("Date custom widget two"),
 *   field_types = {
 *     "field_date_custom_2"
 *   },
 *   multiple_values = TRUE,
 * )
 */
class DateCustomWidgetTwo extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
      'input_format' => 'date',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    
    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];
    
    //Settings for input format (calendar or text)
    $elements['input_format'] = [
      '#type' => 'select',
      '#options' => [
        'date' => $this->t('Input via calendar'),
        'textfield' => $this->t('Input via text')
      ],
      '#default_value' => $this->getSetting('input_format'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['start_date'] = [
      '#type' => $this->getSetting('input_format'),
      '#title' => t('Start'),
      '#description' => t('Field for start date'),
      '#default_value' => isset($items[$delta]->start_date) ? date('Y-m-d', $items[$delta]->start_date) : $this->getTodayDate(),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$this, 'validateEndDate'],
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Validating...'),
        ],
      ],
    ];
    
    $element['end_date'] = [
      '#type' => $this->getSetting('input_format'),
      '#title' => t('End'),
      '#description' => t('Field for end date'),
      '#default_value' => isset($items[$delta]->end_date) ? date('Y-m-d', $items[$delta]->end_date) : $this->getTodayDate(),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$this, 'validateEndDate'],
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Validating...'),
        ],
      ],
      '#element_validate' => [
        [$this, 'validateFormInputDate'],
      ],
      '#suffix' => '<span class="date-validation-msg"></span>',
    ];

    return $element;
  }

  
  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);
    
    $values['start_date'] = strtotime($values['start_date']);
    $values['end_date'] = strtotime($values['end_date']);
    
    return $values;
  }
  
 
  private function getTodayDate() {
    return date("Y-m-d");
  }
  
  private function getFormValues(FormStateInterface $form_state) {
    $currentVals = [];
    
    $currentVals['start_date'] = $form_state->getValue($this->fieldDefinition->getName())['start_date'];
    $currentVals['end_date'] = $form_state->getValue($this->fieldDefinition->getName())['end_date'];
    
    return $currentVals;
  }
  
  //validating correct date format (Y-m-d) and logic (end_date >= start_date)
  public function validateFormInputDate(array $element, FormStateInterface $form_state) {
    $currVals = $this->getFormValues($form_state);
    
    $startDate = $currVals['start_date'];
    $endDate = $currVals['end_date'];
    
    if($this->validateDateFormat($startDate, $endDate) === false) {
      $form_state->setError($element, t('Invalid date format'));
      return;
    }
    
    if($this->validateDateLogic($startDate, $endDate) === false) {
      $form_state->setError($element, t('End date should go after start date'));
    }
     
  }
  
  //ensures that end date goes after start date
  private function validateDateLogic($start, $end) {
    $startDate = strtotime($start);
    $endDate = strtotime($end);
    
    if($endDate >= $startDate) {
      return true;
    }
    
    return false;
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

  //AJAX validation for date format
  public function validateEndDate(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    
    //format is Y-m-d
    $currVals = $this->getFormValues($form_state);
    if($this->validateDateFormat($currVals['start_date'], $currVals['end_date']) === false) {
      $response->addCommand(new HtmlCommand('.date-validation-msg', 'Date format is not correct. It should be Y-m-d'));
    }
    else {
      $response->addCommand(new HtmlCommand('.date-validation-msg', 'Date format is OK'));
    }
    
    return $response;
  }
  
}
