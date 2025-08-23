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
     * Show the form for editing a specific mail template.
     *
     * @param int $id The ID of the mail template.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the template is not found.
     */
    public function edit($id)
    {
        $template = MailTemplate::findOrFail($id);

        return view('admin::settings.mail.template.show', compact('template'));
    }

    /**
     * Update the specified mail template.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing template data.
     * @param int $id The ID of the mail template to update.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back to the template list with a success message.
     */
    public function update(Request $request, $id)
    {
        $template = MailTemplate::findOrFail($id);

        $validated = $request->validate([
            'template_subject' => ['required', 'string', 'max:255'],
            'template_body' => ['required', 'string'],
            'template_active' => ['required', 'boolean'],
        ]);

        $template->update([
            'subject' => $validated['template_subject'],
            'body' => $validated['template_body'],
            'active' => $validated['template_active'],
        ]);

        return redirect()->route('admin.settings.mail.template')->with('success', __('admin/common.save_success', ['item' => __('admin/settings/mail.title')]));
    }
}
