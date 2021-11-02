<?php

namespace Drupal\quest_book\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;

/**
 * Returns responses for quest_book routes.
 */
class QuestBookController extends ControllerBase {

  /**
   * Build page quest book with form, all reviews, edit button.
   */
  public function buildQuestBook() {
    // Check user role for edit review.
    $access = User::load(\Drupal::currentUser()->id())->hasRole('administrator');
    // Get for edit review from class.
    $form_edit = \Drupal::formBuilder()->getForm('Drupal\quest_book\Form\EditReview');
    // Get for delete review.
    $form_delete = \Drupal::formBuilder()->getForm('Drupal\quest_book\Form\DeleteReview');
    // Get form for add review.
    $form_add = \Drupal::formBuilder()->getForm('Drupal\quest_book\Form\AddReview');
    return [
      '#theme' => 'reviews',
      '#reviews' => $this->getReviews(),
      '#form_del' => $form_delete,
      '#form_edit' => $form_edit,
      '#form_add' => $form_add,
      '#user_role' => $access,
    ];
  }

  /**
   * Get from database data for reviews page.
   */
  private function getReviews() {
    $database = \Drupal::service('database');
    return $database
      ->select('quest_book', 'qb')
      ->fields('qb',
        ['id', 'name', 'tel_number',
          'email', 'review_text', 'created', 'avatar', 'image_review',
        ])
      ->condition('id', 0, '<>')
      ->orderBy('created', 'DESC')
      ->execute()->fetchAll();
  }

}
