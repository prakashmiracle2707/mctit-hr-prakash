<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Utility;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReimbursementApprovedMail;
use App\Mail\ReimbursementNotApprovedMail;
use App\Mail\ReimbursementRequestMail;
use App\Mail\ReimbursementPaidMail;

class ReimbursementController extends Controller
{

    // ✅ Admin Panel to See All Requests
    public function index()
    {
        $query = Reimbursement::with('employee', 'assignedUser')->latest();

        // Exclude Draft status for non-employees (Managers, CEO)
        if (\Auth::user()->type == 'employee') {
            $query->where('employee_id', Auth::id());
        } else {
            // For non-employees, exclude Draft status
            $query->where('status', '!=', 'Draft');
        }

        $reimbursements = $query->get();

        // echo "<pre>";print_r($reimbursements);exit;

        return view('reimbursements.index', compact('reimbursements'));
    }

    public function create()
    {
        // Only employees can create reimbursement requests
        if (Auth::user()->type !== 'employee') {
            return redirect()->route('reimbursements.index')->with('error', 'Only employees can submit reimbursement requests.');
        }

        return view('reimbursements.create');
    }

    // ✅ Manager updates status (Approve/Reject)

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'expense_date' => 'required|date',
            'file' => 'nullable|mimes:jpg,png,pdf,doc,docx|max:2048',
        ]);

        
        $directory = 'uploads/reimbursements';
        
        
        if (!file_exists(public_path($directory))) {
            mkdir(public_path($directory), 0777, true);
        }

        
        $fileName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = 'receipt_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($directory), $fileName); // Save in `public/uploads/reimbursements`
        }

        if($request->status == 'Pending'){
            $reimbursement = Reimbursement::create([
                'employee_id' => Auth::id(),
                'amount' => $request->amount,
                'title' => $request->title,
                'description' => $request->description,
                'remark' => $request->remark,
                'file_path' => $fileName, 
                'status' => $request->status,
                'expense_date' => $request->expense_date,
            ]);

            $toEmail = 'rmb@miraclecloud-technology.com';
            //$toEmail = 'mctsource@miraclecloud-technology.com';
            Mail::to($toEmail)->send(new ReimbursementRequestMail($reimbursement));
        }else{
            Reimbursement::create([
                'employee_id' => Auth::id(),
                'amount' => $request->amount,
                'title' => $request->title,
                'description' => $request->description,
                'remark' => $request->remark,
                'file_path' => $fileName, 
                'status' => $request->status,
                'expense_date' => $request->expense_date,
            ]);
        }
        

        return redirect()->route('reimbursements.index')->with('success', 'Reimbursement submitted successfully.');
    }

    public function edit($id)
    {
        $reimbursement = Reimbursement::findOrFail($id);

        return view('reimbursements.edit', compact('reimbursement'));
    }

    public function destroy($id)
    {
        $reimbursement = Reimbursement::findOrFail($id);

        if($reimbursement){
            if ($reimbursement->file_path != "") {
                $filePath = public_path('uploads/reimbursements/' . $reimbursement->file_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

           
            $reimbursement->delete();
        }
        

        return redirect()->route('reimbursements.index')->with('success', 'Reimbursement deleted successfully.');
    }

    public function action($id)
    {
        $reimbursement = Reimbursement::findOrFail($id);
        $employee = Employee::findOrFail($reimbursement->employee_id); // Assuming 'User' is the employee model

        return view('reimbursements.action', compact('employee', 'reimbursement'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'expense_date' => 'required|date',
            'file_path' => 'nullable|mimes:jpg,png,pdf,doc,docx|max:2048',
        ]);

        $reimbursement = Reimbursement::findOrFail($id);

        // Handle File Upload
        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/reimbursements'), $fileName);
            $reimbursement->file_path = $fileName;
        }

        // Update reimbursement record
        if($request->status == 'Pending'){
            $reimbursement->update([
                'title' => $request->title,
                'amount' => $request->amount,
                'description' => $request->description,
                'expense_date' => $request->expense_date,
                'status' => $request->status ?? 'Pending',
            ]);
            
            $toEmail = 'rmb@miraclecloud-technology.com';
            // $toEmail = 'mctsource@miraclecloud-technology.com';
            Mail::to($toEmail)->send(new ReimbursementRequestMail($reimbursement));
        }else{
            $reimbursement->update([
                'title' => $request->title,
                'amount' => $request->amount,
                'description' => $request->description,
                'expense_date' => $request->expense_date,
                'status' => $request->status ?? 'Pending'
            ]);
        }
        
        

        return redirect()->route('reimbursements.index')->with('success', 'Reimbursement updated successfully.');
    }

    public function changeaction(Request $request)
    {
        $settings = Utility::settings();

        date_default_timezone_set($settings['timezone']); // Set timezone
        // echo "<pre>";print_r($request->status);exit;

        $Reimbursement = Reimbursement::find($request->reimbursement_id);
        if ($request->status == 'Approved') {
            $Reimbursement->approved_at = Carbon::now();
            $Reimbursement->assign_to = Auth::id();
            $Reimbursement->remark = $request->remark;

            // Send Email
            Mail::to($Reimbursement->employee->email)->send(new ReimbursementApprovedMail($Reimbursement));
            
            
        }

        if ($request->status == 'Reject') {
            $Reimbursement->approved_at = Carbon::now();
            $Reimbursement->assign_to = Auth::id();
            $Reimbursement->remark = $request->remark;

            // Send Email
            Mail::to($Reimbursement->employee->email)->send(new ReimbursementNotApprovedMail($Reimbursement));
        }

        if ($request->status == 'Mark as Paid') {
            $Reimbursement->paid_at = Carbon::now();
            $Reimbursement->paid_by = Auth::id();
            $Reimbursement->status = 'Paid';

            // Ensure Payment Type is provided
            if (!$request->payment_type) {
                return redirect()->back()->with('error', __('Please select a Payment Type.'));
            }else{
                $Reimbursement->payment_type = $request->payment_type;
            }

            // Store Paid Receipt File
            if ($request->hasFile('paid_receipt')) {
                $file = $request->file('paid_receipt');
                $fileName = 'paid_receipt_' .time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/reimbursements'), $fileName);
                $Reimbursement->paid_receipt = $fileName;
            }

            // Send Email
            Mail::to($Reimbursement->employee->email)->send(new ReimbursementPaidMail($Reimbursement));
        }else{
            $Reimbursement->status = $request->status;
        }

        
        
        $Reimbursement->save();

        return redirect()->route('reimbursements.index')->with('success', __('Reimbursement status successfully updated.') . 
                ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? 
                '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
    }

}

