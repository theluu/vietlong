<?php

declare(strict_types=1);

namespace Drupal\keybolts_core;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Newest first — an editor checking for new leads wants today's at the top.
 */
class ContactSubmissionListBuilder extends EntityListBuilder {

  protected function getEntityIds(): array {
    return $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort('created', 'DESC')
      ->pager(50)
      ->execute();
  }

  public function buildHeader(): array {
    return [
      'created' => $this->t('Thời gian'),
      'name' => $this->t('Họ tên'),
      'phone' => $this->t('Điện thoại'),
      'source' => $this->t('Nguồn'),
      'recaptcha_score' => $this->t('reCAPTCHA'),
      'message' => $this->t('Nội dung'),
    ] + parent::buildHeader();
  }

  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\keybolts_core\Entity\ContactSubmission $entity */
    return [
      'created' => \Drupal::service('date.formatter')->format((int) $entity->get('created')->value, 'short'),
      'name' => $entity->get('name')->value,
      'phone' => $entity->get('phone')->value,
      'source' => $entity->get('source')->value,
      'recaptcha_score' => $entity->get('recaptcha_score')->value ?? '—',
      'message' => mb_substr((string) $entity->get('message')->value, 0, 80),
    ] + parent::buildRow($entity);
  }
}
