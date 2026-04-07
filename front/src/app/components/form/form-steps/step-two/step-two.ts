import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatSelectModule } from '@angular/material/select';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { StageService } from '../../../../services/stage-api';
import { debounceTime, map, startWith } from 'rxjs/operators';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import { Observable } from 'rxjs';
import { StepperService } from '../../../../stepper-service';

@Component({
  selector: 'app-step-two',
  standalone: true,
  imports: [MatAutocompleteModule,MatSelectModule,MatCardModule,ReactiveFormsModule,CommonModule,MatFormFieldModule,MatInputModule],
  templateUrl: './step-two.html',
  styleUrls: ['./step-two.css']
})
export class StepTwo implements OnInit {
  @Input() groupsociete!: FormGroup;
  @Input() readOnly: boolean = false;
  societes: any[] = [];
  filteredSocietes!: Observable<any[]>;

  constructor(private stageService: StageService, public stepperService: StepperService) {}

  ngOnInit(): void {
    // ⚡ Utiliser directement le FormGroup passé en Input ou celui du service
    this.groupsociete = this.groupsociete || this.stepperService.secondFormGroup;

    // Charger les sociétés depuis l'API
    this.stageService.getFormData().subscribe((data) => {
      console.log('dataa',data)
      this.societes = data.societes.map((s: any) => ({
        nom: s.nom,
        adresse: s.adresse,
        domaine: s.secteur_activite,
        emailSo: s.email,
        tel: s.telephone,
        siteweb: s.site_web,
        linkedin: s.linkedin || ''
        
      }));

      // Configurer le filtrage pour l'autocomplete
      this.filteredSocietes = this.groupsociete.get('nom')!.valueChanges.pipe(
        startWith(''),
        map(value => this._filter(value || ''))
        
      );
    });
  }

  private _filter(value: string): any[] {
    const filterValue = value.toLowerCase();
    console.log('*****',value,'*******')
    return this.societes.filter(s => s.nom.toLowerCase().includes(filterValue));
  }

  onSocieteSelected(nom: string): void {
    const societeExistante = this.societes.find(s => s.nom === nom);

    if (societeExistante) {
      // ⚡ PatchValue directement dans le FormGroup partagé      
      // Remplir automatiquement les champs
       this.groupsociete.patchValue({ 
       nom: societeExistante.nom,
        adresse: societeExistante.adresse,
         domaine: societeExistante.domaine,
          emailSo: societeExistante.emailSo,
           tel: societeExistante.tel, 
           siteweb: societeExistante.siteweb });
    } else {
      // Nouvelle société → reset des champs sauf nom
      this.groupsociete.patchValue({
        nom:'',
        adresse: '',
        secteur_activite: '',
        emailSo: '',
        tel: '',
        siteweb: '',
        linkedin: ''
      });
    }
  }
}
