<?php
namespace App\Enums;

enum IncidentStatus: string
{
    case REPORTED   = 'reported';
    case IN_PROGRESS = 'in_progress';
    case VERIFIED   = 'verified';
    case RESPONDING = 'responding';
    case RESOLVED   = 'resolved';
    case OPEN       = 'open';
    case DISMISSED  = 'dismissed';
    case CLOSED     = 'closed';
}
