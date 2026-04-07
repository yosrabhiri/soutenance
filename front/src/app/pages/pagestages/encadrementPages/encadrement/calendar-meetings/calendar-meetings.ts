import { CommonModule } from '@angular/common';
import { Component, Inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MAT_DIALOG_DATA, MatDialogActions, MatDialogModule } from '@angular/material/dialog';

@Component({
  selector: 'app-calendar-meetings',
  imports: [MatDialogActions, MatDialogModule,CommonModule,MatButtonModule],
  templateUrl: './calendar-meetings.html',
  styleUrl: './calendar-meetings.css'
})
export class CalendarMeetings {
constructor(@Inject(MAT_DIALOG_DATA) public data: any) {}
}
