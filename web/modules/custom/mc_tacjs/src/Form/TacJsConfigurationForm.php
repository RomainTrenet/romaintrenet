<?php

namespace Drupal\mc_tacjs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures mc_tacjs’s settings.
 */
class TacJsConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'mc_tacjs_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'mc_tacjs.admin_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('mc_tacjs.admin_settings');

    $form['visibility'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Visibility', [], ['context' => 'MC TAC JS']),
      '#open' => TRUE,
    ];

    $form['visibility']['pages_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable tracking on certain pages', [], ['context' => 'MC TAC JS']),
      '#description' => $this->t('Choose whether to include or exclude the listed paths.'),
      '#options' => [
        'exclude' => $this->t('All pages except those listed', [], ['context' => 'MC TAC JS']),
        'include' => $this->t('Only the listed pages', [], ['context' => 'MC TAC JS']),
      ],
      '#default_value' => $config->get('pages_mode') ?? 'exclude',
    ];

    $form['visibility']['pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Paths', [], ['context' => 'MC TAC JS']),
      '#description' => $this->t(
        "Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard.\nExample paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page. Example: <br><code>/batch</code><br><code>/node/add*</code><br><code>/node/*/*</code><br><code>/user/*/*</code>",
        [
          '%blog' => '/blog',
          '%blog-wildcard' => '/blog/*',
          '%front' => '<front>',
        ]
      ),
      '#default_value' => $config->get('pages'),
    ];

    $form['visibility']['exclude_admin'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Exclude administration pages', [], ['context' => 'MC TAC JS']),
      '#default_value' => $config->get('exclude_admin') ?? TRUE,
    ];

    $form['tracking'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Tracking', [], ['context' => 'MC TAC JS']),
      '#open' => TRUE,
    ];

    $form['tracking']['description'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('These parameters are used to call a script « Tarte au citron ». This requires a script address, a domain, and a UUID.<br>The result is, for example: <i>https://tarteaucitron.io/load.js?domain=my.domain.fr&uuid=the_uuid<i>') . '</p>',
    ];

    $form['tracking']['script'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Script', [], ['context' => 'MC TAC JS']),
      '#description' => $this->t('The "Tarte au citron" script to call, usually <i>https://tarteaucitron.io/load.js</i>'),
      '#default_value' => $config->get('script'),
    ];

    $form['tracking']['domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Domain', [], ['context' => 'MC TAC JS']),
      '#default_value' => $config->get('domain'),
    ];

    $form['tracking']['uuid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('UUID', [], ['context' => 'MC TAC JS']),
      '#default_value' => $config->get('uuid'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save settings'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('mc_tacjs.admin_settings')
      ->set('pages', $form_state->getValue('pages'))
      ->set('pages_mode', $form_state->getValue('pages_mode'))
      ->set('exclude_admin', $form_state->getValue('exclude_admin'))
      ->set('script', $form_state->getValue('script'))
      ->set('domain', $form_state->getValue('domain'))
      ->set('uuid', $form_state->getValue('uuid'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
