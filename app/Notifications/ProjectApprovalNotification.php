<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Project $project;

    private string $level;

    public function __construct(Project $project, string $level)
    {
        $this->project = $project;
        $this->level = $level;
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

        return (new MailMessage)
            ->subject('Project Approval Required: '.$this->project->title)
            ->greeting('Hello '.($notifiable->full_name ?? $notifiable->username))
            ->line('A project requires your approval.')
            ->line('')
            ->line('**Project Details:**')
            ->line('Title: '.$this->project->title)
            ->line('Department: '.($this->project->department->name ?? 'N/A'))
            ->line('Budget: ₱'.number_format($this->project->budget, 2))
            ->line('Approval Level: '.$levelDisplay)
            ->line('')
            ->action('Review Project', url('/projects/'.$this->project->id))
            ->line('Please review and take appropriate action.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'project_approval_required',
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'level' => $this->level,
            'budget' => $this->project->budget,
            'message' => 'Project "'.$this->project->title.'" requires your approval',
        ];
    }
}
