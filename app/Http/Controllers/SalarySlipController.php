<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\SalarySlip;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\FinancialYear;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SalarySlipMail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;


class SalarySlipController extends Controller
{
    public function index(Request $request)
    {
        $employeeQuery = Employee::select('id', 'name')->where('created_by', \Auth::user()->creatorId());
        $employeeList = $employeeQuery->orderBy('name', 'asc')->pluck('name', 'id');

        $financialYears = FinancialYear::orderBy('start_date', 'desc')
            ->pluck(DB::raw("CONCAT(YEAR(start_date), '-', YEAR(end_date))"), 'id');

        // ✅ Get selected financial year
        $selectedFY = $request->financial_year_id ?? FinancialYear::where('is_active', 1)->value('id');
        $financialYear = FinancialYear::find($selectedFY);

        if (!$financialYear) {
            return redirect()->back()->with('error', __('Financial Year not found.'));
        }

        $start = Carbon::parse($financialYear->start_date);
        $end = Carbon::parse($financialYear->end_date);

        // Build month-year filter array (e.g. April 2025 to March 2026)
        $months = [];
        $cursor = $start->copy();
        while ($cursor <= $end) {
            $months[] = [
                'month' => $cursor->format('F'),      // e.g. 'April'
                'year' => $cursor->format('Y'),       // e.g. '2025'
            ];
            $cursor->addMonth();
        }

        $salarySlipsQuery = SalarySlip::with('employees');

        // 👤 Filter by employee if selected
        if ($request->filled('employee_id')) {
            $salarySlipsQuery->where('employee_id', $request->employee_id);
        } elseif (\Auth::user()->type == 'employee') {
            $salarySlipsQuery->where('employee_id', \Auth::user()->employee->id);
        }

        // 🗓️ Filter by financial year (month + year combinations)
        $salarySlipsQuery->where(function ($query) use ($months) {
            foreach ($months as $pair) {
                $query->orWhere(function ($subQuery) use ($pair) {
                    $subQuery->where('month', $pair['month'])
                             ->where('year', $pair['year']);
                });
            }
        });

        $salarySlips = $salarySlipsQuery->orderBy('year')->orderByRaw("FIELD(month, 'January','February','March','April','May','June','July','August','September','October','November','December')")
            ->get();

        return view('salary_slips.index', compact('salarySlips', 'employeeList', 'financialYears', 'selectedFY'));
    }


    public function create()
    {
        $employees = Employee::pluck('name', 'id');

        return view('salary_slips.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employees' => 'required|exists:users,id',
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'month' => 'required|string',
            'salary_slip' => 'required|mimes:jpg,png,pdf|max:2048',
        ]);

        // Define the destination directory (directly inside 'public/')
        $directory = public_path('uploads/salary-slips');

