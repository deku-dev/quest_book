<?php

namespace Drupal\quest_book\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure quest_book settings for this site.
 */
class DeleteReview extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quest_book_delete';
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
    $form['delete_id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'class' => [
          'js-hidden-id',
        ],
      ],
    ];
    $form['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#attributes' => [
        'class' => [
          'btn-danger',
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $conn = \Drupal::database();
    $id_entry = $form_state->getValue('delete_id');
    foreach (explode(",", $id_entry) as $id_key) {
      $conn->delete('quest_book')
        ->condition('id', $id_key)
        ->execute();
    }
  }

}
