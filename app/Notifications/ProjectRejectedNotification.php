<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Project $project;

    private User $rejector;

    private string $level;

    private string $comments;

    private ?string $reason;

    public function __construct(Project $project, User $rejector, string $level, string $comments, ?string $reason = null)
    {
        $this->project = $project;
        $this->rejector = $rejector;
        $this->level = $level;
        $this->comments = $comments;
        $this->reason = $reason;
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

        $mail = (new MailMessage)
            ->subject('Project Rejected: '.$this->project->title)
            ->greeting('Hello '.($notifiable->full_name ?? $notifiable->username))
            ->line('Unfortunately, your project has been rejected.')
            ->line('')
            ->line('**Project Details:**')
            ->line('Title: '.$this->project->title)
            ->line('Rejected By: '.($this->rejector->full_name ?? $this->rejector->username))
            ->line('Level: '.$levelDisplay)
            ->line('');

        if ($this->reason) {
            $mail->line('**Reason:** '.$this->reason);
        }

        $mail->line('**Comments:** '.$this->comments)
            ->line('')
            ->line('Please review the feedback and make necessary revisions before resubmitting.')
            ->action('View Project', url('/projects/'.$this->project->id));

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'project_rejected',
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'level' => $this->level,
            'rejector_id' => $this->rejector->id,
            'rejector_name' => $this->rejector->full_name ?? $this->rejector->username,
            'reason' => $this->reason,
            'comments' => $this->comments,
            'message' => 'Project "'.$this->project->title.'" has been rejected',
        ];
    }
}
