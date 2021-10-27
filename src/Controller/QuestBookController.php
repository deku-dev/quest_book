<?php

namespace Drupal\quest_book\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for quest_book routes.
 */
class QuestBookController extends ControllerBase {

  /**
   * Build page quest book with form, all reviews, edit button.
   */
  public function buildQuestBook() {

    $review = $this->getReviews();
    $build['reviews'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Phone'),
        $this->t('Email'),
        $this->t('Text'),
        $this->t('Date'),
      ],
      '#rows' => $review,
    ];
    return $build;
  }

  /**
   * Get from database data for reviews page.
   */
  private function getReviews() {
    $database = \Drupal::service('database');
    $rows = [];
    $query = $database
      ->select('quest_book', 'qb')
      ->fields('qb',
        ['id', 'name', 'tel_number', 'email', 'review_text', 'created'])
      ->condition('id', 0, '<>')
      ->orderBy('created')
      ->execute()->fetchAll();
    foreach ($query as $value) {
      $rows[$value->id] = [
        'name' => $value->name,
        'tel_number' => $value->tel_number,
        'email' => $value->email,
        'text_review' => $value->review_text,
        'date' => $value->created,
      ];
    }
    return $rows;
  }

}
