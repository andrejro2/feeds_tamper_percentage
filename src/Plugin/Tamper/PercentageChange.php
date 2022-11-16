<?php

namespace Drupal\feeds_tamper_percentage\Plugin\Tamper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tamper\Exception\TamperException;
use Drupal\tamper\TamperableItemInterface;
use Drupal\tamper\TamperBase;

/**
 * Plugin implementation for performing percentage change.
 *
 * @Tamper(
 *   id = "percentage_change",
 *   label = @Translation("Percentage change"),
 *   description = @Translation("Performs increase/decrease value on certain percent."),
 *   category = "Number"
 * )
 */
class PercentageChange extends TamperBase {

  const SETTING_OPERATION = 'operation';
  const SETTING_VALUE = 'value';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config[self::SETTING_OPERATION] = '';
    $config[self::SETTING_VALUE] = '';
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form[self::SETTING_OPERATION] = [
      '#type' => 'select',
      '#title' => $this->t('Operation'),
      '#required' => TRUE,
      '#description' => $this->t('The operation to apply to the imported value.'),
      '#default_value' => $this->getSetting(self::SETTING_OPERATION),
      '#options' => $this->getOptions(),
    ];

    $form[self::SETTING_VALUE] = [
      '#type' => 'number',
      '#title' => $this->t('Value'),
      '#required' => TRUE,
      '#description' => $this->t('A numerical value.'),
      '#field_suffix' => '%',
      '#default_value' => $this->getSetting(self::SETTING_VALUE),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->setConfiguration([
      self::SETTING_OPERATION => $form_state->getValue(self::SETTING_OPERATION),
      self::SETTING_VALUE => $form_state->getValue(self::SETTING_VALUE),
    ]);
  }

  /**
   * Get the math operation options.
   *
   * @return array
   *   List of options.
   */
  protected function getOptions() {
    return [
      'increase' => $this->t('increase'),
      'decrease' => $this->t('decrease'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function tamper($data, TamperableItemInterface $item = NULL) {
    $operation = $this->getSetting(self::SETTING_OPERATION);
    $value = $this->getSetting(self::SETTING_VALUE);

    if ($data === TRUE || $data === FALSE || $data === NULL) {
      $data = (int) $data;
    }

    if (!is_numeric($data)) {
      throw new TamperException('Percentage change plugin failed because data was not numeric.');
    }

    switch ($operation) {
      case 'increase':
        $data = $data + ($data / 100) * $value;
        break;

      case 'decrease':
        $data = $data - ($data / 100) * $value;
        break;

    }

    return $data;
  }

}
