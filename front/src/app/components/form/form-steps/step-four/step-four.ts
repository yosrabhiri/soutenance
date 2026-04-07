import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormGroupDirective, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { MatCardModule } from "@angular/material/card";
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';

@Component({
  selector: 'app-step-four',
imports: [MatSelectModule,MatCardModule,ReactiveFormsModule,CommonModule,MatFormFieldModule,MatInputModule],
  templateUrl: './step-four.html',
  styleUrl: './step-four.css',
   standalone: true,
})
export class StepFour implements OnInit{
  @Input() prof!: FormGroup;
  @Input() encadrants: any[] = []; // <-- On reçoit la liste
  @Input() readOnly: boolean = false;


  constructor(private fb: FormBuilder) {}

  ngOnInit() {
    console.log('step4',this.encadrants)
    // Initialisation si le FormGroup n'existe pas
    if (!this.prof) {
      this.prof = this.fb.group({
      
        selectedProfId: [''], // id caché
        nomPrenom: [''],      // nom + prenom visible
        emailprof: [''],        // email visible
        url_overleaf:['']
      });
    }
  }

  selectEncadrant(enc: any) {
    this.prof.get('nomPrenom')?.setValue(enc.nom + ' ' + enc.prenom);
    this.prof.get('emailprof')?.setValue(enc.email);
    this.prof.get('selectedProfId')?.setValue(enc.id); // on stocke l'id dans le formgroup
     this.prof.get('url_overleaf')?.setValue(enc.url_overleaf);
  }
}