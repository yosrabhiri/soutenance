import { Component } from '@angular/core';
import { StepperNav } from "../../components/form/stepper-nav/stepper-nav";
import { Historique } from '../../components/etudiant/historique/historique';

@Component({
  selector: 'app-home',
  imports: [Historique],
  templateUrl: './home.html',
  styleUrl: './home.css'
})
export class Home {

}
