<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Stores a lead captured by one of the site's forms.
 *
 * A content entity rather than a node: these are operational records, not
 * published content, and they must not appear in /admin/content beside the
 * catalogue.
 *
 * @ContentEntityType(
 *   id = "contact_submission",
 *   label = @Translation("Yêu cầu liên hệ"),
 *   base_table = "contact_submission",
 *   handlers = {
 *     "list_builder" = "Drupal\keybolts_core\ContactSubmissionListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "view contact submissions",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "collection" = "/admin/keybolts/submissions",
 *   },
 * )
 */
class ContactSubmission extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Họ tên'))
      ->setRequired(TRUE);

    $fields['phone'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Số điện thoại'))
      ->setRequired(TRUE);

    $fields['message'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Nội dung'));

    $fields['source'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Nguồn'))
      ->setSetting('allowed_values', [
        'contact' => 'Liên hệ',
        'dealer' => 'Đăng ký đại lý',
        'consult' => 'Tư vấn',
      ])
      ->setDefaultValue('contact');

    $fields['ip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('IP'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Thời gian'));

    return $fields;
  }
}
