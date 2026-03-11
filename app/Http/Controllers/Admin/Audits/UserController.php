<?php

namespace App\Http\Controllers\Admin\Audits;

use Billmora;
use App\Http\Controllers\Controller;
use App\Models\AuditUser;
use App\Models\User;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use AuditsSystem;

    /**
     * Applies permission-based middleware for accessing user audit activities.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:audit.user.activity.view')->only(['index', 'show']);
        $this->middleware('permission:audit.user.activity.export')->only(['export']);
        $this->middleware('permission:audit.user.activity.delete')->only(['clear']);
    }

    /**
     * Display a paginated list of user activity logs.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     * @param int|null $id The ID of the user.
     *
     * @return \Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function index(Request $request, $id = null)
    {
        $search = $request->query('searchActiviyUser');

        $user = $id ? User::findOrFail($id) : null;

        $activities = AuditUser::with('user:id,email,first_name,last_name')
            ->select('id', 'event', 'user_id', 'created_at')
            ->when($id, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($search, function ($query, $search) {
                $query->where('event', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(Billmora::getGeneral('misc_admin_pagination'))
            ->withQueryString();

        if ($id) {
            return view('admin::users.activity.index', compact('user', 'activities'));
        } else {
            return view('admin::audits.user.index', compact('user', 'activities'));
        }
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
     * @param int int|null $id The ID of the user.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function export($id = null)
    {
        $companyName = Billmora::getGeneral('company_name');
        $nowDate = now()->format('Ymd_His');

        if ($id) {
            $user = User::findOrFail($id);
            $activities = AuditUser::where('user_id', $user->id)->get();
            $filename = "{$companyName}_{$user->fullname}_audit-user-activity-{$nowDate}.json";
        } else {
            $activities = AuditUser::with('user:id,email,first_name,last_name')->get();
            $filename = "{$companyName}_all-users_audit-activity-{$nowDate}.json";
        }

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

        $this->recordDelete('user.activity.clear', [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'count' => AuditUser::where('user_id', $user->id)->count(),
        ]);

        AuditUser::where('user_id', $user->id)->delete();

        return redirect()->back()->with('success', __('common.clear_success', ['attribute' => __('admin/audits/user.title')]));
    }
}
