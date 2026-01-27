<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectChangesRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Project $project;

    private User $reviewer;

    private string $level;

    private string $comments;

    public function __construct(Project $project, User $reviewer, string $level, string $comments)
    {
        $this->project = $project;
        $this->reviewer = $reviewer;
        $this->level = $level;
        $this->comments = $comments;
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
            ->subject('Changes Requested for Project: '.$this->project->title)
            ->greeting('Hello '.($notifiable->full_name ?? $notifiable->username))
            ->line('Changes have been requested for your project before it can be approved.')
            ->line('')
            ->line('**Project Details:**')
            ->line('Title: '.$this->project->title)
            ->line('Requested By: '.($this->reviewer->full_name ?? $this->reviewer->username))
            ->line('Level: '.$levelDisplay)
            ->line('')
            ->line('**Requested Changes:**')
            ->line($this->comments)
            ->line('')
            ->action('Edit Project', url('/projects/'.$this->project->id.'/edit'))
            ->line('Please make the requested changes and resubmit for approval.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'changes_requested',
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'level' => $this->level,
            'reviewer_id' => $this->reviewer->id,
            'reviewer_name' => $this->reviewer->full_name ?? $this->reviewer->username,
            'comments' => $this->comments,
            'message' => 'Changes requested for project "'.$this->project->title.'"',
        ];
    }
}
