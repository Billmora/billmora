<?php

namespace App\View\Components\Client\Forms;

use Illuminate\View\Component;
use App\Services\CaptchaService;

class Captcha extends Component
{
    public string $form;

    public function __construct(string $form = '')
    {
        $this->form = $form;
    }

    public function shouldRender(): bool
    {
        return CaptchaService::enabled($this->form);
    }

    public function render()
    {
        return view('client::components.forms.captcha');
    }
}
