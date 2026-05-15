<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class OwnerApplicationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ((int) $user->role !== 2) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $existingApplication = DB::table('owner_applications')
            ->where('user_id', $user->id)
            ->first();

        if ($existingApplication && $existingApplication->status === 'pending') {
            return back()->with('error', 'Your owner application is already under review.');
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'ic_number' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postcode' => ['required', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:150'],
            'hosting_experience' => ['nullable', 'string', 'max:4000'],
            'ic_document' => [$existingApplication ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'supporting_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        DB::beginTransaction();

        try {
            $folder = public_path("assets/images/ownerApplications/{$user->id}");

            if (! File::exists($folder)) {
                File::makeDirectory($folder, 0755, true);
            }

            $icDocumentPath = $existingApplication->ic_document_path ?? null;
            $supportingDocumentPath = $existingApplication->supporting_document_path ?? null;

            if ($request->hasFile('ic_document')) {
                if ($icDocumentPath && File::exists(public_path($icDocumentPath))) {
                    File::delete(public_path($icDocumentPath));
                }

                $icFile = $request->file('ic_document');
                $icFilename = 'ic-document.' . $icFile->getClientOriginalExtension();
                $icFile->move($folder, $icFilename);
                $icDocumentPath = "assets/images/ownerApplications/{$user->id}/{$icFilename}";
            }

            if ($request->hasFile('supporting_document')) {
                if ($supportingDocumentPath && File::exists(public_path($supportingDocumentPath))) {
                    File::delete(public_path($supportingDocumentPath));
                }

                $supportingFile = $request->file('supporting_document');
                $supportingFilename = 'supporting-document.' . $supportingFile->getClientOriginalExtension();
                $supportingFile->move($folder, $supportingFilename);
                $supportingDocumentPath = "assets/images/ownerApplications/{$user->id}/{$supportingFilename}";
            }

            $payload = [
                'full_name' => $validated['full_name'],
                'phone_number' => $validated['phone_number'],
                'ic_number' => $validated['ic_number'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address_line' => $validated['address_line'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'occupation' => $validated['occupation'] ?? null,
                'hosting_experience' => $validated['hosting_experience'] ?? null,
                'ic_document_path' => $icDocumentPath,
                'supporting_document_path' => $supportingDocumentPath,
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'admin_notes' => null,
                'updated_at' => now(),
            ];

            if ($existingApplication) {
                DB::table('owner_applications')
                    ->where('user_id', $user->id)
                    ->update($payload);
            } else {
                DB::table('owner_applications')->insert($payload + [
                    'user_id' => $user->id,
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Your application to become a host has been submitted for admin review.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'We could not submit your application right now. ' . $e->getMessage());
        }
    }

    public function show(int $applicationId): View
    {
        $application = DB::table('owner_applications')
            ->join('users', 'owner_applications.user_id', '=', 'users.id')
            ->leftJoin('users as reviewers', 'owner_applications.reviewed_by', '=', 'reviewers.id')
            ->where('owner_applications.id', $applicationId)
            ->select(
                'owner_applications.*',
                'users.email',
                'users.role',
                'reviewers.name as reviewer_name'
            )
            ->first();

        abort_unless($application, 404);

        return view('profile.admins.owner_application_show', compact('application'));
    }

    public function approve(Request $request, int $applicationId): RedirectResponse
    {
        $application = DB::table('owner_applications')->where('id', $applicationId)->first();

        abort_unless($application, 404);

        DB::beginTransaction();

        try {
            DB::table('owner_applications')
                ->where('id', $applicationId)
                ->update([
                    'status' => 'approved',
                    'admin_notes' => $request->input('admin_notes'),
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('users')
                ->where('id', $application->user_id)
                ->update([
                    'role' => 3,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()
                ->route('admin.owner-applications.show', $applicationId)
                ->with('success', 'Owner application approved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Unable to approve application. ' . $e->getMessage());
        }
    }

    public function reject(Request $request, int $applicationId): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:2000'],
        ]);

        $application = DB::table('owner_applications')->where('id', $applicationId)->first();

        abort_unless($application, 404);

        DB::table('owner_applications')
            ->where('id', $applicationId)
            ->update([
                'status' => 'rejected',
                'admin_notes' => $request->input('admin_notes'),
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('users')
            ->where('id', $application->user_id)
            ->update([
                'role' => 2,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.owner-applications.show', $applicationId)
            ->with('success', 'Owner application rejected.');
    }
}
