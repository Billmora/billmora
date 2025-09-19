<?php

namespace App\Http\Controllers\Admin\Settings\Mail;

use App\Models\MailTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateController extends Controller
{

    /**
     * Applies permission-based middleware for accessing mail template settings.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:settings.mail.template.view')->only('index');
        $this->middleware('permission:settings.mail.template.update')->only(['edit', 'update']);
    }

    /**
     * Display the mail templates table.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('searchTemplateMail');

        $templates = MailTemplate::select('id', 'key', 'name', 'active')
                                ->when($search, function ($query, $search) {
                                    $query->where(function ($q) use ($search) {
                                        $q->where('key', 'like', "%{$search}%")
                                        ->orWhere('name', 'like', "%{$search}%");
                                    });
                                })
                                ->paginate(25)
                                ->withQueryString();

        return view('admin::settings.mail.template.index', compact('templates', 'search'));
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

        return view('admin::settings.mail.template.edit', compact('template', 'translation', 'noTranslation'));
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
            'template_cc' => ['nullable', 'array'],
            'template_bcc' => ['nullable', 'array'],
        ]);

        $template->update([
            'active' => $validated['template_active'],
            'cc' => $validated['template_cc'],
            'bcc' => $validated['template_bcc'],
        ]);

        $template->translations()->updateOrCreate(
            ['lang' => $validated['template_language']],
            [
                'subject' => $validated['template_subject'],
                'body'    => $validated['template_body'],
            ]
        );

        return redirect()->route('admin.settings.mail.template')->with('success', __('common.save_success', ['attribute' => __('admin/settings/mail.title')]));
    }
}
