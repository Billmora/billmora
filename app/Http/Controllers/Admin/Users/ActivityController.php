<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\AuditUser;
use App\Models\User;
use Billmora;
use Illuminate\Http\Request;

class ActivityController extends Controller
{

    /**
     * Display a paginated list of user activity logs.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     * @param int $id The ID of the user.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function index(Request $request, $id)
    {
        $search = $request->query('searchHistoryMail');

        $user = User::findOrFail($id);

        $activities = AuditUser::select('id', 'event', 'user_id', 'created_at')
                            ->where('user_id', $user->id)
                            ->when($search, function ($query, $search) {
                                $query->where(function ($q) use ($search) {
                                    $q->where('event', 'like', "%{$search}%");
                                });
                            })
                            ->latest()
                            ->paginate(25)
                            ->withQueryString();
        
        return view('admin::users.activity.index', compact('user', 'activities'));
    }

    /**
     * Display the details of a specific user activity.
     *
     * @param int $id The ID of the user.
     * @param int $activity The ID of the activity log.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function show($id, $activity)
    {
        $user = User::findOrFail($id);

        $activity = AuditUser::where('id', $activity)
                            ->where('user_id', $user->id)
                            ->firstOrFail();
        
        return view('admin::users.activity.show', compact('user', 'activity'));
    }

    /**
     * Export all activity logs of a user as a JSON file.
     *
     * @param int $id The ID of the user.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function export($id)
    {
        $user = User::findOrFail($id);
        $companyName = Billmora::getGeneral('company_name');
        $nowDate = now()->format('Ymd_His');

        $activities = AuditUser::where('user_id', $user->id)->get();
        $filename = "{$companyName}_{$user->fullname}_audit-user-activity-{$nowDate}.json";

        $json = $activities->toJson(JSON_PRETTY_PRINT);

        return response()->streamDownload(function() use ($json) {
            echo $json;
        }, $filename, ['Content-Type' => 'application/json']);
    }


    /**
     * Clear all activity logs for a specific user.
     *
     * @param int $id The ID of the user.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function clear($id)
    {
        $user = User::findOrFail($id);

        AuditUser::where('user_id', $user->id)->delete();

        return redirect()->back()->with('success', __('common.clear_success', ['attribute' => __('admin/audits/user.title')]));
    }
}
