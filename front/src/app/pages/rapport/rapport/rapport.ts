import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatOptionModule } from '@angular/material/core';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { StageService } from '../../../services/stage-api';
import { StepperService } from '../../../stepper-service';
import { MatIcon } from '@angular/material/icon';
import { HttpClient } from '@angular/common/http';
import { COMMA, ENTER } from '@angular/cdk/keycodes';
import {MatChipInputEvent, MatChipsModule} from '@angular/material/chips';
import { CommonModule } from '@angular/common';
import { environment } from '../../../../environments/environment';
@Component({
  selector: 'app-rapport',
  imports: [ MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatOptionModule,
    MatButtonModule, ReactiveFormsModule,MatIcon,MatChipsModule,CommonModule],
  templateUrl: './rapport.html',
  styleUrl: './rapport.css'
})
export class Rapport implements OnInit {
  group!: FormGroup;
  payloadComplet: any;
  file!: File;
  fileName: string = '';

  readonly separatorKeysCodes = [ENTER, COMMA] as const;

  constructor(
    private fb: FormBuilder,
    private stageService: StageService,
    public formData: StepperService,
    private http: HttpClient
  ) {}

  ngOnInit(): void {
    this.group = this.fb.group({
      nom: ['', Validators.required],
      prenom: ['', Validators.required],
      rapport: ['', Validators.required],
      mots_cles: this.fb.array([], Validators.required),  // <-- FormArray
      code_sujet: ['', Validators.required],
    });

    this.stageService.loadFormData().subscribe((data) => {
      if (!data) return;
      this.payloadComplet = data;
      this.formData.populateFromPayload(data);

      if (data && data.etudiant) {
        this.group.patchValue({
          nom: data.etudiant.nom,
          prenom: data.etudiant.prenom,
        });
      }
    });
  }

  // getter pratique
  get motsCles(): FormArray {
    return this.group.get('mots_cles') as FormArray;
  }
  trackByIndex(index: number): number {
  return index;
}
addMotCle(event: MatChipInputEvent): void {
  const value = (event.value || '').trim();
  if (value) {
    this.motsCles.push(this.fb.control(value));
  }
  event.chipInput!.clear();
}



  removeMotCle(index: number): void {
    this.motsCles.removeAt(index);
  }

  onFileSelected(event: any) {
    const selectedFile: File = event.target.files[0];
    if (selectedFile) {
      this.file = selectedFile;
      this.fileName = selectedFile.name;
    }
  }

  submit() {
    if (!this.file) {
      alert('Veuillez choisir un fichier');
      return;
    }

    const formData = new FormData();
    formData.append('rapport', this.file);
    formData.append('code_sujet', this.group.get('code_sujet')?.value);

   // Ajouter les mots clés comme tableau
  this.motsCles.value.forEach((mot: string) => {
    formData.append('mots_cles[]', mot);
  });

    this.http.post(`${environment.apiUrl}/stage/depose`, formData, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    .subscribe({
      next: (res: any) => console.log('Rapport déposé : ' + res.chemin_document),
      error: (err) => console.error(err)
    });
  }
}
