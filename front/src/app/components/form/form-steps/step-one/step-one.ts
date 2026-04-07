import { Component , Input, OnInit} from '@angular/core';
import { FormControl, FormGroup, FormGroupDirective, Validators } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';

@Component({
  selector: 'app-step-one',
  imports: [MatSelectModule,MatCardModule,ReactiveFormsModule,CommonModule,MatFormFieldModule,MatInputModule],
  templateUrl: './step-one.html',
  styleUrl: './step-one.css'
})
export class StepOne {
  @Input() group!: FormGroup;
}

