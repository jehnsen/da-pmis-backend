<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Document Published: '.$this->document->title)
            ->greeting('Hello '.($notifiable->full_name ?? $notifiable->username))
            ->line('A new document has been published.')
            ->line('')
            ->line('**Document Details:**')
            ->line('Title: '.$this->document->title)
            ->line('Description: '.($this->document->description ?? 'No description'))
            ->line('Published: '.now()->format('F j, Y'))
            ->line('')
            ->action('View Document', url('/documents/'.$this->document->id))
            ->line('Thank you for staying informed!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'document_published',
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'message' => 'New document published: "'.$this->document->title.'"',
        ];
    }
}
