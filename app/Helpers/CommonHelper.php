<?php

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

?>