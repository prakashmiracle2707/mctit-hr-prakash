<?php

use Jenssegers\Agent\Agent;

if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'INR') {
        return number_format($amount, 2) . ' ' . $currency;
    }
}

if (!function_exists('generate_uuid')) {
    function generate_uuid() {
        return (string) \Illuminate\Support\Str::uuid();
    }
}

if (!function_exists('Get_LeaveId')) {
    function Get_LeaveId($LeaveId) {
        return "#L00".$LeaveId;
    }
}

if (!function_exists('Get_Device_Type')) {
    function Get_Device_Type() {
        $agent = new Agent();
        $device_type = $agent->isMobile() ? 'mobile' : 'desktop';
        return $device_type;
    }
}

if (!function_exists('Get_Device_Type_Icon')) {
    function Get_Device_Type_Icon($TypeIcon,$UserId) {
        if($TypeIcon == 'mobile' && ($UserId == 3 || $UserId == 1)){
            return "<i class='ti ti-device-mobile' style='color:red;' title='Mobile'></i>";
        }
        return false;
    }
}

if (!function_exists('GetStatusName')) {
    function GetStatusName($Status,$approved_type) {
        if ($Status == 'Pending'){
            return $Status;
        }
        else if ($Status == 'In_Process'){
            return "In-Process";
        }
        else if ($Status == 'Manager_Approved'){
            return "Awaiting Director Approval";
        }
        else if ($Status == 'Manager_Rejected'){
            return "Manager-Rejected";
        }
        else if ($Status == 'Partially_Approved'){
            return "Partially-Approved";
        }
        else if($Status == 'Approved'){
            if($approved_type == 'auto'){
                return "System – Auto Approved";
            } else {
                return $Status;
            }
        }else if($Status == "Reject"){
            return $Status;
        }
        else if($Status == "Draft"){
            return $Status;
        }
        else if($Status == "Cancelled"){
            return $Status;
        }
        else if($Status == 'Pre-Approved'){
            return $Status;
        }
    }
}

if (!function_exists('Get_MaxCheckOutTime')) {
    function Get_MaxCheckOutTime() {
        return 6;
    }
}

?>