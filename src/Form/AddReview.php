<?php

namespace Drupal\quest_book\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
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
        ->t("Your name:"),
      '#description' => $this->t("Min 2 and max 100 characters"),
      '#required' => TRUE,
      '#attributes' => [
        'min-length' => '2',
        'max-length' => '100',
      ],
      '#ajax' => [
        'callback' => '::validateName',
        'event' => 'change',
        'effect' => 'fade',
      ],
    ];
    $form['name_error'] = [
      '#markup' => '<span id="name-label" class="text-danger"></span>',
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email:'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::validateEmail',
        'event' => 'change',
        'disable-refocus' => FALSE,
        'effect' => 'fade',
      ],
      '#effect' => 'fade',
      '#decription' => $this->t("Email is allowed for example: example@mail.com
      "),
      '#progress' => [
        'type' => 'bar',
      ],
      '#validators' => [
        'email',
      ],
      '#filters' => [
        'lowercase',
      ],
    ];
    $form['email_error'] = [
      '#markup' => '<span id="email-label" class="text-danger"></span>',
    ];
    $form['tel_number'] = [
      '#type' => 'tel',
      '#title' => $this
        ->t("Your phone number:"),
      '#required' => TRUE,
      '#pattern' => '^\+?\d{10,15}$',
      '#ajax' => [
        'callback' => '::validateTel',
        'event' => 'change',
        'disable-refocus' => TRUE,
        'effect' => 'fade',
      ],
    ];
    $form['tel_error'] = [
      '#markup' => '<span id="tel-label" class="text-danger"></span>',
    ];
    $form['avatar'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('User avatar'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2 * 1024 * 1024],
      ],
      '#theme' => 'image_widget',
      '#upload_location' => 'public://avatar/',
      '#description' => $this->t("Upload image no more than 2mb"),
    ];
    $form['image_review'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Review picture'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5 * 1024 * 1024],
      ],
      '#theme' => 'image_widget',
      '#upload_location' => 'public://review_picture/',
      '#description' => $this->t("Upload image no more than 5mb"),
    ];
    $form['review_text'] = [
      '#type' => 'textarea',
      '#title' => $this
        ->t("Text your review:"),
      '#required' => TRUE,
    ];
    $form['review_error'] = [
      '#markup' => '<span id="review-label" class="text-danger"></span>',
    ];
    $form['send_review'] = [
      '#type' => 'submit',
      '#value' => $this
        ->t('Send'),
    ];
    return $form;
  }

  /**
   * Validate name user ajax.
   */
  public function validateName(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $len_name = strlen($form_state->getValue('name'));
    if ($len_name < 2) {
      return $response->addCommand(new HtmlCommand('#name-label', 'The user name is to short.'));
    }
    elseif ($len_name > 100) {
      return $response->addCommand(new HtmlCommand('#name-label', 'The user name is to long.'));
    }
    else {
      return $response->addCommand(new HtmlCommand('#name-label', ''));
    }
  }

  /**
   * Validate email ajax.
   */
  public function validateEmail(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      return $response->addCommand(new HtmlCommand('#email-label', 'Email is not valid. Please enter a valid email.'));
    }
    else {
      return $response->addCommand(new HtmlCommand('#email-label', ''));
    }
  }

  /**
   * Validate tel number ajax.
   */
  public function validateTel(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (!preg_match('/^\+?\d{10,15}$/', $form_state->getValue('tel_number'))) {
      return $response->addCommand(new HtmlCommand('#tel-label', 'Phone number is not valid. Please enter a valid phone number'));
    }
    else {
      return $response->addCommand(new HtmlCommand('#tel-label', ''));
    }
  }

  /**
   * Validate user review.
   */
  public function validateReview(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if (!$form_state->getValue('review_text')) {
      return $response->addCommand(new HtmlCommand('#review-label', 'Please enter a review text'));
    }
    else {
      return $response->addCommand(new HtmlCommand('#review-label', ''));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return $this->validateName($form, $form_state) || $this->validateEmail($form, $form_state) ||$this->validateTel($form, $form_state) || $this->validateReview($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $database = \Drupal::service('database');
    $url_image = ['avatar' => '', 'image_review' => ''];
    // Get url from field database.
    foreach ($url_image as $field => $url) {
      $fieldValue = $form_state->getValue($field);
      if (!empty($fieldValue)) {
        $file = File::load(reset($fieldValue));
        $file->setPermanent();
        $file->save();
        $url_image[$field] = $file->getFilename();
      }
    }
    // Save review in database.
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
