<?php

namespace Drupal\quest_book\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for quest_book routes.
 */
class QuestBookController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
