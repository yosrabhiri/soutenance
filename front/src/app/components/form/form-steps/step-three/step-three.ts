import { ChangeDetectionStrategy,Component, Input, input } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatSelectModule } from '@angular/material/select';
import { CommonModule } from '@angular/common';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';


@Component({
  selector: 'app-step-three',
  imports: [MatNativeDateModule,MatFormFieldModule, MatDatepickerModule,MatSelectModule,MatCardModule,ReactiveFormsModule,CommonModule,MatFormFieldModule,MatInputModule],
  templateUrl: './step-three.html',
  styleUrl: './step-three.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class StepThree {
  @Input() details!: FormGroup;
  @Input() readOnly: boolean = false;
  
   get campaignOne(): FormGroup {
    return this.details.get('campaignOne') as FormGroup;
  }

   onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;

    const file = input.files[0];
    console.log('Fichier sélectionné :', file);
    this.details.patchValue({ fichier: file });

    // Tu peux ici lancer l'upload ou traiter le fichier
  }
}