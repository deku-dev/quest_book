<?php

namespace Drupal\quest_book\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Configure quest_book settings for this site.
 */
class AddReview extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quest_book_reviews';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['quest_book.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t("Your cat's name:"),
      '#description' => $this->t("Min 2 and max 100 characters"),
      '#required' => TRUE,
      '#attributes' => [
        'min-length' => '2',
        'max-length' => '100',
      ],
      '#ajax' => [
        'callback' => '::validateForm',
        'event' => 'change',
      ],
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email:'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::validateForm',
        'event' => 'input',
      ],
      '#decription' => $this->t("Email is allowed for example: example@mail.com
      "),
      '#validators' => [
        'email',
      ],
      '#filters' => [
        'lowercase',
      ],
    ];
    $form['tel_number'] = [
      '#type' => 'tel',
      '#title' => $this
        ->t("Your phone number:"),
      '#required' => TRUE,
      '#pattern' => '^\+?\d{10,15}$',
      '#ajax' => [
        'callback' => '::validateForm',
        'event' => 'change',
      ],
    ];
    $form['avatar'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('User avatar'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2 * 1024 * 1024],
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://cats_images/',
    ];
    $form['review_picture'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Review picture'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5 * 1024 * 1024],
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://cats_images/',
    ];
    $form['review_text'] = [
      '#type' => 'textarea',
      '#title' => $this
        ->t("Text your review:"),
      '#required' => TRUE,
    ];
    $form['send_review'] = [
      '#type' => 'submit',
      '#value' => $this
        ->t('Send'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    echo 'fff';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $database = \Drupal::service('database');
    $url_image = ['avatar' => '', 'image_review' => ''];
    foreach ($url_image as $field => $url) {
      $fieldValue = $form_state->getValue($field);
      if (!empty($fieldValue)) {
        $file = File::load(reset($fieldValue));
        $file->setPermanent();
        $file->save();
        $url_image[$field] = $file->getFilename();
      }
    }
    $result = $database->insert('quest_book')
      ->fields([
        'name' => $form_state->getValue("name"),
        'email' => $form_state->getValue('email'),
        'created' => \Drupal::time()->getRequestTime(),
        'avatar' => $url_image['avatar'],
        'image_review' => $url_image['image_review'],
        'review_text' => $form_state->getValue('review_text'),
        'tel_number' => $form_state->getValue('tel_number'),
      ])
      ->execute();
    if ($result) {
      $message = 'The review has been saved';
      $this
        ->messenger()
        ->addStatus($message);
    }
    else {
      $message = "Error, please repeat";
      $this
        ->messenger()
        ->addError($message);
    }
  }

}
