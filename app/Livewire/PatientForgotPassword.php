<?php

namespace App\Livewire;

use App\Models\Member;
use App\Models\SiteDetail;
use App\Models\SmtpDetails;
use App\Models\Location;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

#[Layout('components.layouts.custom-patient')]
#[Title('Forgot Login ID / Password')]
class PatientForgotPassword extends Component
{
    public $teamId;
    public $email_or_mobile = '';
    public $showModal = true;

    protected $messages = [
        'email_or_mobile.required' => 'Email or mobile number is required.',
    ];

    public function mount()
    {
        $this->teamId = tenant('id');
    }

    protected function rules()
    {
        return [
            'email_or_mobile' => 'required|string',
        ];
    }

    public function sendTemporaryPassword()
    {
        $this->validate();

        try {
            // Clean the input - remove spaces and special characters for mobile
            $cleanInput = preg_replace('/[^0-9@.]/', '', $this->email_or_mobile);
            
            // Determine if input is email or mobile
            $isEmail = filter_var($this->email_or_mobile, FILTER_VALIDATE_EMAIL);
            
            // Find member by email or mobile
            $member = null;
            if ($isEmail) {
                $member = Member::where('team_id', $this->teamId)
                    ->where('email', $this->email_or_mobile)
                    ->where('is_active', 1)
                    ->where('status', 'active')
                    ->first();
            } else {
                // Try to find by mobile number (with or without country code)
                $cleanMobileNumber = preg_replace('/[^0-9]/', '', $this->email_or_mobile);
                
                $member = Member::where('team_id', $this->teamId)
                    ->where('is_active', 1)
                    ->where('status', 'active')
                    ->where(function($query) use ($cleanMobileNumber) {
                        $query->whereRaw("CONCAT(mobile_country_code, mobile_number) = ?", [$cleanMobileNumber])
                              ->orWhere('mobile_number', $cleanMobileNumber);
                    })
                    ->first();
            }

            if (!$member) {
                session()->flash('error', 'No account found with the provided email or mobile number.');
                return;
            }

            // Generate temporary password (8 digits as shown in the email example)
            $temporaryPassword = $this->generateTemporaryPassword();

            // Update member password and set temporary flag
            $member->password = $temporaryPassword;
            $member->is_temporary_password = true;
            $member->save();

            // Send email with temporary password
            $this->sendForgotPasswordEmail($member, $temporaryPassword);

            session()->flash('success', 'A temporary password has been sent to your email address. Please check your inbox and login with the temporary password.');

            // Reset form
            $this->email_or_mobile = '';

        } catch (\Exception $e) {
            Log::error('Forgot password error: ' . $e->getMessage());
            session()->flash('error', 'Failed to process your request. Please try again or contact support.');
        }
    }

    /**
     * Generate a temporary password (8 digits)
     * 
     * @return string
     */
    protected function generateTemporaryPassword($length = 8)
    {
        // Generate 8-digit numeric password as shown in email example
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= random_int(0, 9);
        }
        return $password;
    }

    /**
     * Send forgot password email with temporary password
     * 
     * @param Member $member
     * @param string $temporaryPassword
     * @return void
     */
    protected function sendForgotPasswordEmail($member, $temporaryPassword)
    {
        try {
            // Get SMTP details for the team
            $smtpDetails = SmtpDetails::where('team_id', $this->teamId)->first();
            
            if (!$smtpDetails || empty($smtpDetails->hostname)) {
                Log::warning('SMTP details not configured for team: ' . $this->teamId);
                return;
            }

            // Configure mail settings
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', trim($smtpDetails->hostname));
            Config::set('mail.mailers.smtp.port', trim($smtpDetails->port));
            Config::set('mail.mailers.smtp.encryption', trim($smtpDetails->encryption ?? 'ssl'));
            Config::set('mail.mailers.smtp.username', trim($smtpDetails->username));
            Config::set('mail.mailers.smtp.password', trim($smtpDetails->password));
            Config::set('mail.from.address', trim($smtpDetails->from_email));
            Config::set('mail.from.name', trim($smtpDetails->from_name));

            // Prepare email data
            $emailData = [
                'salutation' => $member->salutation ?? 'Mr',
                'full_name' => $member->full_name,
                'email' => $member->email,
                'temporary_password' => $temporaryPassword,
                'login_url' => route('tenant.patient.login'),
            ];

            // Render email template
            $templateContent = view('emails.patient-forgot-password', ['data' => $emailData])->render();
            $subject = 'Fullerton Health - Forgot Login ID or Password';

            // Send email using SMTP mailer explicitly
            Mail::mailer('smtp')->html($templateContent, function ($message) use ($member, $subject, $smtpDetails) {
                $message->from($smtpDetails->from_email, $smtpDetails->from_name);
                $message->to($member->email)->subject($subject);
            });

            Log::info('Forgot password email sent successfully to: ' . $member->email);

        } catch (\Exception $e) {
            Log::error('Failed to send forgot password email: ' . $e->getMessage());
            throw $e; // Re-throw so we can show error to user
        }
    }

    public function close()
    {
        return redirect()->route('tenant.patient.login');
    }

    public function render()
    {
        // Get first location for the team
        $firstLocation = Location::where('team_id', $this->teamId)
            ->where('status', 1)
            ->orderBy('id')
            ->first();
        
        $locationId = $firstLocation ? $firstLocation->id : null;
        
        // Get logo based on team id and first location
        $logo = SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId ?? null, $locationId);

        return view('livewire.patient-forgot-password', [
            'logo' => $logo,
        ]);
    }
}

