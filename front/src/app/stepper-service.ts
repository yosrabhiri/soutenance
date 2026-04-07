import { Injectable } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { stageDurationValidator } from './stepper-validator';

@Injectable({
  providedIn: 'root'
})
export class StepperService {
  firstFormGroup!: FormGroup;
  secondFormGroup!: FormGroup;
  thirdFormGroup!: FormGroup;
  fourthFormGroup!: FormGroup;
  diplomes :{ id: number; nom: string }[] = [];
  specialites: { id: number; nom: string }[] = [];
  niveaux:{ id: number; nom: string }[] = [];
 

  societes: any[] = [];
  encadrants: any[] = [];

  constructor(private fb: FormBuilder) {
    // Step 1: Étudiant
    this.firstFormGroup = this.fb.group({
      nom: ['', Validators.required],
      prenom: ['', Validators.required],
      cin: ['', [Validators.required, Validators.minLength(8)]],
      niveau: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      diplome: ['', Validators.required],
      specialite: ['', Validators.required]
    });

    // Step 2: Société
    this.secondFormGroup = this.fb.group({
      nom: [''],
      adresse: [''],
      domaine: [''],
      tel: [''],
      emailSo: ['', [Validators.email]],
      siteweb: [''],
      linkedin: ['']
    });

    // Step 3: Encadrant pro
    this.thirdFormGroup = this.fb.group({
      nompre: ['', Validators.required],
      fonction: [''],
      service: [''],
      emailpro: ['', [Validators.email]],
     
      tache: [''],
      start: [''],
      end: [''],
      fichier:[null]
    },{ validators: stageDurationValidator() });

      // Step 4: Encadrant académique
    this.fourthFormGroup = this.fb.group({
      selectedProfId: [''],
      nomPrenom: ['', Validators.required],
      emailprof: ['', [Validators.required, Validators.email]],
      url_overleaf:['']
    });
  }

  /** Merge all steps */
  getAllData() {
    return {
      ...this.firstFormGroup.value,
      ...this.secondFormGroup.value,
      ...this.thirdFormGroup.value,
      ...this.fourthFormGroup.value
    };
  }

  /** Populate forms + arrays from backend payload */
  populateFromPayload(data: any) {
    console.log('aahhh',data)
    // Étudiant
    if (data.etudiant) {
      this.diplomes = data.diplomes;
    this.niveaux = data.niveaux;
    this.specialites = data.specialites;
      this.firstFormGroup.patchValue({
        nom: data.etudiant.nom,
        prenom: data.etudiant.prenom,
        cin: data.etudiant.cin,
       diplome: this.diplomes.find(d => d.id === data.etudiant.diplome_id)?.nom,
      niveau: this.niveaux.find(n => n.id === data.etudiant.niveau_id)?.nom,
      specialite: this.specialites.find(s => s.id === data.etudiant.specialite_id)?.nom,
        email: data.etudiant.email,
       
      });
    }

    // Sociétés
    if (data.societes) {
      this.societes = data.societes.map((s: any) => ({
        id: s.id,
        nom: s.nom,
        adresse: s.adresse,
        domaine: s.secteur_activite,
        tel: s.telephone,
        emailSo: s.email,
        siteweb: s.site_web,
        linkedin: s.linkedin
      }));
    }

    // Encadrants
    if (data.encadrants) {
      this.encadrants = data.encadrants.map((e: any) => ({
        id: e.Code_enseignant,
        nom: e.NomEnseignant,
        prenom: e.PrenomEnseignant,
        email: e.Email
      }));
    }
    // ⚡ Stage existant
  if (data.stage) {
    console.log("mijouud",this.societes)
    // Step 2: Société
    const societe = this.societes.find(s => s.id === data.stage.societe_id);
    if (societe) {
      this.secondFormGroup.patchValue({
        nom: societe.nom,
        adresse: societe.adresse,
        domaine: societe.domaine,
        tel: societe.tel,
        emailSo: societe.emailSo,
        siteweb: societe.siteweb,
        linkedin: societe.linkedin
      });
    }

    // Step 3: Encadrant pro
    this.thirdFormGroup.patchValue({
      nompre: data.stage.encadrant_professionnel?.nom_complet || '',
      fonction: data.stage.encadrant_professionnel?.fonction || '',
      service: data.stage.encadrant_professionnel?.departement || '',
      emailpro: data.stage.encadrant_professionnel?.email || '',
      tache: data.stage.description_taches,
      start: data.stage.date_debut,
      end: data.stage.date_fin,
      fichier: null
    });

    // Step 4: Encadrant académique
    const prof = this.encadrants.find(e => e.id === data.stage.enseignant_id);
    if (prof) {
      this.fourthFormGroup.patchValue({
        selectedProfId: prof.id,
        prof: `${prof.nom} ${prof.prenom}`,
        emailprof: prof.email,
        url_overleaf:data.stage.url_overleaf
      });
    }// ⚡ Stage existant
  if (data.stage) {
    // Step 2: Société
    const societe = this.societes.find(s => s.id === data.stage.societe_id);
    if (societe) {
      this.secondFormGroup.patchValue({
        nom: societe.nom,
        adresse: societe.adresse,
        domaine: societe.domaine,
        tel: societe.tel,
        emailSo: societe.emailSo,
        siteweb: societe.siteweb,
        linkedin: societe.linkedin
      });
    }

    // Step 3: Encadrant pro
    this.thirdFormGroup.patchValue({
      nompre: data.encadrantpro?.nom_complet || '',
      fonction: data.encadrantpro?.fonction || '',
      service: data.encadrantpro?.departement || '',
      emailpro: data.encadrantpro.email || '',
      tache: data.stage.description_taches,
      start: data.stage.date_debut,
      end: data.stage.date_fin,
      fichier: null
    });
    


    // Step 4: Encadrant académique
    const prof = this.encadrants.find(e => e.id === data.stage.enseignant_id);
    if (prof) {
      this.fourthFormGroup.patchValue({
        selectedProfId: prof.id,
        nomPrenom: prof,
        emailprof: prof.email,
        url_overleaf:data.stage.url_overleaf
      });
    }
  }
  }
}
resetForms() {
  //this.firstFormGroup.reset();
  this.secondFormGroup.reset();
  this.thirdFormGroup.reset();
  this.fourthFormGroup.reset();
  //this.fifthFormGroup.reset();
}


}
