<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Project $project;

    private User $approver;

    private string $level;

    private bool $isFinalApproval;

    public function __construct(Project $project, User $approver, string $level, bool $isFinalApproval = false)
    {
        $this->project = $project;
        $this->approver = $approver;
        $this->level = $level;
        $this->isFinalApproval = $isFinalApproval;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $levelDisplay = match ($this->level) {
            'municipal' => 'Municipal Officer',
            'provincial' => 'Provincial Officer',
            'regional' => 'Regional Director',
            default => ucfirst($this->level),
        };

        $subject = $this->isFinalApproval
            ? 'Project Fully Approved: '.$this->project->title
            : 'Project Approved at '.$levelDisplay.' Level: '.$this->project->title;

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello '.($notifiable->full_name ?? $notifiable->username));

        if ($this->isFinalApproval) {
            $mail->line('Great news! Your project has been fully approved and is now active.')
                ->line('')
                ->line('**Project Details:**')
                ->line('Title: '.$this->project->title)
                ->line('Budget: ₱'.number_format($this->project->budget, 2))
                ->line('Final Approver: '.($this->approver->full_name ?? $this->approver->username))
                ->line('')
                ->line('You can now proceed with project implementation.')
                ->action('View Project', url('/projects/'.$this->project->id));
        } else {
            $mail->line('Your project has been approved at the '.$levelDisplay.' level.')
                ->line('')
                ->line('**Project Details:**')
                ->line('Title: '.$this->project->title)
                ->line('Approved By: '.($this->approver->full_name ?? $this->approver->username))
                ->line('Current Status: '.$this->project->approval_status_display)
                ->line('')
                ->line('The project will now proceed to the next approval level.')
                ->action('View Project', url('/projects/'.$this->project->id));
        }

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => $this->isFinalApproval ? 'project_fully_approved' : 'project_level_approved',
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'level' => $this->level,
            'approver_id' => $this->approver->id,
            'approver_name' => $this->approver->full_name ?? $this->approver->username,
            'is_final_approval' => $this->isFinalApproval,
            'message' => $this->isFinalApproval
                ? 'Project "'.$this->project->title.'" has been fully approved'
                : 'Project "'.$this->project->title.'" approved at '.ucfirst($this->level).' level',
        ];
    }
}
