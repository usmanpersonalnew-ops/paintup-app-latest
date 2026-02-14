<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    /**
     * Display a listing of inquiries.
     */
    public function index(): Response
    {
        $inquiries = Inquiry::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Inquiries/Index', [
            'inquiries' => $inquiries,
        ]);
    }

    /**
     * Book a home visit for the inquiry - converts lead to project.
     */
    public function bookVisit($id): RedirectResponse
    {
        $inquiry = Inquiry::findOrFail($id);

        // Create a new Project from the Inquiry
        $project = Project::create([
            'client_name' => $inquiry->name,
            'phone' => $inquiry->phone,
            'location' => $inquiry->city ?? 'Unknown',
            'status' => 'LEAD',
        ]);

        // Update inquiry status
        $inquiry->update(['status' => Inquiry::STATUS_VISIT_BOOKED]);

        // TODO: Send WhatsApp via Msg91
        // This should send a message to the client confirming the home visit

        return redirect()->route('admin.projects.edit', $project->id);
    }
}