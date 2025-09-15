<?php

namespace App\Http\Controllers;

use App\Models\InboundContainerApproval;
use App\Models\InboundContainer; // assuming you already have a InboundContainer model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InboundContainerApprovalController extends Controller
{

    public function create(InboundContainer $container)
    {
        $approvals = InboundContainerApproval::where([['container_id', $container->id]])
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('container.approval', [
            'inboundcontainer' => $container,
            'approvals' => $approvals,
        ]);
    }

    public function store(Request $request, InboundContainer $container)
    {
        $validated = $request->validate([
            'file'     => 'nullable|file|max:20048', // 20MB max
            'approved' => 'required|boolean',
            'comments' => 'nullable',
        ]);
        $approval = new InboundContainerApproval();
        $approval->user_id   = Auth::id();
        $approval->approved = ($validated['approved']==1)?true:false;
        $approval->comments  = $validated['comments'];
        $approval->container_id = $container->id;

        // Handle file upload
        if ($request->hasFile('file')) {
            $file =  $request->file('file');
            $approval->file_id = FileController::PROCESS_ACTUAL_FILE($file)->id;
        }
        if ($approval->approved){
            $container->admin_approved = true;
            $container->save();
        }
        $approval->save();

        return redirect()
            ->route('inbound-approvals.create', $container)
            ->with('success', 'Approval created successfully.');
    }
}
