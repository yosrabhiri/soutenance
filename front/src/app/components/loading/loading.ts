import { Component } from '@angular/core';
import {MatProgressSpinnerModule} from '@angular/material/progress-spinner';

@Component({
  selector: 'app-loading',
  imports:  [MatProgressSpinnerModule],
  templateUrl: './loading.html',
  styleUrl: './loading.css'
})
export class Loading {

}
