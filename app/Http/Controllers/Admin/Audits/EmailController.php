<?php

namespace App\Http\Controllers\Admin\Audits;

use Billmora;
use App\Http\Controllers\Controller;
use App\Mail\BroadcastMail;
use App\Mail\TemplateMail;
use App\Models\AuditEmail;
use App\Models\MailBroadcast;
use App\Models\MailTemplate;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing email audit histories.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:audit.email.history.view')->only(['index', 'show', 'preview']);
        $this->middleware('permission:audit.email.history.export')->only(['export']);
        $this->middleware('permission:audit.email.history.delete')->only(['clear']);
    }

    /**
     * Display a paginated list of email audit histories.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->query('searchHistoryMail');

        $histories = AuditEmail::select('id', 'event', 'user_id', 'to', 'status', 'created_at')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('event', 'like', "%{$search}%")
                    ->orWhere('to', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();
        
        return view('admin::audits.email.index', compact('histories'));
    }

    /**
     * Display the details of a specific email audit history.
     *
     * @param int $id The ID of the email audit record.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($id)
    {
        $history = AuditEmail::findOrFail($id);

        return view('admin::audits.email.show', compact('history'));
    }

    /**
     * Preview the content of a specific email audit history.
     *
     * @param int $id The ID of the email audit record to preview.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function preview($id)
    {
        $history = AuditEmail::findOrFail($id);

        if ($history->event === 'broadcast_mail') {
            $broadcast = MailBroadcast::findOrFail($history->properties['id']);
            $mailable = new BroadcastMail($broadcast);
        } else {
            $template = MailTemplate::where('key', $history->event)->firstOrFail();
            $mailable = new TemplateMail(
                $template->key,
                $template->placeholder,
            );
        }

        $content = $mailable->content();

        return view($content->view, $content->with);
    }

    /**
     * Export all email audit histories as a JSON file.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export()
    {
        $companyName = Billmora::getGeneral('company_name');
        $nowDate = now()->format('Ymd_His');

        $histories = AuditEmail::all();
        $filename = "{$companyName}_audit-email-history-{$nowDate}.json";

        $json = $histories->toJson(JSON_PRETTY_PRINT);

        return response()->streamDownload(function() use ($json) {
            echo $json;
        }, $filename, ['Content-Type' => 'application/json']);
    }

    /**
     * Clear all email audit histories.
     *
     * @param \Illuminate\Http\Request $request The current HTTP request instance.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear()
    {
        $this->recordDelete('email.history.clear',[
            'count' => AuditEmail::count()
        ]);
        
        AuditEmail::truncate();

        return redirect()->back()->with('success', __('common.clear_success', ['attribute' => __('admin/audits/email.title')]));
    }
}
