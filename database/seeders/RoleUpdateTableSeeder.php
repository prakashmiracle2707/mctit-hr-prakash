<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RoleUpdateTableSeeder extends Seeder
{
    public function run()
    {
        $companyPermissions = [
            'Manage User', 'Create User', 'Edit User', 'Delete User',
            'Manage Role', 'Create Role', 'Delete Role', 'Edit Role',
            'Manage Award', 'Create Award', 'Delete Award', 'Edit Award',
            'Manage Transfer', 'Create Transfer', 'Delete Transfer', 'Edit Transfer',
            'Manage Resignation', 'Create Resignation', 'Edit Resignation', 'Delete Resignation',
            'Manage Travel', 'Create Travel', 'Edit Travel', 'Delete Travel',
            'Manage Promotion', 'Create Promotion', 'Edit Promotion', 'Delete Promotion',
            'Manage Complaint', 'Create Complaint', 'Edit Complaint', 'Delete Complaint',
            'Manage Warning', 'Create Warning', 'Edit Warning', 'Delete Warning',
            'Manage Termination', 'Create Termination', 'Edit Termination', 'Delete Termination',
            'Manage Department', 'Create Department', 'Edit Department', 'Delete Department',
            'Manage Designation', 'Create Designation', 'Edit Designation', 'Delete Designation',
            'Manage Document Type', 'Create Document Type', 'Edit Document Type', 'Delete Document Type',
            'Manage Branch', 'Create Branch', 'Edit Branch', 'Delete Branch',
            'Manage Award Type', 'Create Award Type', 'Edit Award Type', 'Delete Award Type',
            'Manage Termination Type', 'Create Termination Type', 'Edit Termination Type', 'Delete Termination Type',
            'Manage Employee', 'Create Employee', 'Edit Employee', 'Delete Employee', 'Show Employee',
            'Manage Payslip Type', 'Create Payslip Type', 'Edit Payslip Type', 'Delete Payslip Type',
            'Manage Allowance Option', 'Create Allowance Option', 'Edit Allowance Option', 'Delete Allowance Option',
            'Manage Loan Option', 'Create Loan Option', 'Edit Loan Option', 'Delete Loan Option',
            'Manage Deduction Option', 'Create Deduction Option', 'Edit Deduction Option', 'Delete Deduction Option',
            'Manage Set Salary', 'Create Set Salary', 'Edit Set Salary', 'Delete Set Salary',
            'Manage Allowance', 'Create Allowance', 'Edit Allowance', 'Delete Allowance',
            'Create Commission', 'Create Loan', 'Create Saturation Deduction', 'Create Other Payment', 'Create Overtime',
            'Edit Commission', 'Delete Commission', 'Edit Loan', 'Delete Loan',
            'Edit Saturation Deduction', 'Delete Saturation Deduction', 'Edit Other Payment', 'Delete Other Payment',
            'Edit Overtime', 'Delete Overtime', 'Manage Pay Slip', 'Create Pay Slip', 'Edit Pay Slip', 'Delete Pay Slip',
            'Manage Account List', 'Create Account List', 'Edit Account List', 'Delete Account List',
            'View Balance Account List', 'Manage Payee', 'Create Payee', 'Edit Payee', 'Delete Payee',
            'Manage Payer', 'Create Payer', 'Edit Payer', 'Delete Payer',
            'Manage Expense Type', 'Create Expense Type', 'Edit Expense Type', 'Delete Expense Type',
            'Manage Income Type', 'Edit Income Type', 'Delete Income Type', 'Create Income Type',
            'Manage Payment Type', 'Create Payment Type', 'Edit Payment Type', 'Delete Payment Type',
            'Manage Deposit', 'Create Deposit', 'Edit Deposit', 'Delete Deposit',
            'Manage Expense', 'Create Expense', 'Edit Expense', 'Delete Expense',
            'Manage Transfer Balance', 'Create Transfer Balance', 'Edit Transfer Balance', 'Delete Transfer Balance',
            'Manage Event', 'Create Event', 'Edit Event', 'Delete Event',
            'Manage Announcement', 'Create Announcement', 'Edit Announcement', 'Delete Announcement',
            'Manage Leave Type', 'Create Leave Type', 'Edit Leave Type', 'Delete Leave Type',
            'Manage Leave', 'Create Leave', 'Edit Leave', 'Delete Leave',
            'Manage Meeting', 'Create Meeting', 'Edit Meeting', 'Delete Meeting',
            'Manage Ticket', 'Create Ticket', 'Edit Ticket', 'Delete Ticket',
            'Manage Attendance', 'Create Attendance', 'Edit Attendance', 'Delete Attendance',
            'Manage Language', 'Create Language', 'Manage Plan', 'Buy Plan',
            'Manage Company Settings', 'Manage System Settings', 'Manage TimeSheet',
            'Create TimeSheet', 'Edit TimeSheet', 'Delete TimeSheet', 'Manage Order',
            'Manage Assets', 'Create Assets', 'Edit Assets', 'Delete Assets',
            'Manage Document', 'Create Document', 'Edit Document', 'Delete Document',
            'Manage Employee Profile', 'Show Employee Profile', 'Manage Employee Last Login',
            'Manage Indicator', 'Create Indicator', 'Edit Indicator', 'Delete Indicator', 'Show Indicator',
            'Manage Appraisal', 'Create Appraisal', 'Edit Appraisal', 'Delete Appraisal', 'Show Appraisal',
            'Manage Goal Type', 'Create Goal Type', 'Edit Goal Type', 'Delete Goal Type',
            'Manage Goal Tracking', 'Create Goal Tracking', 'Edit Goal Tracking', 'Delete Goal Tracking',
            'Manage Company Policy', 'Create Company Policy', 'Edit Company Policy', 'Delete Company Policy',
            'Manage Trainer', 'Create Trainer', 'Edit Trainer', 'Delete Trainer', 'Show Trainer',
            'Manage Training', 'Create Training', 'Edit Training', 'Delete Training', 'Show Training',
            'Manage Training Type', 'Create Training Type', 'Edit Training Type', 'Delete Training Type',
            'Manage Report', 'Manage Holiday', 'Create Holiday', 'Edit Holiday', 'Delete Holiday',
            'Manage Job Category', 'Create Job Category', 'Edit Job Category', 'Delete Job Category',
            'Manage Job Stage', 'Create Job Stage', 'Edit Job Stage', 'Delete Job Stage',
            'Manage Job', 'Create Job', 'Edit Job', 'Delete Job', 'Show Job',
            'Manage Job Application', 'Create Job Application', 'Edit Job Application', 'Delete Job Application', 'Show Job Application',
            'Move Job Application', 'Add Job Application Note', 'Delete Job Application Note', 'Add Job Application Skill',
            'Manage Job OnBoard', 'Manage Custom Question', 'Create Custom Question', 'Edit Custom Question', 'Delete Custom Question',
            'Manage Interview Schedule', 'Create Interview Schedule', 'Edit Interview Schedule', 'Delete Interview Schedule',
            'Manage Career', 'Manage Competencies', 'Create Competencies', 'Edit Competencies', 'Delete Competencies',
            'Manage Performance Type', 'Create Performance Type', 'Edit Performance Type', 'Delete Performance Type',
            'Manage Contract Type', 'Create Contract Type', 'Edit Contract Type', 'Delete Contract Type',
            'Manage Contract', 'Create Contract', 'Edit Contract', 'Delete Contract',
            'Store Note', 'Delete Note', 'Store Comment', 'Delete Comment', 'Delete Attachment',
            'Create Webhook', 'Edit Webhook', 'Delete Webhook',
            'Manage Zoom meeting', 'Create Zoom meeting', 'Show Zoom meeting', 'Delete Zoom meeting',
            'Manage Biometric Attendance', 'Biometric Attendance Synchronize',
            'Manage Project','Create Project','Edit Project','Delete Project',
        ];

        

        
        $companyRole = Role::firstOrCreate(['name' => 'company'], ['created_by' => 0]);

        
        $companyRole->syncPermissions($companyPermissions);

        
    }
}
