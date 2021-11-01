<?php

namespace Drupal\quest_book\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\file\Entity\File;

/**
 * Configure quest_book settings for this site.
 */
class EditReview extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quest_book_edit';
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
    $form['edit_id'] = [
      '#type' => 'hidden',
      '#prefix' => '<div class="modal-body">',
      '#attributes' => [
        'class' => [
          'js-hidden-id',
        ],
      ],
    ];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t("Your name:"),
      '#description' => $this->t("Min 2 and max 100 characters"),
      '#attributes' => [
        'min-length' => '2',
        'max-length' => '100',
      ],
      '#ajax' => [
        'callback' => '::validateForm',
        'event' => 'change',
        'effect' => 'fade',
      ],
    ];
    $form['name_error'] = [
      '#markup' => '<span id="name-edit"></span>',
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email:'),
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
      '#markup' => '<span id="email-edit"></span>',
    ];
    $form['tel_number'] = [
      '#type' => 'tel',
      '#title' => $this
        ->t("Your phone number:"),
      '#pattern' => '^\+?\d{10,15}$',
      '#ajax' => [
        'callback' => '::validateTel',
        'event' => 'change',
        'disable-refocus' => FALSE,
        'effect' => 'fade',
      ],
    ];
    $form['tel_error'] = [
      '#markup' => '<span id="tel-edit"></span>',
    ];
    $form['review_text'] = [
      '#type' => 'textarea',
      '#title' => $this
        ->t("Text your review:"),
    ];
    $form['review_error'] = [
      '#markup' => '<span id="review-edit"></span>',
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
      '#upload_location' => 'public://avatar/',
    ];
    $form['avatar-thumb'] = [
      '#markup' => '<div id="avatar-js-thumb"></div>',
    ];
    $form['delete_avatar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete Avatar'),
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
      '#upload_location' => 'public://review_picture/',
    ];
    $form['image_thumb'] = [
      '#markup' => '<div id="image-js-thumb"></div>',
    ];

    $form['delete_picture'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Delete review picture'),
    ];
    $form['error-label'] = [
      '#markup' => '<span id="error-edit" class="text-danger label-alert"></span>',
      '#suffix' => '</div>',
    ];
    $form['cancel-modal'] = [
      '#markup' => '<button type="button" class="button" data-bs-dismiss="modal">Cancel</button>',
      '#prefix' => '<div class="modal-footer">',
    ];
    $form['edit_cat'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::submitForm',
        'event' => 'click',
      ],
    ];
    return $form;
  }

  /**
   * Validate name user ajax.
   */
  public function validateName(array &$form, FormStateInterface $form_state) {
    $len_name = strlen($form_state->getValue('name'));
    return ($len_name < 2) ? $this->setCommand($len_name < 2, "#name-edit", "<span class='text-danger'>The user name is to short.</span>") : $this->setCommand($len_name < 100, "#name-edit", "<span class='text-danger'>The user name is to short.</span>");
  }

  /**
   * Validate email ajax.
   */
  public function validateEmail(array &$form, FormStateInterface $form_state) {
    return $this->setCommand(!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL), '#email-edit', '<span class="text-danger">Email is not valid. Please enter a valid email.</span>');
  }

  /**
   * Validate tel number ajax.
   */
  public function validateTel(array &$form, FormStateInterface $form_state) {
    return $this->setCommand(!preg_match('^\+?\d{10,15}$c', $form_state->getValue('tel_number')), "#tel-edit", '<span class="text-danger">Phone number is not valid. Please enter a valid phone number</span>');
  }

  /**
   * Validate user review.
   */
  public function validateReview(array &$form, FormStateInterface $form_state) {
    return $this->setCommand(!$form_state->getValue('review_text'), '#review-edit', 'Please enter a review text');
  }

  /**
   * Add htmlcommand to response ajax.
   *
   * @param bool $condition
   *   Condition for setcommand.
   * @param string $selector
   *   Selector where replace html.
   * @param string $text
   *   Text for error.
   */
  private function setCommand($condition, string $selector, string $text) {
    $response = new AjaxResponse();
    if ($condition) {
      return $response->addCommand(new HtmlCommand($selector, $text));
    }
    else {
      return $response->addCommand(new HtmlCommand($selector, $text));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return $this->validateName($form, $form_state) || $this->validateEmail($form, $form_state) || $this->validateTel($form, $form_state) || $this->validateReview($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $connection = \Drupal::service('database');
    $image = [
      'review_picture' => NULL,
      'avatar' => NULL,
    ];
    foreach ($image as $field => $img) {
      if ($form_state->getValue($field)) {
        $image[$field] = "";
        continue;
      }
      $image_edit = $form_state->getValue($field);
      $file = File::load(reset($image_edit));
      $file->setPermanent();
      $file->save();
      $image[$field] = $file->getFilename();
    }
    $valueList = [
      'name' => $form_state->getValue('name'),
      'email' => $form_state->getValue('email'),
      'review_text' => $form_state->getValue('review_text'),
      'review_picture' => $image['review_picture'],
      'avatar' => $image['avatar'],
      'tel_number' => $form_state->getValue('tel_number'),
    ];
    foreach ($valueList as $field => $value) {
      if (empty($value)) {
        continue;
      }
      $connection->update('deku')
        ->fields([
          $field => $value,
        ])
        ->condition('id', $form_state->getValue('edit_id'))
        ->execute();
      if ($field == 'review_picture' || $field == 'avatar') {
        return $response->addCommand(new HtmlCommand(
          '#' . $field . '-' . $form_state->getValue('edit_id') . '-ajax',
          '<a href="' . $form_state->getValue($field) . '">
          <img src="' . $form_state->getValue($field) . '" width="120px" alt="' . $form_state->getValue('name') . '">
        </a>'
        ));
      }
      else {
        return $response->addCommand(new HtmlCommand(
          '#' . $field . '-' . $form_state->getValue('edit_id') . '-ajax',
          $value
        ));
      }
    }
  }

}
