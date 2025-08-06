<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UltraMsgService
{
    protected $instanceId;
    protected $token;

    public function __construct()
    {
        $this->instanceId = config('services.ultramsg.instance_id');
        $this->token = config('services.ultramsg.token');
    }

    public function sendMessage($to, $message)
    {
        $url = "https://api.ultramsg.com/{$this->instanceId}/messages/chat";

        $response = Http::post($url, [
            'token' => $this->token,
            'to' => $to,
            'body' => $message,
        ]);

        return $response->json();
    }

    public function sendLeaveRequest($leaveId, $employeeName, $leaveTypeId, $leaveType, $leaveTime, $leaveDate, $leaveReason, $status, $phoneNumber)
    {
        if ($leaveTypeId == 5) {
            $message = "Hi {$employeeName},\n\n";
            $message .= "✅ *Your early leave request* has been *successfully submitted*.\n\n";
            $message .= "🆔 *Leave ID:* {$leaveId}\n";
            $message .= "🗓️ *Date:* {$leaveDate}\n";
            $message .= "📅 *Leave Type:* {$leaveType}\n";
            $message .= "⏰ *Leave Time:* {$leaveTime}\n";
            $message .= "✏️ *Reason:* {$leaveReason}\n";
            $message .= "📌 *Status:* {$status}\n\n";
            $message .= "We will notify you once it's reviewed.\n\n";
            $message .= "Thank you.";
        }else{
            $message = "Hi {$employeeName},\n\n";
            $message .= "✅ *Your leave request* has been *successfully submitted*.\n\n";
            $message .= "🆔 *Leave ID:* {$leaveId}\n";
            $message .= "📅 *Leave Type:* {$leaveType}\n";
            $message .= "🗓️ *Date:* {$leaveDate}\n";
            $message .= "✏️ *Reason:* {$leaveReason}\n";
            $message .= "📌 *Status:* {$status}\n\n";
            $message .= "We will notify you once it's reviewed.\n\n";
            $message .= "Thank you.";
        }
        
        return $this->sendMessage($phoneNumber, $message);
    }


    public function sendLeaveCancelled($leaveId, $employeeName, $leaveTypeId, $leaveType, $leaveTime, $leaveDate, $leaveReason, $status, $phoneNumber)
    {
        $message = "Hi {$employeeName},\n\n";
        $message .= "🚫 *Your leave request has been successfully cancelled.*\n\n";
        $message .= "🆔 *Leave ID:* {$leaveId}\n";
        $message .= "📅 *Leave Type:* {$leaveType}\n";
        $message .= "🗓️ *Date:* {$leaveDate}\n";
        $message .= "✏️ *Reason:* {$leaveReason}\n";
        $message .= "📌 *Status:* Cancelled ❌\n\n";
        $message .= "If this was done by mistake, please contact HR immediately.\n\n";
        $message .= "Thank you,\n";
        $message .= config('app.name') . " HR Team";
        
        return $this->sendMessage($phoneNumber, $message);
    }


    public function sendLeaveApproved($leaveId, $employeeName, $leaveTypeId, $leaveType, $leaveTime, $leaveDate, $leaveReason, $status, $phoneNumber)
    {
        $message = "Hi {$employeeName},\n\n";
        $message .= "✅ *Your leave request* has been *approved*.\n\n";
        $message .= "🆔 *Leave ID:* {$leaveId}\n";
        $message .= "📅 *Leave Type:* {$leaveType}\n";
        $message .= "🗓️ *Date:* {$leaveDate}\n";
        $message .= "✏️ *Reason:* {$leaveReason}\n";
        $message .= "📌 *Status:* {$status}\n\n";
        $message .= "Wishing you a restful and productive time away.\n\n";
        $message .= "Best regards,\n";
        $message .= config('app.name') . " HR Team";
        
        return $this->sendMessage($phoneNumber, $message);
    }

    public function sendLeaveAutoApproved($leaveId, $employeeName, $leaveTypeId, $leaveType, $leaveTime, $leaveDate, $leaveReason, $status, $phoneNumber)
    {
        $message = "Hi {$employeeName},\n\n";
        $message .= "✅ *Your leave request* has been *automatically approved by the system* after a delay.\n\n";
        $message .= "🆔 *Leave ID:* {$leaveId}\n";
        $message .= "📅 *Leave Type:* {$leaveType}\n";
        $message .= "🗓️ *Date:* {$leaveDate}\n";
        $message .= "✏️ *Reason:* {$leaveReason}\n";
        $message .= "📌 *Status:* {$status}\n\n";
        $message .= "🙏 *Sorry for the inconvenience caused due to the delay.*\n\n";
        $message .= "Best regards,\n";
        $message .= config('app.name') . " HR Team";
        
        return $this->sendMessage($phoneNumber, $message);
    }

    public function sendLeaveRejected($leaveId, $employeeName, $leaveTypeId, $leaveType, $leaveTime, $leaveDate, $leaveReason, $status, $phoneNumber)
    {
        $message = "Hi {$employeeName},\n\n";
        $message .= "❌ *Your leave request* has been *rejected*.\n\n";
        $message .= "🆔 *Leave ID:* {$leaveId}\n";
        $message .= "📅 *Leave Type:* {$leaveType}\n";
        $message .= "🗓️ *Date:* {$leaveDate}\n";
        $message .= "✏️ *Reason:* {$leaveReason}\n";
        $message .= "📌 *Status:* {$status}\n\n";
        $message .= "If you have any questions or need clarification, please contact your manager.\n\n";
        $message .= "Best regards,\n";
        $message .= config('app.name') . " HR Team";
        
        return $this->sendMessage($phoneNumber, $message);
    }
}
