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
    ];
    while ($result = $review->fetchAssoc()) {
      $build['reviews']['#rows'][$result['id']] = [
        'id' => '1',
        'name' => '1',
        'email' => '2',
        'tel_number' => '4',
        'date' => '6',
      ];
    }
    return $build;
  }

  /**
   * Get from database data for reviews page.
   */
  private function getReviews() {
    $database = \Drupal::service('database');

    return $database
      ->select('quest_book', 'qb')
      ->fields('qb', ['name', 'tel_number', 'email', 'review_text', 'created'])
      ->orderBy('created')
      ->execute();
  }

}
