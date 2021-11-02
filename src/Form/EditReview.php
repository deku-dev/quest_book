<?php

namespace Drupal\quest_book\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Form Edit entry in database and validation.
 */
class EditReview extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'quest_book_edit';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['quest_book.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['edit_id'] = [
      '#type' => 'hidden',
      '#prefix' => '<div class="modal-body">',
      '#attributes' => [
        'class' => [
          'js-hidden-id',
        ],
      ],
    ];
    $form['name-edit'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t("Your name:"),
      '#description' => $this->t("Min 2 and max 100 characters"),
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
      '#markup' => '<span id="name-edit" class="text-danger"></span>',
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
      '#markup' => '<span id="email-edit" class="text-danger"></span>',
    ];
    $form['tel_number'] = [
      '#type' => 'tel',
      '#title' => $this
        ->t("Your phone number:"),
      '#pattern' => '/^\+?\d{10,15}$/',
      '#ajax' => [
        'callback' => '::validateTel',
        'event' => 'change',
        'disable-refocus' => FALSE,
        'effect' => 'fade',
      ],
    ];
    $form['tel_error'] = [
      '#markup' => '<span id="tel-edit" class="text-danger"></span>',
    ];
    $form['review_text'] = [
      '#type' => 'textarea',
      '#title' => $this
        ->t("Text your review:"),
    ];
    $form['review_error'] = [
      '#markup' => '<span id="review-edit" class="text-danger"></span>',
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
    // Checkbox for delete avatar.
    $form['delete-avatar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete Avatar'),
    ];
    $form['image_review'] = [
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
    // Checkbox for delete review picture.
    $form['delete-image_review'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Delete review picture'),
      '#suffix' => '</div>',
    ];
    // Button for hide modal window.
    $form['cancel-modal'] = [
      '#type' => 'button',
      '#value' => $this->t('Cancel'),
      '#attributes' => [
        'data-bs-dismiss' => 'modal',
        'class' => [
          'btn-danger',
        ],
      ],
      '#prefix' => '<div class="modal-footer">',
    ];
    // Send changes to php.
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
   * Validate name user ajax(min 2, max 100 characters).
   */
  public function validateName(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $len_name = strlen($form_state->getValue('name-edit'));
    if ($len_name < 2) {
      return $response->addCommand(new HtmlCommand('#name-edit', 'The user name is to short.'));
    }
    elseif ($len_name > 100) {
      return $response->addCommand(new HtmlCommand('#name-edit', 'The user name is to long.'));
    }
    else {
      return $response->addCommand(new HtmlCommand('#name-edit', ''));
    }
  }

  /**
   * Validate email ajax with standart method from php.
   */
  public function validateEmail(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    if (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      return $response->addCommand(new HtmlCommand('#email-edit', 'Email is not valid. Please enter a valid email.'));
    }
    else {
      return $response->addCommand(new HtmlCommand('#email-label', ''));
    }
  }

  /**
   * Validate tel number ajax (min 10, max 15, only number, "+"not necessarily).
   */
  public function validateTel(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    if (preg_match('/^\+?\d{10,15}$/', $form_state->getValue('tel_number'))) {
      return $response->addCommand(new HtmlCommand('#tel-edit', ''));
    }
    else {
      return $response->addCommand(new HtmlCommand('#tel-edit', 'Phone number is not valid. Please enter a valid phone number'));
    }
  }

  /**
   * Validate user review for entered value.
   */
  public function validateReview(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    if (!$form_state->getValue('review_text')) {
      return $response->addCommand(new HtmlCommand('#review-edit', 'Please enter a review text'));
    }
    else {
      return $response->addCommand(new HtmlCommand('#review-edit', ''));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): bool {
    return $this->validateName($form, $form_state) ||
      $this->validateEmail($form, $form_state) ||
      $this->validateTel($form, $form_state) ||
      $this->validateReview($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $connection = \Drupal::service('database');
    $image = [
      'image_review' => NULL,
      'avatar' => NULL,
    ];
    // Get name and prepare for add to database.
    foreach ($image as $field => $img) {
      if (!$form_state->getValue($field)) {
        continue;
      }
      if ($form_state->getValue('delete-' . $field)) {
        $image[$field] = "";
        // Default image for field.
        $replaceCell = $field == 'avatar' ? '<img src="/modules/custom/quest_book/icons/image-not-found.png" width="120px" alt="No Avatar">' : '';
        $response->addCommand(new HtmlCommand('#' . $field . '-' . $form_state->getValue('edit_id') . '-ajax', $replaceCell));
        continue;
      }
      $image_edit = $form_state->getValue($field);
      $file = File::load(reset($image_edit));
      $file->setPermanent();
      $file->save();
      $image[$field] = $file->getFilename();
    }
    // Array for all field in database.
    $valueList = [
      'name' => $form_state->getValue('name-edit'),
      'email' => $form_state->getValue('email'),
      'review_text' => $form_state->getValue('review_text'),
      'tel_number' => $form_state->getValue('tel_number'),
    ];
    foreach ($image as $field => $url) {
      if (is_null($url)) {
        continue;
      }
      $connection->update('quest_book')
        ->fields([
          $field => $url,
        ])
        ->condition('id', $form_state->getValue('edit_id'))
        ->execute();
      // Add response for image field.
      $folderImg = [
        'image_review' => '/sites/default/files/review_picture/',
        'avatar' => '/sites/default/files/avatar/',
      ];
      $response->addCommand(new HtmlCommand(
        '#' . $field . '-' . $form_state->getValue('edit_id') . '-ajax',
        '<a href="' . $folderImg[$field] . $url . '">
        <img src="' . $folderImg[$field] . $url . '" width="120px" alt="' . $form_state->getValue('name') . '">
      </a>'
      ));

    }
    // Cycle and update database field.
    foreach ($valueList as $field => $value) {
      // For empty field not update in database.
      if (empty($value)) {
        continue;
      }
      $connection->update('quest_book')
        ->fields([
          $field => $value,
        ])
        ->condition('id', $form_state->getValue('edit_id'))
        ->execute();
      // Response for all field but not image field.
      $response->addCommand(new HtmlCommand(
          '#' . $field . '-' . $form_state->getValue('edit_id') . '-ajax',
          $value
        ));
    }
    return $response;
  }

}
