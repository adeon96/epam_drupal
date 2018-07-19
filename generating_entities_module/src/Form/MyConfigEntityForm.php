<?php

namespace Drupal\generating_entities_module\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MyConfigEntityForm.
 */
class MyConfigEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $my_config_entity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $my_config_entity->label(),
      '#description' => $this->t("Label for the My config entity."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $my_config_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\generating_entities_module\Entity\MyConfigEntity::load',
      ],
      '#disabled' => !$my_config_entity->isNew(),
    ];
	
	$form['configuration_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Configuration title'),
      '#maxlength' => 50, 
      '#default_value' => $my_config_entity->getConfigurationTitle(),
      '#description' => $this->t("Title for the My configuration entity."),
      '#required' => TRUE,
    ];
	
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#maxlength' => 300,
      '#default_value' => $my_config_entity->getDescription(),
      '#description' => $this->t("Description for the My configuration entity."),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $my_config_entity = $this->entity;	
    $status = $my_config_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label My config entity.', [
          '%label' => $my_config_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label My config entity.', [
          '%label' => $my_config_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($my_config_entity->toUrl('collection'));
  }

}
