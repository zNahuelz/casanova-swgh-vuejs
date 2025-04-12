<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'PENDIENTE';
    case Attended = 'ATENDIDO';
    case Rescheduled = 'REPROGRAMADO';
}
