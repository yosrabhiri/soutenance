
import { Component, inject } from '@angular/core';
import {FormBuilder, Validators, FormsModule, ReactiveFormsModule} from '@angular/forms';
import {MatInputModule} from '@angular/material/input';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatStepper, MatStepperModule} from '@angular/material/stepper';
import {MatButtonModule} from '@angular/material/button';
import { StepOne } from "../form-steps/step-one/step-one";
import { StepTwo } from "../form-steps/step-two/step-two";
import { StepThree } from "../form-steps/step-three/step-three";
import { StepFour } from "../form-steps/step-four/step-four";
import { StepFive } from "../form-steps/step-five/step-five";
import { StepperService } from '../../../stepper-service';
import jsPDF from 'jspdf';

import { CommonModule, DatePipe } from '@angular/common';
import { OnInit } from '@angular/core';
import { StageService } from '../../../services/stage-api';
import { DemandeService } from '../../../services/demande';


@Component({
  selector: 'app-stepper-nav',
  imports: [MatButtonModule,
    CommonModule,
    MatStepperModule,
    FormsModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule, StepOne, StepTwo, StepThree, StepFour, StepFive],
    providers:[DatePipe],
  templateUrl: './stepper-nav.html',
  styleUrl: './stepper-nav.css'
})

export class StepperNav implements OnInit {
  encadrants: any[] = [];
  payloadComplet: any;
  disablePreviousSteps = false;
  stageSubmitted=false;
  niveau:string
  

  constructor(
    public formData: StepperService,
    private stageService: StageService,
    private datePipe: DatePipe,
    private demandeService: DemandeService
  ) {}

  showD() {
    console.log('heyy', this.formData.getAllData());
  }

  ngOnInit(): void {
    // utilisation du StageService avec cache (BehaviorSubject)
    this.stageService.loadFormData().subscribe((data) => {
      if (!data) return;

      console.log('Payload complet:', data);
      this.payloadComplet = data;

      // délègue le patching des formulaires au StepperService
      /*this.formData.populateFromPayload(data);
      console.log(this.formData.firstFormGroup.value.niveau,'ahjjjjkkk')*/
      // ⚡ Si c'est une mise à jour → remplir les formulaires
      console.log("Date fin reçue du backend:", data.stage?.date_fin);
      console.log("Date actuelle :", new Date());
  console.log(data.isUpdate)
  if (data.isUpdate) {
    this.formData.populateFromPayload(data);
    console.log(data.isUpdate,"fil update");
  } else {
    console.log(data.isUpdate,"takaaka");
    this.formData.populateFromPayload(data);
    // ⚡ Si c'est une nouvelle création → reset des formulaires
    
    this.formData.resetForms();
  }
      const niveau = this.formData.firstFormGroup.get('niveau')?.value;

      // encadrants dispo pour la vue
      this.encadrants = this.formData.encadrants;

      console.log('Encadrants formatés:', this.encadrants);
    });
  }
isReadOnly(): boolean {
  return this.disablePreviousSteps;
}
  submitStage(stepper:MatStepper) { 
    const confirmMsg = "⚠️ Une fois que vous passez à l’étape suivante, vous ne pourrez plus éditer les étapes précédentes. Voulez‑vous continuer ?";
  if (this.stageSubmitted) {
    stepper.next(); // juste avancer
    return;
  }
  if (confirm(confirmMsg)) {
  
  const allData = this.formData.getAllData();

  // Créer un FormData pour envoyer fichier + autres champs
  const payload = new FormData();

  // ⚡ Fichier du step 3
  if (allData.fichier) {
    payload.append('fichier', allData.fichier);
  }

  // ⚡ Étudiant (step1)
  payload.append('student_id', allData.studentId || '');
  payload.append('nom', allData.nom || '');
  payload.append('prenom', allData.prenom || '');
  payload.append('cin', allData.cin || '');
  payload.append('email', allData.email || '');

  // ⚡ Société (step2)
  payload.append('societe_nom', allData.nom || '');
  payload.append('societe_adresse', allData.adresse || '');
  payload.append('societe_domaine', allData.domaine || '');
  payload.append('societe_tel', allData.tel || '');
  payload.append('societe_email', allData.emailSo || '');
  payload.append('societe_siteweb', allData.siteweb || '');
  payload.append('societe_linkedin', allData.linkedin || '');

  // ⚡ Encadrant (step3)
  payload.append('encadrant_nom', allData.nompre || '');
  payload.append('encadrant_fonction', allData.fonction || '');
  payload.append('encadrant_service', allData.service || '');
  payload.append('encadrant_email', allData.emailpro || '');
  payload.append('url_overleaf', allData.url_overleaf || '');

  // ⚡ Enseignant (step4)
  payload.append('enseignant_id', allData.selectedProfId || '');
  payload.append('description_taches', allData.tache || '');
  payload.append('date_debut', this.datePipe.transform(allData.start, 'yyyy-MM-dd') || '');
  payload.append('date_fin', this.datePipe.transform(allData.end, 'yyyy-MM-dd') || '');

  // Envoyer via le service
  /*this.stageService.saveStage(payload).subscribe({
    next: (res) => {console.log('Stage enregistré :', res)
      this.disablePreviousSteps = true;
  this.stageSubmitted = true;
         stepper.next();},
    error: (err) => console.error('Erreur :', err)
  });
  this.demandeService.createDemande(payload).subscribe({
    next: (res) => console.log('demande envoyé :', res),
    error: (err) => console.error('Erreur :', err)
  })

   } */
  // Envoyer via le service
    this.stageService.saveStage(payload).subscribe({
      next: (res) => {
        console.log("Stage enregistré :", res);
        this.disablePreviousSteps = true;
        this.stageSubmitted = true;
        stepper.next();

        // ⚡ ENVOYER LA DEMANDE UNIQUEMENT SI C’EST UNE CREATION
        if (!res.isUpdate) {
          this.demandeService.createDemande(payload).subscribe({
            next: (d) => console.log("Demande envoyée :", d),
            error: (err) => console.error("Erreur demande :", err),
          });
        }
      },
      error: (err) => console.error("Erreur :", err),
    });
  }else {
    // L'utilisateur a annulé → ne fait rien
    return;
  }
}

}
