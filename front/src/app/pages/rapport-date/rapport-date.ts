import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators } from '@angular/forms';
import { RapportService } from '../../services/rapport-service';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatNativeDateModule } from '@angular/material/core';
import { MatButtonModule } from '@angular/material/button';



@Component({
  selector: 'app-date-rapport',
  templateUrl: './rapport-date.html',
  styleUrls: ['./rapport-date.css'],
   imports: [
    CommonModule,
    ReactiveFormsModule, // ✅ obligatoire pour [formGroup]
    FormsModule,         // facultatif si tu utilises ngModel
    HttpClientModule  ,   // pour HttpClient
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatButtonModule,
  ],
})
export class DateRapportComponent implements OnInit {
  form!: FormGroup;
  message = '';

  constructor(
    private fb: FormBuilder,
    private rapportDateService: RapportService
  ) {}

  ngOnInit(): void {
    this.form = this.fb.group({
      date_ouverture: ['', Validators.required],
      date_fermeture: ['', Validators.required],
    });

    // Charger les dates existantes
    this.rapportDateService.getDatesRapport().subscribe(data => {
      if (data.date_ouverture && data.date_fermeture) {
        this.form.patchValue(data);
      }
    });
  }

  onSubmit(): void {
  if (this.form.invalid) return;

  const formValue = {
    ...this.form.value,
    date_ouverture: this.formatDate(this.form.value.date_ouverture),
    date_fermeture: this.formatDate(this.form.value.date_fermeture),
  };

  this.rapportDateService.updateDatesRapport(formValue).subscribe({
    next: (res) => this.message = res.message,
    error: (err) => this.message = 'Erreur lors de la mise à jour'
  });
}

formatDate(date: any): string {
  const d = new Date(date);
  const month = ('0' + (d.getMonth() + 1)).slice(-2);
  const day = ('0' + d.getDate()).slice(-2);
  return `${d.getFullYear()}-${month}-${day}`;
}
}
