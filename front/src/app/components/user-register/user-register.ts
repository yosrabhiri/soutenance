import { Component, inject, OnInit } from '@angular/core';
import {FormBuilder, FormGroup, FormsModule, ReactiveFormsModule, Validators}from '@angular/forms';
import { CommonModule, JsonPipe } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { Router,RouterLink } from '@angular/router';
import { MatOption, MatSelectModule } from '@angular/material/select';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { HttpClient } from '@angular/common/http';
import { StageService } from '../../services/stage-api';
@Component({
  selector: 'app-user-register',
  imports: [FormsModule,CommonModule,MatCardModule,MatInputModule,MatButtonModule,MatIconModule,MatFormFieldModule,RouterLink,MatOption, MatDatepickerModule,MatNativeDateModule,MatSelectModule ,ReactiveFormsModule],
  standalone:true,
  templateUrl: './user-register.html',
  styleUrl: './user-register.css'
})
export class UserRegister implements OnInit{
/*router =inject(Router);
 user = {
    name: '',
    email: '',
    password: '',
    confirmPassword: ''
  };
registerValid=true;
register() {
    // Vérifier que les mots de passe correspondent
    if (this.user.password !== this.user.confirmPassword) {
      this.registerValid = false;
      alert('Passwords do not match!');
      return;
    }

    // Sauvegarder dans le Local Storage
    localStorage.setItem('registeredUser', JSON.stringify({
      name: this.user.name,
      email: this.user.email,
      password: this.user.password // ⚠️ en vrai, ne pas stocker un mot de passe en clair !
    }));

    this.registerValid = true;
    //alert('Registration successful!');
    this.router.navigate(['/home']);
}}*/
constructor(private fb: FormBuilder, private http: HttpClient, private api: StageService) {
    this.registerForm = this.fb.group({
      numero_inscription: ['', Validators.required],
      prenom: ['', Validators.required],
      nom: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required],
      adresse: ['', Validators.required],
      code_postal: ['', Validators.required],
      date_naissance: ['', Validators.required],
      lieu_naissance: ['', Validators.required],
      nationalite: ['', Validators.required],
      genre: ['', Validators.required],
      annee_universitaire: ['', Validators.required],
      annee_bac: ['', [Validators.required, Validators.minLength(4), Validators.maxLength(4)]],
      moyenne_bac: ['', Validators.required],
      session_bac: ['', Validators.required],
      mention_bac: ['', Validators.required],
      section_bac: ['', Validators.required],
      pays_bac: ['', Validators.required],
      statut_universitaire: ['', Validators.required],
      diplome_id: ['', Validators.required],
      niveau_id: ['', Validators.required],
      specialite_id: ['', Validators.required],
      cin: ['', Validators.required],
    });
  }
  router = inject(Router);
  filteredNiveaux: any[] = [];
filteredSpecialites: any[] = [];
registerForm!: FormGroup;
  onSubmit() {
    if (this.registerForm.valid) {
      let formValue = this.registerForm.value;

  // Si c'est une date valide
  if (formValue.date_naissance) {
    formValue.date_naissance = new Date(formValue.date_naissance)
      .toISOString()
      .split('T')[0]; // "2025-08-10"
  }
      this.http.post('http://127.0.0.1:8000/api/register/etudiant', this.registerForm.value)
        .subscribe({
          next: (res: any) =>{console.log('✅ Étudiant enregistré', res),
            this.router.navigate(['/login']);
          } ,
          error: (err: any) => console.error('❌ Erreur', err)
        });
    }
  }
  

  diplomes: any[] = [];
  niveaux: any[] = [];
  specialites: any[] = [];
  filterNiveaux(diplomeId: number) {
  this.filteredNiveaux = this.niveaux.filter(n => n.diplome_id === diplomeId);
}

filterSpecialites(niveauId: number) {
  this.filteredSpecialites = this.specialites.filter(s => s.niveau_id === niveauId);
}
  ngOnInit(): void {
  this.loadData();

  // Quand on change le diplôme
  this.registerForm.get('diplome_id')?.valueChanges.subscribe(diplomeId => {
    this.filterNiveaux(diplomeId);
    // réinitialiser niveau et spécialité
    this.registerForm.patchValue({ niveau_id: null, specialite_id: null });
  });

  // Quand on change le niveau
  this.registerForm.get('niveau_id')?.valueChanges.subscribe(niveauId => {
    this.filterSpecialites(niveauId);
    this.registerForm.patchValue({ specialite_id: null });
  });
}

  loadData() {
    this.api.getDiplomes().subscribe(res => {
  this.diplomes = res.diplomes;   // ⚡ pas juste res
});
    this.api.getNiveaux().subscribe(res => {
  this.niveaux = res.niveaux;   // ⚡ pas juste res
});
    this.api.getSpecialites().subscribe(res => {
  this.specialites = res.specialites;   // ⚡ pas juste res
});




}}

