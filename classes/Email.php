<?php
class Email {
    private $error;

    public function __construct() {
        // Initialize PHP's mail function settings
        ini_set("SMTP", "localhost");
        ini_set("smtp_port", "25");
    }

    private function sendMail($to, $subject, $body, $headers) {
        return mail($to, $subject, $body, $headers);
    }

    public function sendInterviewSchedule($to_email, $to_name, $interview_data) {
        try {
            $subject = 'Interview Schedule - CCS Screening';
            
            $headers = "From: CCS Screening System <noreply@example.com>\r\n";
            $headers .= "Reply-To: noreply@example.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2>Interview Schedule Confirmation</h2>
                    <p>Dear {$to_name},</p>
                    <p>Your interview has been scheduled for the CCS Screening process.</p>
                    
                    <div style='background-color: #f8f9fa; padding: 20px; margin: 20px 0;'>
                        <h3 style='margin-top: 0;'>Interview Details:</h3>
                        <p><strong>Date:</strong> " . date('F d, Y', strtotime($interview_data['schedule_date'])) . "</p>
                        <p><strong>Time:</strong> " . 
                            date('h:i A', strtotime($interview_data['start_time'])) . " - " . 
                            date('h:i A', strtotime($interview_data['end_time'])) . 
                        "</p>
                        <p><strong>Interviewer:</strong> {$interview_data['interviewer_name']}</p>
                        <p><strong>Meeting Link:</strong> <a href='{$interview_data['meeting_link']}'>{$interview_data['meeting_link']}</a></p>
                    </div>

                    <div style='background-color: #e9ecef; padding: 20px; margin: 20px 0;'>
                        <h3 style='margin-top: 0;'>Important Notes:</h3>
                        <ul style='padding-left: 20px;'>
                            <li>Please click the meeting link above at your scheduled time</li>
                            <li>Test your camera and microphone before joining</li>
                            <li>Ensure you have a stable internet connection</li>
                            <li>Join 5 minutes before the scheduled time</li>
                            <li>Have your resume and documents ready</li>
                        </ul>
                    </div>

                    <p>Best regards,<br>CCS Screening Team</p>
                </div>
            ";

            return $this->sendMail($to_email, $subject, $body, $headers);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function sendInterviewReminder($to_email, $to_name, $interview_data) {
        try {
            $subject = 'Interview Reminder - CCS Screening';
            
            $headers = "From: CCS Screening System <noreply@example.com>\r\n";
            $headers .= "Reply-To: noreply@example.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2>Interview Reminder</h2>
                    <p>Dear {$to_name},</p>
                    <p>This is a reminder about your upcoming interview.</p>
                    
                    <div style='background-color: #f8f9fa; padding: 20px; margin: 20px 0;'>
                        <h3 style='margin-top: 0;'>Interview Details:</h3>
                        <p><strong>Date:</strong> " . date('F d, Y', strtotime($interview_data['schedule_date'])) . "</p>
                        <p><strong>Time:</strong> " . 
                            date('h:i A', strtotime($interview_data['start_time'])) . " - " . 
                            date('h:i A', strtotime($interview_data['end_time'])) . 
                        "</p>
                        <p><strong>Interviewer:</strong> {$interview_data['interviewer_name']}</p>
                        <p><strong>Meeting Link:</strong> <a href='{$interview_data['meeting_link']}'>{$interview_data['meeting_link']}</a></p>
                    </div>

                    <div style='background-color: #e9ecef; padding: 20px; margin: 20px 0;'>
                        <h3 style='margin-top: 0;'>Reminder:</h3>
                        <ul style='padding-left: 20px;'>
                            <li>Click the meeting link above at your scheduled time</li>
                            <li>Join 5 minutes early to test your audio/video</li>
                            <li>Ensure you're in a quiet environment</li>
                        </ul>
                    </div>

                    <p>Best regards,<br>CCS Screening Team</p>
                </div>
            ";

            return $this->sendMail($to_email, $subject, $body, $headers);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function sendInterviewCancellation($to_email, $to_name, $interview_data) {
        try {
            $subject = 'Interview Cancellation - CCS Screening';
            
            $headers = "From: CCS Screening System <noreply@example.com>\r\n";
            $headers .= "Reply-To: noreply@example.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2>Interview Cancellation Notice</h2>
                    <p>Dear {$to_name},</p>
                    <p>We regret to inform you that your scheduled interview has been cancelled.</p>
                    
                    <div style='background-color: #f8f9fa; padding: 20px; margin: 20px 0;'>
                        <h3 style='margin-top: 0;'>Cancelled Interview Details:</h3>
                        <p><strong>Date:</strong> " . date('F d, Y', strtotime($interview_data['schedule_date'])) . "</p>
                        <p><strong>Time:</strong> " . 
                            date('h:i A', strtotime($interview_data['start_time'])) . " - " . 
                            date('h:i A', strtotime($interview_data['end_time'])) . 
                        "</p>
                        <p><strong>Meeting Link:</strong> <a href='{$interview_data['meeting_link']}'>{$interview_data['meeting_link']}</a></p>
                    </div>

                    " . ($interview_data['cancel_reason'] ? "
                    <div style='margin: 20px 0;'>
                        <h3>Reason for Cancellation:</h3>
                        <p>" . nl2br(htmlspecialchars($interview_data['cancel_reason'])) . "</p>
                    </div>
                    " : "") . "

                    <p>We will contact you shortly to reschedule your interview.</p>
                    <p>Best regards,<br>CCS Screening Team</p>
                </div>
            ";

            return $this->sendMail($to_email, $subject, $body, $headers);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getError() {
        return $this->error;
    }
}
