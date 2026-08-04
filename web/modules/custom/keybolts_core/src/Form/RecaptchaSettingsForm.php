<?php

declare(strict_types=1);

namespace Drupal\keybolts_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\keybolts_core\Service\RecaptchaVerifier;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * reCAPTCHA v3 settings.
 *
 * The site key is public and lives in configuration. The secret key does NOT:
 * config/sync is committed to git, so a secret there would be published. It
 * goes to State, which stays in the database.
 */
final class RecaptchaSettingsForm extends ConfigFormBase {

  private const CONFIG = 'keybolts_core.recaptcha';
  private const SECRET_STATE = 'keybolts_core.recaptcha_secret';

  public function __construct(
    $config_factory,
    private readonly StateInterface $state,
    private readonly RecaptchaVerifier $verifier,
  ) {
    parent::__construct($config_factory);
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('state'),
      $container->get('keybolts_core.recaptcha'),
    );
  }

  public function getFormId(): string {
    return 'keybolts_recaptcha_settings';
  }

  protected function getEditableConfigNames(): array {
    return [self::CONFIG];
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config(self::CONFIG);
    $secret = (string) $this->state->get(self::SECRET_STATE, '');

    $form['help'] = [
      '#markup' => $this->t('<p>Lấy khoá tại <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin</a>. Nhớ thêm tên miền của website vào mục <em>Domains</em>, nếu không khách gửi form sẽ bị chặn.</p>'),
    ];

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Bật reCAPTCHA'),
      '#default_value' => $config->get('enabled') ?? TRUE,
      '#description' => $this->t('Tắt thì form vẫn gửi được, chỉ bỏ qua bước xác thực.'),
    ];

    $form['site_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site key'),
      '#default_value' => (string) $config->get('site_key'),
      '#description' => $this->t('Khoá công khai, nhúng vào trang. Bắt đầu bằng 6L…'),
      '#maxlength' => 255,
    ];

    $form['secret_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Secret key'),
      // Never render the stored secret back into HTML.
      '#description' => $secret
        ? $this->t('Đã lưu (kết thúc bằng …@suffix). Để trống nếu không muốn thay đổi.', ['@suffix' => substr($secret, -4)])
        : $this->t('Chưa cấu hình.'),
      '#maxlength' => 255,
    ];

    $form['threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('Ngưỡng điểm'),
      '#default_value' => $config->get('threshold') ?? 0.5,
      '#min' => 0,
      '#max' => 1,
      '#step' => 0.05,
      '#description' => $this->t('Điểm dưới ngưỡng bị coi là bot và chặn. Google trả 0 (nghi ngờ) đến 1 (tin cậy). Mặc định 0.5.'),
    ];

    $form['actions']['test'] = [
      '#type' => 'submit',
      '#value' => $this->t('Kiểm tra kết nối tới Google'),
      '#submit' => ['::testConnection'],
      '#limit_validation_errors' => [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Sends a deliberately invalid token. Google answering at all proves the
   * secret reaches them; a network error proves it does not.
   */
  public function testConnection(array &$form, FormStateInterface $form_state): void {
    if (!$this->verifier->isEnabled()) {
      $this->messenger()->addWarning($this->t('Chưa có secret key nên không kiểm tra được.'));
      return;
    }
    $score = $this->verifier->verify('test-token-from-admin-form', 'admin_test');
    if ($score === NULL) {
      $this->messenger()->addError($this->t('Không gọi được Google. Kiểm tra kết nối mạng của máy chủ.'));
      return;
    }
    $this->messenger()->addStatus($this->t('Gọi được Google. Secret key hợp lệ — token thử nghiệm bị từ chối đúng như mong đợi.'));
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(self::CONFIG)
      ->set('enabled', (bool) $form_state->getValue('enabled'))
      ->set('site_key', trim((string) $form_state->getValue('site_key')))
      ->set('threshold', (float) $form_state->getValue('threshold'))
      ->save();

    // Empty means "leave it alone", so a save never wipes a working secret.
    $secret = trim((string) $form_state->getValue('secret_key'));
    if ($secret !== '') {
      $this->state->set(self::SECRET_STATE, $secret);
      $this->messenger()->addStatus($this->t('Đã cập nhật secret key.'));
    }

    parent::submitForm($form, $form_state);
  }

}
