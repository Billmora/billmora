<?php

namespace App\Http\Controllers\Admin\Settings\Mail;

use App\Models\MailTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateController extends Controller
{

    /**
     * Display the mail templates table.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $templates = MailTemplate::select('id', 'key', 'name', 'active')->get();

        return view('admin::settings.mail.template.index', compact('templates'));
    }

    /**
     * Show the form for editing a specific mail template translation.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request, optionally containing a `lang` query parameter.
     * @param int $id The ID of the mail template to edit.
     *
     * @return \Illuminate\View\View The view displaying the mail template edit form with the chosen translation.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the mail template is not found.
     */
    public function edit(Request $request, $id)
    {
        $lang = $request->query('lang', config('app.fallback_locale'));

        $template = MailTemplate::with('translations')->findOrFail($id);

        $translation = $template->translations->where('lang', $lang)->first();

        $noTranslation = false;
        if (!$translation && $lang !== config('app.fallback_locale')) {
            $translation = $template->translations->where('lang', config('app.fallback_locale'))->first();
            $noTranslation = true;
        }

        return view('admin::settings.mail.template.show', compact('template', 'translation', 'noTranslation'));
    }

    /**
     * Update the specified mail template and its translation.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing template update data.
     * @param int $id The ID of the mail template to update.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success flash message.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the mail template is not found.
     */
    public function update(Request $request, $id)
    {
        $template = MailTemplate::findOrFail($id);

        $validated = $request->validate([
            'template_language' => ['required', 'string'],
            'template_subject' => ['required', 'string', 'max:255'],
            'template_body' => ['required', 'string'],
            'template_active' => ['required', 'boolean'],
        ]);

        $template->update([
            'active' => $validated['template_active'],
        ]);

        $template->translations()->updateOrCreate(
            ['lang' => $validated['template_language']],
            [
                'subject' => $validated['template_subject'],
                'body'    => $validated['template_body'],
            ]
        );

        return redirect()->route('admin.settings.mail.template')->with('success', __('admin/common.save_success', ['item' => __('admin/settings/mail.title')]));
    }
}