        // Ensure the directory exists
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true); // Create directory with full permissions
        }

        // Generate a random file name with original extension
        $file = $request->file('salary_slip');
        $randomFileName = Str::random(20) . '.' . $file->getClientOriginalExtension();

        // Move the file to 'public/uploads/salary-slips'
        $file->move($directory, $randomFileName);

        // Get employee details
        $employee = Employee::findOrFail($request->employees);

        // Save to database (Only store the file name)
        $salarySlip = SalarySlip::create([
            'employee_id' => $request->employees,
            'year' => $request->year,
            'month' => $request->month,
            'file_path' => $randomFileName, // Save only the file name, not full path
        ]);

        // Send email with attachment
        Mail::to($employee->email)->send(new SalarySlipMail($employee, $request->month, $request->year, $randomFileName));

        return redirect()->route('salary_slips.index')->with('success', 'Salary slip uploaded and email sent successfully.');
    }


    public function destroy(SalarySlip $salarySlip)
    {
        // Ensure the file path is correct
        if ($salarySlip->file_path) {
            $filePath = public_path('uploads/salary-slips/' . $salarySlip->file_path); // Correct location

            // Check if the file exists before attempting to delete
            if (file_exists($filePath)) {
                unlink($filePath); // Delete the file
            }
        }

        // Delete the salary slip record from the database
        $salarySlip->delete();

        return redirect()->route('salary_slips.index')->with('success', 'Salary slip deleted successfully.');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'salary_slips' => 'required|array',
            'salary_slips.*' => 'mimes:jpg,png,pdf|max:2048',
            'month' => 'required|string',
            'year' => 'required|integer|min:2000|max:' . date('Y'),
        ]);

        $directory = public_path('uploads/temp-salary-slips');

        // ✅ Delete old preview files before starting
        if (File::exists($directory)) {
            $files = File::files($directory);
            foreach ($files as $file) {
                File::delete($file); // Remove each file
            }
        } else {
            // Ensure the directory exists
            mkdir($directory, 0777, true);
        }

        $previewData = [];
        foreach ($request->file('salary_slips') as $file) {
            $originalFileName = $file->getClientOriginalName(); // Example: "Janki_Desai_JAN-25.pdf"

            // ✅ Extract name parts from filename
            $nameParts = explode('_', $originalFileName);
            $firstName = ucfirst($nameParts[0] ?? '');
            $lastName = count($nameParts) >= 3 ? ucfirst($nameParts[1] ?? '') : null;

            // ✅ Search for employee
            if ($lastName) {
                $employee = Employee::where('name', 'LIKE', "%{$firstName}%")
                                    ->where('name', 'LIKE', "%{$lastName}%")
                                    ->first();
            } else {
                $employee = Employee::where('name', 'LIKE', "%{$firstName}%")->first();
            }

            // ✅ Check if salary slip already exists
            $isDuplicate = false;
            if ($employee) {
                $existingSlip = SalarySlip::where('employee_id', $employee->id)
                    ->where('year', $request->year)
                    ->where('month', $request->month)
                    ->exists();
                $isDuplicate = $existingSlip ? true : false;
            }

            // ✅ Store temporary file
            $tempFileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $tempFileName);

            $previewData[] = [
                'original_name' => $originalFileName,
                'employee_name' => $lastName ? "{$firstName} {$lastName}" : $firstName,
                'employee' => $employee,
                'file_path' => "public/uploads/temp-salary-slips/{$tempFileName}",
                'is_duplicate' => $isDuplicate,
            ];
        }

        // ✅ Store preview data in session
        Session::put('salary_slip_preview', [
            'data' => $previewData,
            'month' => $request->month,
            'year' => $request->year,
        ]);

        return view('salary_slips.preview', compact('previewData'));
    }

    public function deletePreview(Request $request)
    {
        $index = $request->input('index');

        if (Session::has('salary_slip_preview')) {
            $previewData = Session::get('salary_slip_preview');

            if (isset($previewData['data'][$index])) {
                // ✅ Fix public path duplication
                $filePath = public_path(str_replace('public/', '', $previewData['data'][$index]['file_path']));

                // ✅ Delete the file if it exists
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }

                // ✅ Remove the record from session
                unset($previewData['data'][$index]);

                // ✅ Re-index array and update session
                $previewData['data'] = array_values($previewData['data']);
                Session::put('salary_slip_preview', $previewData);

                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false]);
    }

    public function confirm()
    {
        if (!Session::has('salary_slip_preview')) {
            return redirect()->route('salary_slips.index')->with('error', 'No salary slips to process.');
        }

        $previewData = Session::get('salary_slip_preview');
        $month = $previewData['month'];
        $year = $previewData['year'];
        $processedCount = 0;
        $skippedCount = 0;

        foreach ($previewData['data'] as $slip) {
            if (!$slip['employee']) {
                continue; // Skip unmatched employees
            }

            $employee = $slip['employee'];
            $filePath = str_replace('temp-salary-slips', 'salary-slips', str_replace('public/', '', $slip['file_path']));

            // ✅ Check if salary slip already exists for this employee, month, and year
            $existingSlip = SalarySlip::where('employee_id', $employee->id)
                ->where('year', $year)
                ->where('month', $month)
                ->exists();

            if ($existingSlip) {
                $skippedCount++;
                continue; // Skip duplicate records
            }

            // ✅ Move file to final directory
            File::move(public_path(str_replace('public/', '', $slip['file_path'])), public_path($filePath));

            // ✅ Save new salary slip in database
            $salarySlip = SalarySlip::create([
                'employee_id' => $employee->id,
                'year' => $year,
                'month' => $month,
                'file_path' => basename($filePath),
            ]);

            $processedCount++;

            // ✅ Send Email Notification with Attachment
            try {
                Mail::to($employee->email)
                    ->send(new SalarySlipMail($employee, $month, $year, $salarySlip->file_path));
            } catch (\Exception $e) {
                \Log::error("Salary Slip Email Error: " . $e->getMessage());
            }
        }

        // ✅ Clear session after processing
        Session::forget('salary_slip_preview');

        return redirect()->route('salary_slips.index')->with('success', 
            "Salary slips uploaded successfully. Processed: {$processedCount}, Skipped (Duplicates): {$skippedCount}."
        );
    }

    public function download($id)
    {
        // Find the salary slip record
        $salarySlip = SalarySlip::with('employees')->findOrFail($id);

        // Extract first name from full name
        $fullName = $salarySlip->employees->name; // Example: "Sunny .H. Macwan"
        $employeeName = ucfirst(strtok($fullName, ' ')); // Extracts "Sunny"

        // Format month and year
        $month = strtoupper(substr($salarySlip->month, 0, 3)); // "January" -> "JAN"
        $year = substr($salarySlip->year, -2); // "2025" -> "25"

        // Construct new file name pattern: "Sunny_JAN_25.pdf"
        $extension = pathinfo($salarySlip->file_path, PATHINFO_EXTENSION);
        $newFileName = "{$employeeName}_{$month}_{$year}.{$extension}";

        // Define the correct file path (public/uploads/salary-slips/)
        $filePath = public_path("uploads/salary-slips/{$salarySlip->file_path}");

        // Check if file exists
        if (file_exists($filePath)) {
            return response()->download($filePath, $newFileName);
        } else {
            return redirect()->back()->with('error', 'File not found.');
        }
    }
}
