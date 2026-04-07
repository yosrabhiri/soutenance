import { Component, Inject } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef, MatDialogContent } from '@angular/material/dialog'
import {MatDialog, MatDialogModule} from '@angular/material/dialog';
import {MatButtonModule} from '@angular/material/button';
import { MatInputModule } from "@angular/material/input";
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import {ChangeDetectionStrategy} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {MatTimepickerModule} from '@angular/material/timepicker';
import {MatFormFieldModule} from '@angular/material/form-field';
import {provideNativeDateAdapter} from '@angular/material/core';
import {MatDatepickerModule} from '@angular/material/datepicker';
import { Header } from '../header/header';

@Component({
  selector: 'app-reunion-dialogue',
    providers: [provideNativeDateAdapter()],
  imports: [MatButtonModule,MatInputModule,ReactiveFormsModule,CommonModule, MatFormFieldModule,
    MatTimepickerModule,
    MatDatepickerModule,
    FormsModule,],
  templateUrl: './reunion-dialogue.html',
  styleUrl: './reunion-dialogue.css',
   changeDetection: ChangeDetectionStrategy.OnPush,

})

export class ReunionDialogue {
   readonly Header = Header;
  value: Date;
  reunionForm: FormGroup;
   formControl = new FormControl<Date | null>(null);

  constructor(
    private fb: FormBuilder,
    private dialogRef: MatDialogRef<ReunionDialogue>,
    @Inject(MAT_DIALOG_DATA) public data: any // data.selectedEtudiants
  ) {
    this.reunionForm = this.fb.group({
      jour: ['', Validators.required],
      heure: ['', Validators.required],
      salle: [''],
      note: [''],
      titre: ['']
    });
  }
formatDate(date: Date): string {
  const d = new Date(date);
  const month = ('0' + (d.getMonth() + 1)).slice(-2);
  const day = ('0' + d.getDate()).slice(-2);
  const year = d.getFullYear();
  return `${year}-${month}-${day}`;
}

formatTime(date: Date): string {
  const d = new Date(date);
  const hours = ('0' + d.getHours()).slice(-2);
  const minutes = ('0' + d.getMinutes()).slice(-2);
  const seconds = ('0' + d.getSeconds()).slice(-2);
  return `${hours}:${minutes}:${seconds}`;
}
  save() {
   if (this.reunionForm.valid) {
    const payload = {
      ...this.reunionForm.value,
      jour: this.reunionForm.value.jour ? this.formatDate(this.reunionForm.value.jour) : null,
      heure: this.reunionForm.value.heure ? this.formatTime(this.reunionForm.value.heure) : null,
      etudiants: this.data.selectedEtudiants.map((e: any) => e.etudiant_id)
    };
    console.log("Payload formaté :", payload);
    this.dialogRef.close(payload);
  }
  }

  cancel() {
    this.dialogRef.close();
  }

}
