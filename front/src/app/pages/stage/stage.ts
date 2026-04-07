import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatSelectModule} from '@angular/material/select';
import { ReactiveFormsModule } from '@angular/forms';
import { HttpClient,HttpClientModule } from '@angular/common/http';
import { StepperNav } from '../../components/form/stepper-nav/stepper-nav';


@Component({
  selector: 'app-stage',
  imports: [MatFormFieldModule,MatSelectModule,ReactiveFormsModule,HttpClientModule,StepperNav],
  templateUrl: './stage.html',
  styleUrl: './stage.css',
  standalone: true,
})
export class Stage  {
}