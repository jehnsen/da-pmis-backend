<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MilestoneCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Project $project;

    private ProjectMilestone $milestone;

    public function __construct(Project $project, ProjectMilestone $milestone)
    {
        $this->project = $project;
        $this->milestone = $milestone;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Milestone Completed: '.$this->milestone->title)
            ->greeting('Hello '.($notifiable->full_name ?? $notifiable->username))
            ->line('A project milestone has been completed!')
            ->line('')
            ->line('**Milestone Details:**')
            ->line('Milestone: '.$this->milestone->title)
            ->line('Project: '.$this->project->title)
            ->line('Completed Date: '.now()->format('F j, Y'))
            ->line('')
            ->action('View Project', url('/projects/'.$this->project->id))
            ->line('Keep up the great work!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'milestone_completed',
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'milestone_id' => $this->milestone->id,
            'milestone_title' => $this->milestone->title,
            'message' => 'Milestone "'.$this->milestone->title.'" completed for project "'.$this->project->title.'"',
        ];
    }
}
