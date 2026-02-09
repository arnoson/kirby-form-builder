<?php

namespace Uniform\Actions;

use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Uniform\Actions\Action;

class BrevoAction extends Action {
  public function perform() {
    /** @var \Kirby\Cms\Page */
    $page = $this->option('page');

    $emailName = $page->brevo_email()->value();
    $email = $this->form->data($emailName);

    $attributes = [];
    foreach ($this->form->data() as $key => $value) {
      if ($value !== '' && preg_match('/^[a-zA-Z0-9_]+$/', (string) $key)) {
        $attributes[$key] = $value;
      }
    }

    $data = [
      'updateEnabled' => $page->brevo_updateEnabled()->toBool(),
      'email' => $email,
      'attributes' => $attributes,
    ];

    if ($doubleOptIn = $page->brevo_doubleOptIn()->toBool()) {
      $data = A::merge($data, [
        'includeListIds' => [$page->brevo_listId()->toInt()],
        'redirectionUrl' => $page->brevo_redirectionUrl()->value(),
        'templateId' => $page->brevo_templateId()->toInt(),
      ]);
    } else {
      // For some reason the field is named differently for non-doi.
      $data['listIds'] = [$page->brevo_listId()->toInt()];
    }

    $url = $doubleOptIn
      ? 'https://api.brevo.com/v3/contacts/doubleOptinConfirmation'
      : 'https://api.brevo.com/v3/contacts';

    $response = Remote::post($url, [
      'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Api-Key' => option('arnoson.kirby-form-builder.brevoApiKey'),
      ],
      'data' => json_encode($data),
    ]);

    if ($response->code() < 200 || $response->code() >= 300) {
      $error = json_decode($response->content())->message ?? 'unknown error';
      $message = t('arnoson.kirby-form-builder.brevo-error');
      $message = option('debug') ? "$message: $error" : $message;
      $this->fail($message);
    }
  }
}
