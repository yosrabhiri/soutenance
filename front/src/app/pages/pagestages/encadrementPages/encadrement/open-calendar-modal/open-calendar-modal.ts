import { Component, Inject, Input, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog } from '@angular/material/dialog';
import { FullCalendarModule } from '@fullcalendar/angular';
import { CalendarOptions } from '@fullcalendar/core'; // useful for typechecking
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin, { DateClickArg } from '@fullcalendar/interaction';
import { CalendarMeetings } from '../calendar-meetings/calendar-meetings';

interface Reunion {
  jour: string;
  heure: string;
  titre: string;
  salle?: string;
  note?: string | null;
}

@Component({
  selector: 'app-open-calendar-modal',
  templateUrl: './open-calendar-modal.html',
  styleUrls: ['./open-calendar-modal.css'],
  imports: [FullCalendarModule]
})
export class OpenCalendarModalComponent  {
calendarOptions!: CalendarOptions;

  constructor(@Inject(MAT_DIALOG_DATA) public data: { events: any[] },private dialog: MatDialog) {}

  ngOnInit() {
  // ✅ Construire les options du calendrier à partir des réunions
  this.calendarOptions = {
    initialView: 'dayGridMonth',
    plugins: [dayGridPlugin, interactionPlugin],
    eventClick: (info) => this.openEventDetails(info.event), // ✅ Gérer clic
    events: this.data.events.map(e => ({
      id: e.id, // 👈 garder l'ID de la réunion
      title: e.title, // Pas besoin de concaténer studentName
      start: this.formatDateTime(e.start, e.heure), // ✅ Ajoute l'heure
      extendedProps: {
        heure: e.heure,
        salle: e.salle || 'Non spécifiée',
        note: e.note || '',
        etudiants: e.etudiants || [] ,
        specialite:e.specialite// 👈 liste complète des étudiants
      }
    }))
  };
}

openEventDetails(event: any) {
  this.dialog.open(CalendarMeetings, {
    width: '400px',
    data: {
      title: event.title,
      heure: event.extendedProps.heure,
      salle: event.extendedProps.salle,
      note: event.extendedProps.note,
      etudiants: event.extendedProps.etudiants
    }
  });
}

 formatDateTime(date: Date | string, heure?: string): string {
  const d = new Date(date);
  if (heure) {
    const [h, m] = heure.split(':');
    d.setHours(+h, +m || 0);
  }
  return d.toISOString(); // FullCalendar accepte format ISO
}
  handleDateClick(arg: DateClickArg) {
    alert('Date click: ' + arg.dateStr);
  }


}