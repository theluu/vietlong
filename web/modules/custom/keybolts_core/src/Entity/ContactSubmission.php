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
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
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
 *     "canonical" = "/admin/keybolts/submissions/{contact_submission}",
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

    // No form collects this yet — every design ships name/phone/message only.
    // It exists so an email-capable form can be added without a schema change.
    $fields['email'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Email'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['message'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Nội dung'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['source'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Nguồn'))
      ->setSetting('allowed_values', [
        'contact' => 'Liên hệ',
        'dealer' => 'Đăng ký đại lý',
        'consult' => 'Tư vấn',
      ])
      ->setDefaultValue('contact');

    // Empty means the score is unknown — no key configured, or Google was
    // unreachable. It does not mean the visitor failed the check.
    $fields['recaptcha_score'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Điểm reCAPTCHA'))
      ->setSetting('precision', 3)
      ->setSetting('scale', 2)
      ->setDescription(t('Trống = không xác thực được (chưa cấu hình key hoặc Google không phản hồi).'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['ip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('IP'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Thời gian'));

    return $fields;
  }
}
